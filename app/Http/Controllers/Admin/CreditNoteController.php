<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNote;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CreditNoteController extends Controller
{
    /**
     * Display a listing of credit notes
     */
    public function index(Request $request): Response
    {
        $query = CreditNote::with(['invoice', 'client', 'creator', 'approver']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('credit_note_number', 'like', "%{$search}%")
                    ->orWhereHas('invoice', function ($q) use ($search) {
                        $q->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $creditNotes = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Accounting/CreditNotes/Index', [
            'creditNotes' => $creditNotes,
            'filters' => $request->only(['search', 'status']),
            'stats' => [
                'total' => CreditNote::count(),
                'pending' => CreditNote::where('status', 'pending')->count(),
                'total_amount' => CreditNote::where('status', 'approved')->sum('amount'),
            ],
            'reasons' => CreditNote::getReasons(),
        ]);
    }

    /**
     * Show the form for creating a new credit note
     */
    public function create(Request $request): Response
    {
        $invoice = null;
        if ($invoiceId = $request->get('invoice_id')) {
            $invoice = Invoice::with('client')->find($invoiceId);
        }

        return Inertia::render('Admin/Accounting/CreditNotes/Create', [
            'invoice' => $invoice,
            'invoices' => Invoice::whereIn('status', ['sent', 'partial', 'paid', 'overdue'])
                ->orderBy('invoice_number', 'desc')
                ->get(['id', 'invoice_number', 'client_name', 'total_amount', 'paid_amount']),
            'reasons' => CreditNote::getReasons(),
        ]);
    }

    /**
     * Store a newly created credit note
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice = Invoice::findOrFail($validated['invoice_id']);

        // Validate amount doesn't exceed invoice total
        if ($validated['amount'] > $invoice->total_amount) {
            return back()->withErrors(['amount' => 'Credit note amount cannot exceed invoice total.']);
        }

        $creditNote = CreditNote::create([
            'invoice_id' => $validated['invoice_id'],
            'client_id' => $invoice->client_id,
            'created_by' => auth()->id(),
            'issue_date' => now(),
            'amount' => $validated['amount'],
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('admin.accounting.credit-notes.show', $creditNote)
            ->with('success', 'Credit note created successfully.');
    }

    /**
     * Display the specified credit note
     */
    public function show(CreditNote $creditNote): Response
    {
        $creditNote->load(['invoice.items', 'client', 'creator', 'approver']);

        return Inertia::render('Admin/Accounting/CreditNotes/Show', [
            'creditNote' => $creditNote,
        ]);
    }

    /**
     * Approve credit note
     */
    public function approve(CreditNote $creditNote)
    {
        if ($creditNote->status !== 'pending') {
            return back()->with('error', 'Only pending credit notes can be approved.');
        }

        $creditNote->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Credit note approved successfully.');
    }

    /**
     * Apply credit note to invoice
     */
    public function apply(CreditNote $creditNote)
    {
        if ($creditNote->status !== 'approved') {
            return back()->with('error', 'Only approved credit notes can be applied.');
        }

        $creditNote->apply();

        return back()->with('success', 'Credit note applied to invoice.');
    }

    /**
     * Void credit note
     */
    public function void(CreditNote $creditNote)
    {
        if ($creditNote->status === 'applied') {
            return back()->with('error', 'Cannot void an applied credit note.');
        }

        $creditNote->update(['status' => 'void']);

        return back()->with('success', 'Credit note voided.');
    }

    /**
     * Remove the specified credit note
     */
    public function destroy(CreditNote $creditNote)
    {
        if ($creditNote->status === 'applied') {
            return back()->with('error', 'Cannot delete an applied credit note.');
        }

        $creditNote->delete();

        return redirect()
            ->route('admin.accounting.credit-notes.index')
            ->with('success', 'Credit note deleted successfully.');
    }
}
