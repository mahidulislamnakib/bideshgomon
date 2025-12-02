<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Package extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_module_id',
        'name',
        'slug',
        'description',
        'short_description',
        'price',
        'original_price',
        'currency',
        'price_unit',
        'features',
        'specifications',
        'inclusions',
        'exclusions',
        'is_popular',
        'is_featured',
        'is_active',
        'badge_text',
        'badge_color',
        'sort_order',
        'max_bookings',
        'current_bookings',
        'available_from',
        'available_until',
    ];

    protected $casts = [
        'features' => 'array',
        'specifications' => 'array',
        'inclusions' => 'array',
        'exclusions' => 'array',
        'is_popular' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'max_bookings' => 'integer',
        'current_bookings' => 'integer',
        'available_from' => 'date',
        'available_until' => 'date',
    ];

    // Relationships
    public function serviceModule(): BelongsTo
    {
        return $this->belongsTo(ServiceModule::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByService($query, $serviceSlug)
    {
        return $query->whereHas('serviceModule', function ($q) use ($serviceSlug) {
            $q->where('slug', $serviceSlug);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeAvailable($query)
    {
        $now = now();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                $subQ->whereNull('available_from')
                    ->orWhere('available_from', '<=', $now);
            })->where(function ($subQ) use ($now) {
                $subQ->whereNull('available_until')
                    ->orWhere('available_until', '>=', $now);
            });
        });
    }

    // Accessors
    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->original_price && $this->original_price > $this->price) {
            return round((($this->original_price - $this->price) / $this->original_price) * 100);
        }
        return null;
    }

    public function getFormattedPriceAttribute(): string
    {
        $formatted = '৳' . number_format($this->price, 0);
        
        if ($this->price_unit !== 'fixed') {
            $formatted .= ' / ' . str_replace('_', ' ', $this->price_unit);
        }
        
        return $formatted;
    }

    public function getIsAvailableAttribute(): bool
    {
        $now = now();
        
        // Check date availability
        if ($this->available_from && $now->lt($this->available_from)) {
            return false;
        }
        
        if ($this->available_until && $now->gt($this->available_until)) {
            return false;
        }
        
        // Check booking limits
        if ($this->max_bookings && $this->current_bookings >= $this->max_bookings) {
            return false;
        }
        
        return $this->is_active;
    }

    public function getSpotsLeftAttribute(): ?int
    {
        if (!$this->max_bookings) {
            return null; // Unlimited
        }
        
        return max(0, $this->max_bookings - $this->current_bookings);
    }
}
