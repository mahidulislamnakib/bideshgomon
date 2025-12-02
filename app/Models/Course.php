<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Course extends Model
{
    protected $fillable = [
        'university_id',
        'code',
        'name',
        'description',
        'subject',
        'level',
        'degree_type',
        'duration_months',
        'study_mode',
        'tuition_fee',
        'currency',
        'start_date',
        'application_deadline',
        'credits',
        'prerequisites',
        'learning_outcomes',
        'language',
        'enrollment_capacity',
        'current_enrollment',
        'average_rating',
        'reviews_count',
        'scholarships_available',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'tuition_fee' => 'decimal:2',
        'average_rating' => 'decimal:2',
        'start_date' => 'date',
        'application_deadline' => 'date',
        'prerequisites' => 'array',
        'learning_outcomes' => 'array',
        'scholarships_available' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function university(): BelongsTo
    {
        return $this->belongsTo(University::class);
    }

    // Query Scopes
    public function scopeByUniversity($query, $universityId)
    {
        return $query->where('university_id', $universityId);
    }

    public function scopeBySubject($query, $subject)
    {
        return $query->where('subject', $subject);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeByStudyMode($query, $studyMode)
    {
        return $query->where('study_mode', $studyMode);
    }

    public function scopeByTuitionRange($query, $max)
    {
        return $query->where('tuition_fee', '<=', $max);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('subject', 'like', "%{$search}%");
        });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
                     ->where(function ($q) {
                         $q->whereNull('enrollment_capacity')
                           ->orWhereRaw('current_enrollment < enrollment_capacity');
                     });
    }

    // Accessors
    public function getFormattedTuitionAttribute()
    {
        if (!$this->tuition_fee) {
            return 'Contact for pricing';
        }

        $symbol = $this->currency === 'USD' ? '$' : $this->currency . ' ';
        return $symbol . number_format((float)$this->tuition_fee, 0);
    }

    public function getFormattedDurationAttribute()
    {
        $months = $this->duration_months;
        $years = floor($months / 12);
        $remainingMonths = $months % 12;

        if ($years > 0 && $remainingMonths > 0) {
            return "{$years} year" . ($years > 1 ? 's' : '') . " {$remainingMonths} month" . ($remainingMonths > 1 ? 's' : '');
        } elseif ($years > 0) {
            return "{$years} year" . ($years > 1 ? 's' : '');
        } else {
            return "{$months} month" . ($months > 1 ? 's' : '');
        }
    }

    public function getAvailabilityStatusAttribute()
    {
        if (!$this->is_active) {
            return 'Inactive';
        }

        if (!$this->enrollment_capacity) {
            return 'Open';
        }

        $available = $this->enrollment_capacity - $this->current_enrollment;

        if ($available <= 0) {
            return 'Full';
        } elseif ($available <= 5) {
            return 'Limited Seats';
        } else {
            return 'Open';
        }
    }
}
