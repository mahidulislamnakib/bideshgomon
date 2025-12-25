<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PaymentController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display payment listing
     */
    public function index(Request $request): Response
    {
        $query = Payment::with([
            'invoice:id,invoice_number,client_id,total_amount',
            'invoice.client:id,name',
            'receiver:id,name',
        ]);

        // Filters
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice', fn($q) => $q->where('invoice_number', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        // Sorting
        $sortField = $request->get('sort', 'payment_date');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $payments = $query->paginate(20)->withQueryString();

        // Stats
        $totalCollected = Payment::where('status', Payment::STATUS_COMPLETED)->sum('amount');
        $totalCount = Payment::where('status', Payment::STATUS_COMPLETED)->count();
        $thisMonth = Payment::where('status', Payment::STATUS_COMPLETED)
            ->whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('amount');

        $stats = [
            'totalCollected' => $totalCollected,
            'thisMonth' => $thisMonth,
            'totalCount' => $totalCount,
            'average' => $totalCount > 0 ? $totalCollected / $totalCount : 0,
        ];

        // Method breakdown
        $methodBreakdown = Payment::selectRaw('payment_method as method, SUM(amount) as total, COUNT(*) as count')
            ->where('status', Payment::STATUS_COMPLETED)
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Recent payments
        $recentPayments = Payment::with(['invoice:id,invoice_number,client_name,client_id', 'invoice.client:id,name'])
            ->where('status', Payment::STATUS_COMPLETED)
            ->orderByDesc('payment_date')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Accounting/Payments/Index', [
            'payments' => $payments,
            'stats' => $stats,
            'methodBreakdown' => $methodBreakdown,
            'recentPayments' => $recentPayments,
            'filters' => $request->only(['payment_method', 'search', 'date_from', 'date_to', 'sort', 'direction']),
            'paymentMethods' => Payment::getPaymentMethods(),
        ]);
    }

    /**
     * Store new payment
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getPaymentMethods())),
            'transaction_id' => 'nullable|string|max:100',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Validate amount doesn't exceed balance
        $maxPayment = $invoice->total_amount - $invoice->paid_amount;
        if ($validated['amount'] > $maxPayment) {
            return back()->withErrors([
                'amount' => "Payment amount cannot exceed outstanding balance of ৳" . number_format($maxPayment, 2),
            ]);
        }

        $validated['received_by'] = auth()->id();

        $this->invoiceService->recordPayment($invoice, $validated);

        return back()->with('success', 'Payment recorded successfully.');
    }

    /**
     * Show payment details
     */
    public function show(Payment $payment): Response
    {
        $payment->load([
            'invoice.client:id,name,email',
            'invoice.items',
            'receiver:id,name',
        ]);

        return Inertia::render('Admin/Accounting/Payments/Show', [
            'payment' => $payment,
        ]);
    }

    /**
     * Delete/refund payment
     */
    public function destroy(Payment $payment): RedirectResponse
    {
        $payment->update(['status' => Payment::STATUS_REFUNDED]);
        $payment->delete();

        return back()->with('success', 'Payment removed and invoice balance updated.');
    }
}
