<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class University extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country_id',
        'city',
        'state',
        'world_ranking',
        'country_ranking',
        'tuition_min',
        'tuition_max',
        'logo',
        'website',
        'established_year',
        'type',
        'acceptance_rate',
        'student_count',
        'international_students',
        'scholarships_available',
        'application_fee',
        'description',
        'programs',
        'popular_majors',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'tuition_min' => 'decimal:2',
        'tuition_max' => 'decimal:2',
        'acceptance_rate' => 'decimal:2',
        'application_fee' => 'decimal:2',
        'scholarships_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'programs' => 'array',
        'popular_majors' => 'array',
    ];

    /**
     * Get the country that the university belongs to.
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Scope to filter by country.
     */
    public function scopeByCountry($query, $countryId)
    {
        return $query->where('country_id', $countryId);
    }

    /**
     * Scope to filter by ranking range.
     */
    public function scopeByRankingRange($query, $min = null, $max = null)
    {
        if ($min) {
            $query->where('world_ranking', '>=', $min);
        }
        if ($max) {
            $query->where('world_ranking', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope to filter by tuition range.
     */
    public function scopeByTuitionRange($query, $max = null)
    {
        if ($max) {
            $query->where('tuition_max', '<=', $max);
        }

        return $query;
    }

    /**
     * Scope to search universities.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('state', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter featured universities.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter active universities.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted tuition range.
     */
    public function getFormattedTuitionAttribute()
    {
        if (! $this->tuition_min && ! $this->tuition_max) {
            return 'N/A';
        }

        if ($this->tuition_min && $this->tuition_max) {
            return '$'.number_format((float) $this->tuition_min, 0).' - $'.number_format((float) $this->tuition_max, 0);
        }

        return '$'.number_format((float) ($this->tuition_max ?? $this->tuition_min), 0);
    }
}
