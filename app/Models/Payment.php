<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'received_by',
        'amount',
        'payment_date',
        'payment_method',
        'transaction_id',
        'reference_number',
        'notes',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    const METHOD_BKASH = 'bkash';

    const METHOD_NAGAD = 'nagad';

    const METHOD_ROCKET = 'rocket';

    const METHOD_BANK = 'bank_transfer';

    const METHOD_CASH = 'cash';

    const METHOD_CARD = 'card';

    const METHOD_CHECK = 'check';

    const METHOD_OTHER = 'other';

    const STATUS_PENDING = 'pending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    const STATUS_REFUNDED = 'refunded';

    /**
     * Get available payment methods with labels
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_BKASH => 'bKash',
            self::METHOD_NAGAD => 'Nagad',
            self::METHOD_ROCKET => 'Rocket',
            self::METHOD_BANK => 'Bank Transfer',
            self::METHOD_CASH => 'Cash',
            self::METHOD_CARD => 'Card',
            self::METHOD_CHECK => 'Check',
            self::METHOD_OTHER => 'Other',
        ];
    }

    /**
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Boot method to update invoice after payment changes
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });
    }

    // Relationships

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
