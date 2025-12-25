<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'service_category_id',
        'provider_id',
        'provider_type',
        'title',
        'name', // alias for title
        'slug',
        'description',
        'features',
        'requirements',
        'base_price',
        'price', // alias for base_price
        'agency_posted_price',
        'admin_approved_price',
        'processing_fee',
        'currency',
        'is_negotiable',
        'processing_days',
        'country',
        'visa_type',
        'documents_required',
        'status',
        'is_active',
        'is_featured',
        'max_bookings_per_day',
        'total_bookings',
        'average_rating',
        'total_reviews',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'features' => 'array',
        'requirements' => 'array',
        'documents_required' => 'array',
        'base_price' => 'decimal:2',
        'price' => 'decimal:2',
        'agency_posted_price' => 'decimal:2',
        'admin_approved_price' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'is_negotiable' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the name attribute (alias for title)
     */
    public function getNameAttribute()
    {
        return $this->title ?? $this->attributes['name'] ?? null;
    }

    /**
     * Get the price attribute (alias for base_price)
     */
    public function getPriceAttribute()
    {
        return $this->base_price ?? $this->attributes['price'] ?? 0;
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'approved');
    }

    /**
     * Relationships
     */
    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
