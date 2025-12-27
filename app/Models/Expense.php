<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'expense_number',
        'expense_category_id',
        'created_by',
        'approved_by',
        'bank_account_id',
        'vendor_name',
        'vendor_contact',
        'expense_date',
        'amount',
        'tax_amount',
        'total_amount',
        'payment_method',
        'reference_number',
        'receipt_path',
        'description',
        'notes',
        'status',
        'is_recurring',
        'recurring_frequency',
        'next_recurring_date',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'next_recurring_date' => 'date',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUS_PAID = 'paid';

    const METHOD_CASH = 'cash';

    const METHOD_BANK = 'bank';

    const METHOD_BKASH = 'bkash';

    const METHOD_NAGAD = 'nagad';

    const METHOD_CARD = 'card';

    const METHOD_CHECK = 'check';

    const FREQUENCY_WEEKLY = 'weekly';

    const FREQUENCY_MONTHLY = 'monthly';

    const FREQUENCY_QUARTERLY = 'quarterly';

    const FREQUENCY_YEARLY = 'yearly';

    /**
     * Generate expense number
     */
    public static function generateExpenseNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "EXP-{$year}-";

        $lastExpense = static::withTrashed()
            ->where('expense_number', 'like', $prefix.'%')
            ->orderBy('expense_number', 'desc')
            ->first();

        if ($lastExpense) {
            $lastNumber = (int) substr($lastExpense->expense_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix.str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($expense) {
            if (empty($expense->expense_number)) {
                $expense->expense_number = static::generateExpenseNumber();
            }
            $expense->total_amount = $expense->amount + $expense->tax_amount;
        });

        static::updating(function ($expense) {
            $expense->total_amount = $expense->amount + $expense->tax_amount;
        });
    }

    /**
     * Get payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Cash',
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_BKASH => 'bKash',
            self::METHOD_NAGAD => 'Nagad',
            self::METHOD_CARD => 'Card',
            self::METHOD_CHECK => 'Check',
        ];
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('expense_date', [$startDate, $endDate]);
    }

    // Relationships

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }
}
