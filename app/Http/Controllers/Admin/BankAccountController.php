<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BankAccountController extends Controller
{
    /**
     * Display a listing of bank accounts
     */
    public function index(): Response
    {
        $accounts = BankAccount::orderBy('is_primary', 'desc')
            ->orderBy('is_active', 'desc')
            ->orderBy('account_name')
            ->get();

        $stats = [
            'total_balance' => $accounts->where('is_active', true)->sum('current_balance'),
            'bank_accounts' => $accounts->where('account_type', 'bank')->count(),
            'mobile_accounts' => $accounts->where('account_type', 'mobile_banking')->count(),
            'cash_accounts' => $accounts->where('account_type', 'cash')->count(),
        ];

        return Inertia::render('Admin/Accounting/BankAccounts/Index', [
            'accounts' => $accounts,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new bank account
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Accounting/BankAccounts/Create', [
            'accountTypes' => [
                'bank' => 'Bank Account',
                'mobile_banking' => 'Mobile Banking',
                'cash' => 'Cash Account',
            ],
            'mobileBankingProviders' => [
                'bKash',
                'Nagad',
                'Rocket',
                'Upay',
                'SureCash',
            ],
        ]);
    }

    /**
     * Store a newly created bank account
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts',
            'account_type' => 'required|in:bank,mobile_banking,cash',
            'bank_name' => 'required_if:account_type,bank|nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'routing_number' => 'nullable|string|max:20',
            'mobile_banking_provider' => 'required_if:account_type,mobile_banking|nullable|string|max:50',
            'opening_balance' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:500',
            'is_primary' => 'boolean',
        ]);

        $validated['current_balance'] = $validated['opening_balance'] ?? 0;
        $validated['is_active'] = true;

        // If setting as primary, unset other primary accounts
        if ($validated['is_primary'] ?? false) {
            BankAccount::where('is_primary', true)->update(['is_primary' => false]);
        }

        BankAccount::create($validated);

        return redirect()
            ->route('admin.accounting.bank-accounts.index')
            ->with('success', 'Bank account created successfully.');
    }

    /**
     * Display the specified bank account
     */
    public function show(BankAccount $bankAccount): Response
    {
        // Get recent transactions related to this account
        $recentTransactions = DB::table('wallet_transactions')
            ->where('payment_method', 'like', '%'.$bankAccount->account_name.'%')
            ->orWhere('reference_type', 'bank_account')
            ->where('reference_id', $bankAccount->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('Admin/Accounting/BankAccounts/Show', [
            'account' => $bankAccount,
            'transactions' => $recentTransactions,
        ]);
    }

    /**
     * Show the form for editing the specified bank account
     */
    public function edit(BankAccount $bankAccount): Response
    {
        return Inertia::render('Admin/Accounting/BankAccounts/Edit', [
            'account' => $bankAccount,
            'accountTypes' => [
                'bank' => 'Bank Account',
                'mobile_banking' => 'Mobile Banking',
                'cash' => 'Cash Account',
            ],
            'mobileBankingProviders' => [
                'bKash',
                'Nagad',
                'Rocket',
                'Upay',
                'SureCash',
            ],
        ]);
    }

    /**
     * Update the specified bank account
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number,'.$bankAccount->id,
            'account_type' => 'required|in:bank,mobile_banking,cash',
            'bank_name' => 'required_if:account_type,bank|nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'routing_number' => 'nullable|string|max:20',
            'mobile_banking_provider' => 'required_if:account_type,mobile_banking|nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'is_primary' => 'boolean',
        ]);

        // If setting as primary, unset other primary accounts
        if (($validated['is_primary'] ?? false) && ! $bankAccount->is_primary) {
            BankAccount::where('is_primary', true)->update(['is_primary' => false]);
        }

        $bankAccount->update($validated);

        return redirect()
            ->route('admin.accounting.bank-accounts.index')
            ->with('success', 'Bank account updated successfully.');
    }

    /**
     * Update account balance (manual adjustment)
     */
    public function adjustBalance(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:credit,debit',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
        ]);

        $oldBalance = $bankAccount->current_balance;

        if ($validated['adjustment_type'] === 'credit') {
            $bankAccount->credit($validated['amount']);
        } else {
            $bankAccount->debit($validated['amount']);
        }

        // Log the adjustment (you might want to create an audit log table)
        activity()
            ->performedOn($bankAccount)
            ->withProperties([
                'old_balance' => $oldBalance,
                'new_balance' => $bankAccount->current_balance,
                'adjustment_type' => $validated['adjustment_type'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
            ])
            ->log('Balance adjusted');

        return back()->with('success', 'Balance adjusted successfully.');
    }

    /**
     * Set account as primary
     */
    public function setPrimary(BankAccount $bankAccount)
    {
        BankAccount::where('is_primary', true)->update(['is_primary' => false]);
        $bankAccount->update(['is_primary' => true]);

        return back()->with('success', 'Account set as primary.');
    }

    /**
     * Toggle account active status
     */
    public function toggleActive(BankAccount $bankAccount)
    {
        $bankAccount->update(['is_active' => ! $bankAccount->is_active]);

        $status = $bankAccount->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Account {$status} successfully.");
    }

    /**
     * Remove the specified bank account
     */
    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->is_primary) {
            return back()->with('error', 'Cannot delete primary account.');
        }

        if ($bankAccount->current_balance > 0) {
            return back()->with('error', 'Cannot delete account with remaining balance.');
        }

        $bankAccount->delete();

        return redirect()
            ->route('admin.accounting.bank-accounts.index')
            ->with('success', 'Bank account deleted successfully.');
    }
}
