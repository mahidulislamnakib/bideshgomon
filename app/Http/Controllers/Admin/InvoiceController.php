<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Service;
use App\Models\User;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display invoice listing
     */
    public function index(Request $request): Response
    {
        $query = Invoice::with(['client:id,name,email', 'creator:id,name'])
            ->withCount('items')
            ->withSum('payments', 'amount');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhereHas('client', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('issue_date', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $invoices = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => Invoice::count(),
            'draft' => Invoice::status('draft')->count(),
            'sent' => Invoice::status('sent')->count(),
            'paid' => Invoice::status('paid')->count(),
            'overdue' => Invoice::overdue()->count(),
            'total_outstanding' => Invoice::unpaid()
                ->selectRaw('SUM(total_amount - paid_amount) as total')
                ->value('total') ?? 0,
        ];

        return Inertia::render('Admin/Accounting/Invoices/Index', [
            'invoices' => $invoices,
            'stats' => $stats,
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to', 'sort', 'direction']),
        ]);
    }

    /**
     * Show create form
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Accounting/Invoices/Create', [
            'invoiceNumber' => Invoice::generateInvoiceNumber(),
            'clients' => User::select('id', 'name', 'email', 'phone')
                ->orderBy('name')
                ->get(),
            'services' => Service::select('id', 'name', 'price', 'description')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'defaultTaxRate' => 15.00,
            'defaultTerms' => "Payment is due within 30 days.\nAll amounts are in Bangladeshi Taka (৳).",
        ]);
    }

    /**
     * Store new invoice
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'client_name' => 'required_without:client_id|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,sent',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);

        $invoiceData = collect($validated)->except('items')->toArray();
        $invoiceData['created_by'] = auth()->id();

        $invoice = $this->invoiceService->createInvoice(
            $invoiceData,
            $validated['items']
        );

        return redirect()
            ->route('admin.accounting.invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Show invoice details
     */
    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'items.service:id,name',
            'client:id,name,email,phone',
            'creator:id,name',
            'payments.receiver:id,name',
        ]);

        return Inertia::render('Admin/Accounting/Invoices/Show', [
            'invoice' => $invoice,
            'paymentMethods' => \App\Models\Payment::getPaymentMethods(),
        ]);
    }

    /**
     * Show edit form
     */
    public function edit(Invoice $invoice): Response
    {
        // Paid and cancelled invoices cannot be edited
        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])) {
            return redirect()
                ->route('admin.accounting.invoices.show', $invoice)
                ->with('error', 'Paid and cancelled invoices cannot be edited.');
        }

        $invoice->load(['items', 'client:id,name,email,phone', 'payments']);

        return Inertia::render('Admin/Accounting/Invoices/Edit', [
            'invoice' => $invoice,
            'clients' => User::select('id', 'name', 'email', 'phone')
                ->orderBy('name')
                ->get(),
            'services' => Service::select('id', 'name', 'price', 'description')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update invoice
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // Paid and cancelled invoices cannot be edited
        if (in_array($invoice->status, [Invoice::STATUS_PAID, Invoice::STATUS_CANCELLED])) {
            return back()->with('error', 'Paid and cancelled invoices cannot be edited.');
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:users,id',
            'client_name' => 'required_without:client_id|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:2000',
            'status' => 'nullable|in:draft,sent,partial,overdue',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount' => 'nullable|numeric|min:0',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);

        $invoiceData = collect($validated)->except('items')->toArray();

        $this->invoiceService->updateInvoice(
            $invoice,
            $invoiceData,
            $validated['items']
        );

        return redirect()
            ->route('admin.accounting.invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    /**
     * Delete invoice
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        // Only allow deletion of draft or cancelled invoices
        if (! in_array($invoice->status, [Invoice::STATUS_DRAFT, Invoice::STATUS_CANCELLED])) {
            return back()->with('error', 'Only draft or cancelled invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()
            ->route('admin.accounting.invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    /**
     * Mark invoice as sent
     */
    public function send(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->markAsSent($invoice);

        return back()->with('success', 'Invoice marked as sent.');
    }

    /**
     * Duplicate invoice
     */
    public function duplicate(Invoice $invoice): RedirectResponse
    {
        $newInvoice = $this->invoiceService->duplicateInvoice($invoice);

        return redirect()
            ->route('admin.accounting.invoices.edit', $newInvoice)
            ->with('success', 'Invoice duplicated successfully.');
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->cancelInvoice($invoice);

        return back()->with('success', 'Invoice cancelled.');
    }

    /**
     * Print-friendly invoice view
     */
    public function print(Invoice $invoice): Response
    {
        $invoice->load([
            'items.service:id,name',
            'client:id,name,email,phone,address',
            'creator:id,name',
            'payments.receiver:id,name',
        ]);

        return Inertia::render('Admin/Accounting/Invoices/Print', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Export invoice as PDF (download)
     */
    public function pdf(Invoice $invoice)
    {
        $invoice->load([
            'items.service:id,name',
            'client:id,name,email,phone,address',
            'creator:id,name',
            'payments',
        ]);

        // Check if DomPDF is installed
        if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
            ]);

            return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
        }

        // Fallback: redirect to print page with message
        return redirect()
            ->route('admin.accounting.invoices.print', $invoice)
            ->with('info', 'PDF export requires DomPDF package. Use browser print to save as PDF.');
    }
}
