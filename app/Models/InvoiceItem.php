<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'tax_amount',
        'discount',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot method to calculate amount on save
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $item->tax_amount = $subtotal * ($item->tax_rate / 100);
            $item->amount = $subtotal - $item->discount + $item->tax_amount;
        });

        static::saved(function ($item) {
            $item->invoice->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->invoice->calculateTotals();
        });
    }

    // Relationships

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
