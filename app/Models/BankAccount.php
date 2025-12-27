<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_name',
        'account_number',
        'bank_name',
        'branch_name',
        'routing_number',
        'swift_code',
        'account_type',
        'currency',
        'opening_balance',
        'current_balance',
        'mobile_banking_provider',
        'mobile_number',
        'is_primary',
        'is_active',
        'show_on_invoice',
        'notes',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_primary' => 'boolean',
        'is_active' => 'boolean',
        'show_on_invoice' => 'boolean',
    ];

    const TYPE_CURRENT = 'current';

    const TYPE_SAVINGS = 'savings';

    const TYPE_MOBILE_BANKING = 'mobile_banking';

    const MOBILE_PROVIDERS = [
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
        'upay' => 'Upay',
    ];

    /**
     * Get account types
     */
    public static function getAccountTypes(): array
    {
        return [
            self::TYPE_CURRENT => 'Current Account',
            self::TYPE_SAVINGS => 'Savings Account',
            self::TYPE_MOBILE_BANKING => 'Mobile Banking',
        ];
    }

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->account_type === self::TYPE_MOBILE_BANKING) {
            $provider = self::MOBILE_PROVIDERS[$this->mobile_banking_provider] ?? $this->mobile_banking_provider;

            return "{$provider} - {$this->mobile_number}";
        }

        return "{$this->bank_name} - {$this->account_number}";
    }

    /**
     * Get masked account number
     */
    public function getMaskedAccountNumberAttribute(): string
    {
        $number = $this->account_number;
        if (strlen($number) > 4) {
            return str_repeat('*', strlen($number) - 4).substr($number, -4);
        }

        return $number;
    }

    /**
     * Update balance
     */
    public function updateBalance(float $amount, string $type = 'credit'): void
    {
        if ($type === 'credit') {
            $this->increment('current_balance', $amount);
        } else {
            $this->decrement('current_balance', $amount);
        }
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for invoice display
     */
    public function scopeForInvoice($query)
    {
        return $query->where('show_on_invoice', true)->where('is_active', true);
    }

    // Relationships

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
