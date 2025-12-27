<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\RecurringInvoice;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RecurringInvoiceController extends Controller
{
    /**
     * Display a listing of recurring invoices
     */
    public function index(Request $request): Response
    {
        $query = RecurringInvoice::with(['client', 'service']);

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by frequency
        if ($frequency = $request->get('frequency')) {
            $query->where('frequency', $frequency);
        }

        $recurringInvoices = $query->orderBy('next_invoice_date')->paginate(20)->withQueryString();

        return Inertia::render('Admin/Accounting/RecurringInvoices/Index', [
            'recurringInvoices' => $recurringInvoices,
            'filters' => $request->only(['status', 'frequency']),
            'stats' => [
                'total' => RecurringInvoice::count(),
                'active' => RecurringInvoice::where('status', 'active')->count(),
                'monthly_revenue' => RecurringInvoice::where('status', 'active')
                    ->where('frequency', 'monthly')
                    ->sum('total_amount'),
                'annual_revenue' => RecurringInvoice::where('status', 'active')
                    ->sum('total_amount') * 12, // Approximate
            ],
        ]);
    }

    /**
     * Show the form for creating a new recurring invoice
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Accounting/RecurringInvoices/Create', [
            'clients' => Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'email', 'client_type']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'frequencies' => [
                'weekly' => 'Weekly',
                'biweekly' => 'Bi-Weekly',
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'semi_annual' => 'Semi-Annual',
                'annual' => 'Annual',
            ],
        ]);
    }

    /**
     * Store a newly created recurring invoice
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'nullable|exists:services,id',
            'frequency' => 'required|in:weekly,biweekly,monthly,quarterly,semi_annual,annual',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'required|string|max:1000',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'notes' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|integer|min:0',
            'auto_send' => 'boolean',
        ]);

        // Calculate totals
        $subtotal = $validated['subtotal'];
        $taxAmount = $subtotal * (($validated['tax_rate'] ?? 0) / 100);
        $discountAmount = 0;

        if (isset($validated['discount_amount'])) {
            if (($validated['discount_type'] ?? 'fixed') === 'percentage') {
                $discountAmount = $subtotal * ($validated['discount_amount'] / 100);
            } else {
                $discountAmount = $validated['discount_amount'];
            }
        }

        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $recurringInvoice = RecurringInvoice::create([
            'client_id' => $validated['client_id'],
            'service_id' => $validated['service_id'] ?? null,
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'next_invoice_date' => $validated['start_date'],
            'description' => $validated['description'],
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'discount_type' => $validated['discount_type'] ?? 'fixed',
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? 15,
            'auto_send' => $validated['auto_send'] ?? false,
            'status' => 'active',
        ]);

        return redirect()
            ->route('admin.accounting.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice created successfully.');
    }

    /**
     * Display the specified recurring invoice
     */
    public function show(RecurringInvoice $recurringInvoice): Response
    {
        $recurringInvoice->load(['client', 'service', 'generatedInvoices' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return Inertia::render('Admin/Accounting/RecurringInvoices/Show', [
            'recurringInvoice' => $recurringInvoice,
        ]);
    }

    /**
     * Show the form for editing the specified recurring invoice
     */
    public function edit(RecurringInvoice $recurringInvoice): Response
    {
        return Inertia::render('Admin/Accounting/RecurringInvoices/Edit', [
            'recurringInvoice' => $recurringInvoice->load(['client', 'service']),
            'clients' => Client::where('is_active', true)->orderBy('name')->get(['id', 'name', 'email', 'client_type']),
            'services' => Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price']),
            'frequencies' => [
                'weekly' => 'Weekly',
                'biweekly' => 'Bi-Weekly',
                'monthly' => 'Monthly',
                'quarterly' => 'Quarterly',
                'semi_annual' => 'Semi-Annual',
                'annual' => 'Annual',
            ],
        ]);
    }

    /**
     * Update the specified recurring invoice
     */
    public function update(Request $request, RecurringInvoice $recurringInvoice)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'service_id' => 'nullable|exists:services,id',
            'frequency' => 'required|in:weekly,biweekly,monthly,quarterly,semi_annual,annual',
            'end_date' => 'nullable|date|after:start_date',
            'description' => 'required|string|max:1000',
            'subtotal' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:fixed,percentage',
            'notes' => 'nullable|string|max:500',
            'payment_terms' => 'nullable|integer|min:0',
            'auto_send' => 'boolean',
        ]);

        // Calculate totals
        $subtotal = $validated['subtotal'];
        $taxAmount = $subtotal * (($validated['tax_rate'] ?? 0) / 100);
        $discountAmount = 0;

        if (isset($validated['discount_amount'])) {
            if (($validated['discount_type'] ?? 'fixed') === 'percentage') {
                $discountAmount = $subtotal * ($validated['discount_amount'] / 100);
            } else {
                $discountAmount = $validated['discount_amount'];
            }
        }

        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $recurringInvoice->update([
            'client_id' => $validated['client_id'],
            'service_id' => $validated['service_id'] ?? null,
            'frequency' => $validated['frequency'],
            'end_date' => $validated['end_date'] ?? null,
            'description' => $validated['description'],
            'subtotal' => $subtotal,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'discount_type' => $validated['discount_type'] ?? 'fixed',
            'total_amount' => $totalAmount,
            'notes' => $validated['notes'] ?? null,
            'payment_terms' => $validated['payment_terms'] ?? 15,
            'auto_send' => $validated['auto_send'] ?? false,
        ]);

        return redirect()
            ->route('admin.accounting.recurring-invoices.show', $recurringInvoice)
            ->with('success', 'Recurring invoice updated successfully.');
    }

    /**
     * Pause recurring invoice
     */
    public function pause(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status !== 'active') {
            return back()->with('error', 'Only active recurring invoices can be paused.');
        }

        $recurringInvoice->update(['status' => 'paused']);

        return back()->with('success', 'Recurring invoice paused.');
    }

    /**
     * Resume recurring invoice
     */
    public function resume(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status !== 'paused') {
            return back()->with('error', 'Only paused recurring invoices can be resumed.');
        }

        // Set next invoice date if in the past
        $nextDate = $recurringInvoice->next_invoice_date;
        if ($nextDate->isPast()) {
            $nextDate = $recurringInvoice->calculateNextDate();
        }

        $recurringInvoice->update([
            'status' => 'active',
            'next_invoice_date' => $nextDate,
        ]);

        return back()->with('success', 'Recurring invoice resumed.');
    }

    /**
     * Cancel recurring invoice
     */
    public function cancel(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status === 'cancelled') {
            return back()->with('error', 'Recurring invoice is already cancelled.');
        }

        $recurringInvoice->update(['status' => 'cancelled']);

        return back()->with('success', 'Recurring invoice cancelled.');
    }

    /**
     * Generate invoice manually
     */
    public function generateInvoice(RecurringInvoice $recurringInvoice)
    {
        if ($recurringInvoice->status !== 'active') {
            return back()->with('error', 'Cannot generate invoice for inactive recurring invoices.');
        }

        $invoice = $recurringInvoice->generateInvoice();

        if ($invoice) {
            return redirect()
                ->route('admin.accounting.invoices.show', $invoice)
                ->with('success', 'Invoice generated successfully.');
        }

        return back()->with('error', 'Failed to generate invoice.');
    }

    /**
     * Remove the specified recurring invoice
     */
    public function destroy(RecurringInvoice $recurringInvoice)
    {
        $recurringInvoice->delete();

        return redirect()
            ->route('admin.accounting.recurring-invoices.index')
            ->with('success', 'Recurring invoice deleted successfully.');
    }
}
