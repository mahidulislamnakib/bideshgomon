<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceModule extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'slug',
        'service_type',
        'requires_form_submission',
        'has_delivery_charges',
        'default_delivery_charge',
        'is_query_based',
        'is_marketplace',
        'supports_university_exclusivity',
        'restricted_to_agency_type_id',
        'short_description',
        'full_description',
        'icon',
        'image',
        'is_active',
        'is_featured',
        'coming_soon',
        'launch_date',
        'base_price',
        'price_type',
        'currency',
        'requirements',
        'documents_required',
        'processing_time',
        'processing_days',
        'requires_approval',
        'settings',
        'config',
        'route_prefix',
        'controller',
        'allowed_roles',
        'min_profile_completion',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'views_count',
        'applications_count',
        'completions_count',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'coming_soon' => 'boolean',
        'requires_approval' => 'boolean',
        'requires_form_submission' => 'boolean',
        'has_delivery_charges' => 'boolean',
        'is_query_based' => 'boolean',
        'is_marketplace' => 'boolean',
        'supports_university_exclusivity' => 'boolean',
        'launch_date' => 'date',
        'base_price' => 'decimal:2',
        'default_delivery_charge' => 'decimal:2',
        'requirements' => 'array',
        'documents_required' => 'array',
        'processing_time' => 'array',
        'processing_days' => 'integer',
        'settings' => 'array',
        'config' => 'array',
        'allowed_roles' => 'array',
        'min_profile_completion' => 'integer',
        'views_count' => 'integer',
        'applications_count' => 'integer',
        'completions_count' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the category this module belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    /**
     * Get all applications for this service
     */
    public function applications(): HasMany
    {
        return $this->hasMany(ServiceApplication::class);
    }

    /**
     * Get all reviews for this service
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class);
    }

    /**
     * Get approved reviews only
     */
    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ServiceReview::class)->where('is_approved', true);
    }

    /**
     * Get all form fields for this service (Dynamic Form Builder)
     */
    public function formFields(): HasMany
    {
        return $this->hasMany(ServiceFormField::class)->orderBy('sort_order');
    }

    /**
     * Get active form fields only
     */
    public function activeFormFields(): HasMany
    {
        return $this->hasMany(ServiceFormField::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /**
     * Scope: Only active services
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Featured services
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true)->where('is_active', true);
    }

    /**
     * Scope: Coming soon services
     */
    public function scopeComingSoon($query)
    {
        return $query->where('coming_soon', true);
    }

    /**
     * Scope: Available now (active and not coming soon)
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)->where('coming_soon', false);
    }

    /**
     * Scope: Ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Increment views count
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Increment applications count
     */
    public function incrementApplications(): void
    {
        $this->increment('applications_count');
    }

    /**
     * Increment completions count
     */
    public function incrementCompletions(): void
    {
        $this->increment('completions_count');
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->approvedReviews()->avg('rating') ?? 0, 1);
    }

    /**
     * Get total reviews count
     */
    public function getReviewsCountAttribute(): int
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Check if user can access this service
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Check if service is active
        if (! $this->is_active || $this->coming_soon) {
            return false;
        }

        // Check if user's role is allowed
        if (! empty($this->allowed_roles) && ! in_array($user->role->name, $this->allowed_roles)) {
            return false;
        }

        // Check profile completion requirement
        if ($this->min_profile_completion > 0) {
            // Calculate user's profile completion percentage
            // This would need to be implemented in User model
            $profileCompletion = $user->profile_completion_percentage ?? 0;
            if ($profileCompletion < $this->min_profile_completion) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_type === 'free') {
            return 'Free';
        }

        if ($this->price_type === 'quote') {
            return 'Get Quote';
        }

        $symbol = $this->currency === 'BDT' ? '৳' : '$';
        $price = (float) $this->base_price; // Cast to float to prevent type errors

        if ($this->price_type === 'variable') {
            return $symbol.number_format($price, 0).'+';
        }

        return $symbol.number_format($price, 0);
    }

    /**
     * Get processing time text
     */
    public function getProcessingTimeTextAttribute(): ?string
    {
        if (empty($this->processing_time)) {
            return null;
        }

        $min = $this->processing_time['min'] ?? 0;
        $max = $this->processing_time['max'] ?? 0;
        $unit = $this->processing_time['unit'] ?? 'days';

        if ($min === $max) {
            return "{$min} {$unit}";
        }

        return "{$min}-{$max} {$unit}";
    }

    /**
     * Get completion rate percentage
     */
    public function getCompletionRateAttribute(): float
    {
        if ($this->applications_count === 0) {
            return 0;
        }

        return round(($this->completions_count / $this->applications_count) * 100, 1);
    }

    /**
     * Get restricted agency type relationship
     */
    public function restrictedToAgencyType(): BelongsTo
    {
        return $this->belongsTo(AgencyType::class, 'restricted_to_agency_type_id');
    }

    /**
     * Check if service is restricted to a specific agency type
     */
    public function isRestrictedToAgencyType(): bool
    {
        return ! is_null($this->restricted_to_agency_type_id);
    }

    /**
     * Check if an agency can offer this service
     */
    public function canAgencyOffer(Agency $agency): bool
    {
        // Check if service is restricted to specific agency type
        if ($this->isRestrictedToAgencyType()) {
            return $agency->agency_type_id === $this->restricted_to_agency_type_id;
        }

        // Check if service is in agency's allowed service modules
        $allowedModules = $agency->agencyType->allowed_service_modules ?? [];

        return in_array($this->id, $allowedModules);
    }

    /**
     * Check if service supports multiple agencies (marketplace model)
     */
    public function supportsMultipleAgencies(): bool
    {
        return $this->is_marketplace === true;
    }

    /**
     * Check if service requires delivery
     */
    public function requiresDelivery(): bool
    {
        return $this->has_delivery_charges === true;
    }

    /**
     * Check if service supports university exclusivity
     */
    public function hasUniversityExclusivity(): bool
    {
        return $this->supports_university_exclusivity === true;
    }

    /**
     * Scope: Marketplace services (multiple agencies can compete)
     */
    public function scopeMarketplace($query)
    {
        return $query->where('is_marketplace', true);
    }

    /**
     * Scope: Query-based services (require form submission)
     */
    public function scopeQueryBased($query)
    {
        return $query->where('is_query_based', true);
    }

    /**
     * Scope: Services with delivery charges
     */
    public function scopeWithDelivery($query)
    {
        return $query->where('has_delivery_charges', true);
    }

    /**
     * Scope: Services restricted to specific agency type
     */
    public function scopeRestrictedToAgencyType($query, $agencyTypeId)
    {
        return $query->where('restricted_to_agency_type_id', $agencyTypeId);
    }
}
