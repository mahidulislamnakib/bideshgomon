<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_type',
        'name',
        'company_name',
        'email',
        'phone',
        'alternate_phone',
        'address',
        'city',
        'country',
        'postal_code',
        'tax_id',
        'nid',
        'passport_number',
        'credit_limit',
        'outstanding_balance',
        'payment_terms',
        'currency',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    const TYPE_INDIVIDUAL = 'individual';

    const TYPE_BUSINESS = 'business';

    const TYPE_AGENCY = 'agency';

    const PAYMENT_TERMS = [
        'due_on_receipt' => 'Due on Receipt',
        'net_7' => 'Net 7 Days',
        'net_15' => 'Net 15 Days',
        'net_30' => 'Net 30 Days',
        'net_60' => 'Net 60 Days',
    ];

    /**
     * Get display name
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->client_type === self::TYPE_BUSINESS && $this->company_name) {
            return $this->company_name;
        }

        return $this->name;
    }

    /**
     * Scope for active clients
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by client type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('client_type', $type);
    }

    /**
     * Update outstanding balance
     */
    public function updateOutstandingBalance(): void
    {
        $outstanding = $this->invoices()
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->sum('total_amount');

        $paid = $this->invoices()
            ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PARTIAL, Invoice::STATUS_OVERDUE])
            ->sum('paid_amount');

        $this->update([
            'outstanding_balance' => $outstanding - $paid,
        ]);
    }

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(CreditNote::class);
    }
}
