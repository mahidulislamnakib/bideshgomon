<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'service_id',
        'description',
        'details',
        'quantity',
        'unit',
        'unit_price',
        'discount_percent',
        'amount',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    /**
     * Boot method to calculate amount
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $subtotal = $item->quantity * $item->unit_price;
            $discount = $subtotal * ($item->discount_percent / 100);
            $item->amount = $subtotal - $discount;
        });

        static::saved(function ($item) {
            $item->quotation->calculateTotals();
        });

        static::deleted(function ($item) {
            $item->quotation->calculateTotals();
        });
    }

    // Relationships

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
