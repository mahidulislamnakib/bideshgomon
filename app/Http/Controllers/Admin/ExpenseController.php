<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request): Response
    {
        $query = Expense::with(['category', 'creator', 'approver', 'bankAccount']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('expense_number', 'like', "%{$search}%")
                    ->orWhere('vendor_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($categoryId = $request->get('category')) {
            $query->where('expense_category_id', $categoryId);
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        if ($request->get('start_date') && $request->get('end_date')) {
            $query->dateBetween($request->get('start_date'), $request->get('end_date'));
        }

        // Filter by payment method
        if ($method = $request->get('payment_method')) {
            $query->where('payment_method', $method);
        }

        $expenses = $query->latest('expense_date')->paginate(20)->withQueryString();

        // Get stats
        $stats = [
            'total_expenses' => Expense::sum('total_amount'),
            'pending' => Expense::where('status', 'pending')->sum('total_amount'),
            'approved' => Expense::where('status', 'approved')->sum('total_amount'),
            'this_month' => Expense::whereMonth('expense_date', now()->month)
                ->whereYear('expense_date', now()->year)
                ->sum('total_amount'),
        ];

        return Inertia::render('Admin/Accounting/Expenses/Index', [
            'expenses' => $expenses,
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'filters' => $request->only(['search', 'category', 'status', 'start_date', 'end_date', 'payment_method']),
            'stats' => $stats,
            'paymentMethods' => Expense::getPaymentMethods(),
        ]);
    }

    /**
     * Show the form for creating a new expense
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Accounting/Expenses/Create', [
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'bankAccounts' => BankAccount::active()->get(),
            'paymentMethods' => Expense::getPaymentMethods(),
        ]);
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'is_recurring' => 'boolean',
            'recurring_frequency' => 'nullable|required_if:is_recurring,true|string',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['tax_amount'] = $validated['tax_amount'] ?? 0;
        $validated['total_amount'] = $validated['amount'] + $validated['tax_amount'];
        $validated['status'] = 'pending';

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $validated['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $expense = Expense::create($validated);

        return redirect()
            ->route('admin.accounting.expenses.index')
            ->with('success', 'Expense created successfully.');
    }

    /**
     * Display the specified expense
     */
    public function show(Expense $expense): Response
    {
        $expense->load(['category', 'creator', 'approver', 'bankAccount']);

        return Inertia::render('Admin/Accounting/Expenses/Show', [
            'expense' => $expense,
        ]);
    }

    /**
     * Show the form for editing the expense
     */
    public function edit(Expense $expense): Response
    {
        return Inertia::render('Admin/Accounting/Expenses/Edit', [
            'expense' => $expense,
            'categories' => ExpenseCategory::active()->orderBy('name')->get(),
            'bankAccounts' => BankAccount::active()->get(),
            'paymentMethods' => Expense::getPaymentMethods(),
        ]);
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense)
    {
        if ($expense->status === 'paid') {
            return back()->with('error', 'Cannot edit a paid expense.');
        }

        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'vendor_name' => 'nullable|string|max:255',
            'vendor_contact' => 'nullable|string|max:100',
            'expense_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:100',
            'description' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $validated['tax_amount'] = $validated['tax_amount'] ?? 0;
        $validated['total_amount'] = $validated['amount'] + $validated['tax_amount'];

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt
            if ($expense->receipt_path) {
                Storage::disk('public')->delete($expense->receipt_path);
            }
            $validated['receipt_path'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $expense->update($validated);

        return redirect()
            ->route('admin.accounting.expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Approve an expense
     */
    public function approve(Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Only pending expenses can be approved.');
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Expense approved successfully.');
    }

    /**
     * Reject an expense
     */
    public function reject(Request $request, Expense $expense)
    {
        if ($expense->status !== 'pending') {
            return back()->with('error', 'Only pending expenses can be rejected.');
        }

        $expense->update([
            'status' => 'rejected',
            'notes' => $request->get('rejection_reason', $expense->notes),
        ]);

        return back()->with('success', 'Expense rejected.');
    }

    /**
     * Mark expense as paid
     */
    public function markPaid(Expense $expense)
    {
        if ($expense->status !== 'approved') {
            return back()->with('error', 'Only approved expenses can be marked as paid.');
        }

        $expense->update(['status' => 'paid']);

        // Update bank account balance if applicable
        if ($expense->bank_account_id) {
            $expense->bankAccount->updateBalance($expense->total_amount, 'debit');
        }

        return back()->with('success', 'Expense marked as paid.');
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Expense $expense)
    {
        if ($expense->status === 'paid') {
            return back()->with('error', 'Cannot delete a paid expense.');
        }

        // Delete receipt file
        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()
            ->route('admin.accounting.expenses.index')
            ->with('success', 'Expense deleted successfully.');
    }
}
