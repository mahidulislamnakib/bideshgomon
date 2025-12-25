<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'client_id',
        'created_by',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'paid_amount',
        'status',
        'notes',
        'terms',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_PARTIAL = 'partial';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Generate next invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "BG-{$year}-";
        
        $lastInvoice = static::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate and update totals
     */
    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum('amount');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount - $this->discount_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Get balance due
     */
    public function getBalanceDueAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    /**
     * Check if invoice is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date->isPast() && 
               !in_array($this->status, [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    /**
     * Update status based on payments
     */
    public function updatePaymentStatus(): void
    {
        $paidAmount = $this->payments()->sum('amount');
        $this->paid_amount = $paidAmount;

        if ($paidAmount >= $this->total_amount) {
            $this->status = self::STATUS_PAID;
        } elseif ($paidAmount > 0) {
            $this->status = self::STATUS_PARTIAL;
        } elseif ($this->is_overdue && $this->status !== self::STATUS_DRAFT) {
            $this->status = self::STATUS_OVERDUE;
        }

        $this->save();
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', [self::STATUS_PAID, self::STATUS_CANCELLED]);
    }

    /**
     * Scope: Unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_PARTIAL, self::STATUS_OVERDUE]);
    }

    /**
     * Scope: Date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('issue_date', [$startDate, $endDate]);
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'desc');
    }
}
