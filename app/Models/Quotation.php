<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'client_id',
        'created_by',
        'client_name',
        'client_email',
        'client_phone',
        'client_address',
        'issue_date',
        'valid_until',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'converted_invoice_id',
        'sent_at',
        'viewed_at',
        'accepted_at',
        'rejected_at',
        'notes',
        'terms',
        'rejection_reason',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'viewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    const STATUS_DRAFT = 'draft';

    const STATUS_SENT = 'sent';

    const STATUS_VIEWED = 'viewed';

    const STATUS_ACCEPTED = 'accepted';

    const STATUS_REJECTED = 'rejected';

    const STATUS_EXPIRED = 'expired';

    const STATUS_CONVERTED = 'converted';

    /**
     * Generate quotation number
     */
    public static function generateQuotationNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "QT-{$year}-";

        $lastQuotation = static::withTrashed()
            ->where('quotation_number', 'like', $prefix.'%')
            ->orderBy('quotation_number', 'desc')
            ->first();

        if ($lastQuotation) {
            $lastNumber = (int) substr($lastQuotation->quotation_number, -4);
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

        static::creating(function ($quotation) {
            if (empty($quotation->quotation_number)) {
                $quotation->quotation_number = static::generateQuotationNumber();
            }
        });
    }

    /**
     * Calculate totals from items
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
     * Check if quotation is expired
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until < now() && ! in_array($this->status, [
            self::STATUS_ACCEPTED,
            self::STATUS_CONVERTED,
        ]);
    }

    /**
     * Convert to invoice
     */
    public function convertToInvoice(): Invoice
    {
        $invoice = Invoice::create([
            'client_id' => $this->client_id,
            'created_by' => auth()->id(),
            'issue_date' => now(),
            'due_date' => now()->addDays(30),
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'discount_amount' => $this->discount_amount,
            'total_amount' => $this->total_amount,
            'status' => Invoice::STATUS_DRAFT,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'client_address' => $this->client_address,
        ]);

        // Copy items
        foreach ($this->items as $item) {
            $invoice->items()->create([
                'service_id' => $item->service_id,
                'description' => $item->description,
                'details' => $item->details,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
                'discount_percent' => $item->discount_percent,
                'amount' => $item->amount,
                'sort_order' => $item->sort_order,
            ]);
        }

        $this->update([
            'status' => self::STATUS_CONVERTED,
            'converted_invoice_id' => $invoice->id,
        ]);

        return $invoice;
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for pending quotations
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [self::STATUS_SENT, self::STATUS_VIEWED])
            ->where('valid_until', '>=', now());
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function convertedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'converted_invoice_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }
}
