<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'credit_note_number',
        'invoice_id',
        'client_id',
        'created_by',
        'issue_date',
        'amount',
        'reason',
        'description',
        'status',
        'approved_by',
        'approved_at',
        'applied_at',
        'refund_method',
        'refund_reference',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'approved_at' => 'datetime',
        'applied_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_APPLIED = 'applied';

    const STATUS_VOID = 'void';

    const REASON_REFUND = 'refund';

    const REASON_OVERCHARGE = 'overcharge';

    const REASON_SERVICE_ISSUE = 'service_issue';

    const REASON_CANCELLATION = 'cancellation';

    const REASON_GOODWILL = 'goodwill';

    /**
     * Generate credit note number
     */
    public static function generateCreditNoteNumber(): string
    {
        $year = now()->format('Y');
        $prefix = "CN-{$year}-";

        $lastNote = static::withTrashed()
            ->where('credit_note_number', 'like', $prefix.'%')
            ->orderBy('credit_note_number', 'desc')
            ->first();

        if ($lastNote) {
            $lastNumber = (int) substr($lastNote->credit_note_number, -4);
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

        static::creating(function ($creditNote) {
            if (empty($creditNote->credit_note_number)) {
                $creditNote->credit_note_number = static::generateCreditNoteNumber();
            }
        });
    }

    /**
     * Get reason labels
     */
    public static function getReasons(): array
    {
        return [
            self::REASON_REFUND => 'Customer Refund',
            self::REASON_OVERCHARGE => 'Overcharge Correction',
            self::REASON_SERVICE_ISSUE => 'Service Issue',
            self::REASON_CANCELLATION => 'Order Cancellation',
            self::REASON_GOODWILL => 'Goodwill Gesture',
        ];
    }

    /**
     * Apply credit note to invoice
     */
    public function apply(): void
    {
        $invoice = $this->invoice;

        // Credit the invoice
        $newPaidAmount = min(
            $invoice->total_amount,
            $invoice->paid_amount + $this->amount
        );

        $invoice->update([
            'paid_amount' => $newPaidAmount,
        ]);

        $invoice->updatePaymentStatus();

        $this->update([
            'status' => self::STATUS_APPLIED,
            'applied_at' => now(),
        ]);
    }

    /**
     * Scope by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Relationships

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
