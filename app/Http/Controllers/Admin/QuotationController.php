<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\TaxSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    /**
     * Display a listing of quotations
     */
    public function index(Request $request): Response
    {
        $query = Quotation::with(['client', 'creator', 'convertedInvoice']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('client_email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($request->get('start_date') && $request->get('end_date')) {
            $query->whereBetween('issue_date', [$request->get('start_date'), $request->get('end_date')]);
        }

        $quotations = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Accounting/Quotations/Index', [
            'quotations' => $quotations,
            'filters' => $request->only(['search', 'status', 'start_date', 'end_date']),
            'stats' => [
                'total' => Quotation::count(),
                'pending' => Quotation::whereIn('status', ['sent', 'viewed'])->count(),
                'accepted' => Quotation::where('status', 'accepted')->count(),
                'converted' => Quotation::where('status', 'converted')->count(),
                'total_value' => Quotation::whereIn('status', ['sent', 'viewed', 'accepted'])->sum('total_amount'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new quotation
     */
    public function create(Request $request): Response
    {
        $client = null;
        if ($clientId = $request->get('client_id')) {
            $client = Client::find($clientId);
        }

        return Inertia::render('Admin/Accounting/Quotations/Create', [
            'clients' => Client::active()->orderBy('name')->get(),
            'services' => Service::active()->orderBy('title')->get(),
            'defaultTax' => TaxSetting::getDefault(),
            'selectedClient' => $client,
        ]);
    }

    /**
     * Store a newly created quotation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);

        $quotation = Quotation::create([
            'client_id' => $validated['client_id'],
            'created_by' => auth()->id(),
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'] ?? null,
            'client_phone' => $validated['client_phone'] ?? null,
            'client_address' => $validated['client_address'] ?? null,
            'issue_date' => $validated['issue_date'],
            'valid_until' => $validated['valid_until'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
            'status' => 'draft',
        ]);

        // Create items
        foreach ($validated['items'] as $index => $item) {
            $quotation->items()->create([
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'sort_order' => $index,
            ]);
        }

        return redirect()
            ->route('admin.accounting.quotations.show', $quotation)
            ->with('success', 'Quotation created successfully.');
    }

    /**
     * Display the specified quotation
     */
    public function show(Quotation $quotation): Response
    {
        $quotation->load(['client', 'creator', 'items.service', 'convertedInvoice']);

        return Inertia::render('Admin/Accounting/Quotations/Show', [
            'quotation' => $quotation,
        ]);
    }

    /**
     * Show the form for editing the quotation
     */
    public function edit(Quotation $quotation): Response
    {
        if (in_array($quotation->status, ['converted', 'accepted'])) {
            return redirect()
                ->route('admin.accounting.quotations.show', $quotation)
                ->with('error', 'Cannot edit a converted or accepted quotation.');
        }

        $quotation->load('items');

        return Inertia::render('Admin/Accounting/Quotations/Edit', [
            'quotation' => $quotation,
            'clients' => Client::active()->orderBy('name')->get(),
            'services' => Service::active()->orderBy('title')->get(),
            'defaultTax' => TaxSetting::getDefault(),
        ]);
    }

    /**
     * Update the specified quotation
     */
    public function update(Request $request, Quotation $quotation)
    {
        if (in_array($quotation->status, ['converted', 'accepted'])) {
            return back()->with('error', 'Cannot update a converted or accepted quotation.');
        }

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'client_address' => 'nullable|string|max:500',
            'issue_date' => 'required|date',
            'valid_until' => 'required|date|after_or_equal:issue_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:500',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.service_id' => 'nullable|exists:services,id',
        ]);

        $quotation->update([
            'client_id' => $validated['client_id'],
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'] ?? null,
            'client_phone' => $validated['client_phone'] ?? null,
            'client_address' => $validated['client_address'] ?? null,
            'issue_date' => $validated['issue_date'],
            'valid_until' => $validated['valid_until'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'notes' => $validated['notes'] ?? null,
            'terms' => $validated['terms'] ?? null,
        ]);

        // Sync items
        $quotation->items()->delete();
        foreach ($validated['items'] as $index => $item) {
            $quotation->items()->create([
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'sort_order' => $index,
            ]);
        }

        return redirect()
            ->route('admin.accounting.quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    /**
     * Send quotation to client
     */
    public function send(Quotation $quotation)
    {
        if (! $quotation->client_email) {
            return back()->with('error', 'Client email is required to send quotation.');
        }

        // TODO: Implement email sending

        $quotation->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return back()->with('success', 'Quotation sent to client.');
    }

    /**
     * Mark quotation as accepted
     */
    public function accept(Quotation $quotation)
    {
        $quotation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return back()->with('success', 'Quotation marked as accepted.');
    }

    /**
     * Mark quotation as rejected
     */
    public function reject(Request $request, Quotation $quotation)
    {
        $quotation->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejection_reason' => $request->get('reason'),
        ]);

        return back()->with('success', 'Quotation marked as rejected.');
    }

    /**
     * Convert quotation to invoice
     */
    public function convert(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Quotation is already converted.');
        }

        $invoice = $quotation->convertToInvoice();

        return redirect()
            ->route('admin.accounting.invoices.show', $invoice)
            ->with('success', 'Quotation converted to invoice successfully.');
    }

    /**
     * Duplicate quotation
     */
    public function duplicate(Quotation $quotation)
    {
        $newQuotation = $quotation->replicate([
            'quotation_number',
            'status',
            'sent_at',
            'viewed_at',
            'accepted_at',
            'rejected_at',
            'converted_invoice_id',
        ]);

        $newQuotation->quotation_number = Quotation::generateQuotationNumber();
        $newQuotation->issue_date = now();
        $newQuotation->valid_until = now()->addDays(30);
        $newQuotation->status = 'draft';
        $newQuotation->created_by = auth()->id();
        $newQuotation->save();

        // Duplicate items
        foreach ($quotation->items as $item) {
            $newItem = $item->replicate();
            $newItem->quotation_id = $newQuotation->id;
            $newItem->save();
        }

        return redirect()
            ->route('admin.accounting.quotations.edit', $newQuotation)
            ->with('success', 'Quotation duplicated successfully.');
    }

    /**
     * Remove the specified quotation
     */
    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Cannot delete a converted quotation.');
        }

        $quotation->delete();

        return redirect()
            ->route('admin.accounting.quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }
}
