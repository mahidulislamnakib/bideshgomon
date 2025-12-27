<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'description',
        'is_compound',
        'is_default',
        'is_active',
        'applicable_services',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'is_compound' => 'boolean',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'applicable_services' => 'array',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Scope for active taxes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('effective_from', '<=', now())
            ->where(function ($q) {
                $q->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            });
    }

    /**
     * Get the default tax
     */
    public static function getDefault(): ?self
    {
        return static::active()->where('is_default', true)->first();
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(float $amount): float
    {
        return $amount * ($this->rate / 100);
    }

    /**
     * Get formatted rate
     */
    public function getFormattedRateAttribute(): string
    {
        return number_format($this->rate, 2).'%';
    }
}
