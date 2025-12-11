<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Ad extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'body',
        'image_url',
        'cta_url',
        'cta_text',
        'placement',
        'start_at',
        'end_at',
        'priority',
        'status',
        'meta',
        'impressions',
        'clicks',
        'ctr',
    ];

    protected $casts = [
        'meta' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'ctr' => 'decimal:2',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Clear cache when ad is saved or deleted
        static::saved(function () {
            // Use cache tagging if supported (Redis), otherwise clear all ads cache
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags(['ads'])->flush();
            } else {
                Cache::forget('ads_display');
            }
        });

        static::deleted(function () {
            // Use cache tagging if supported (Redis), otherwise clear all ads cache
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags(['ads'])->flush();
            } else {
                Cache::forget('ads_display');
            }
        });
    }

    /**
     * Scope: Active ads only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            });
    }

    /**
     * Scope: By placement
     */
    public function scopePlacement($query, string $placement)
    {
        return $query->where('placement', $placement);
    }

    /**
     * Get ads for specific criteria with caching
     */
    public static function getForDisplay(string $placement, string $page, ?string $userRole = null, string $deviceType = 'desktop'): ?self
    {
        $cacheKey = "ad:{$placement}:{$page}:{$userRole}:{$deviceType}";

        // Use cache tagging if supported (Redis), otherwise use simple cache
        if (method_exists(Cache::getStore(), 'tags')) {
            return Cache::tags(['ads'])->remember($cacheKey, now()->addHours(1), function () use ($placement, $page, $userRole, $deviceType) {
                return static::fetchAd($placement, $page, $userRole, $deviceType);
            });
        } else {
            return Cache::remember($cacheKey, now()->addHours(1), function () use ($placement, $page, $userRole, $deviceType) {
                return static::fetchAd($placement, $page, $userRole, $deviceType);
            });
        }
    }

    /**
     * Fetch ad from database (extracted for caching flexibility)
     */
    protected static function fetchAd(string $placement, string $page, ?string $userRole, string $deviceType): ?self
    {
        return static::active()
            ->placement($placement)
            ->where(function ($query) use ($page, $userRole, $deviceType) {
                // Check targeting rules in meta field
                $query->whereNull('meta')
                    ->orWhereRaw("JSON_CONTAINS(meta->'$.pages', ?)", [json_encode($page)])
                    ->orWhereRaw("JSON_LENGTH(meta->'$.pages') = 0")
                    ->orWhereRaw("meta->'$.pages' IS NULL");

                if ($userRole) {
                    $query->where(function ($q) use ($userRole) {
                        $q->whereRaw("JSON_CONTAINS(meta->'$.roles', ?)", [json_encode($userRole)])
                            ->orWhereRaw("JSON_LENGTH(meta->'$.roles') = 0")
                            ->orWhereRaw("meta->'$.roles' IS NULL");
                    });
                }

                if ($deviceType) {
                    $query->where(function ($q) use ($deviceType) {
                        $q->whereRaw("JSON_CONTAINS(meta->'$.devices', ?)", [json_encode($deviceType)])
                            ->orWhereRaw("JSON_LENGTH(meta->'$.devices') = 0")
                            ->orWhereRaw("meta->'$.devices' IS NULL");
                    });
                }
            })
            ->orderByDesc('priority')
            ->first();
    }

    /**
     * Check if ad matches targeting criteria
     */
    public function matchesTargeting(string $page, ?string $userRole = null, string $deviceType = 'desktop'): bool
    {
        if (! $this->meta) {
            return true;
        }

        // Check page targeting
        if (isset($this->meta['pages']) && ! empty($this->meta['pages'])) {
            if (! in_array($page, $this->meta['pages'])) {
                return false;
            }
        }

        // Check role targeting
        if ($userRole && isset($this->meta['roles']) && ! empty($this->meta['roles'])) {
            if (! in_array($userRole, $this->meta['roles'])) {
                return false;
            }
        }

        // Check device targeting
        if (isset($this->meta['devices']) && ! empty($this->meta['devices'])) {
            if (! in_array($deviceType, $this->meta['devices'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record impression
     */
    public function recordImpression(?int $userId = null, ?string $page = null, ?string $deviceType = null): void
    {
        AdImpression::create([
            'ad_id' => $this->id,
            'user_id' => $userId,
            'page' => $page,
            'device_type' => $deviceType,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        // Increment counter
        $this->increment('impressions');
        $this->updateCtr();
    }

    /**
     * Record click
     */
    public function recordClick(?int $userId = null, ?string $page = null, ?string $deviceType = null): void
    {
        AdClick::create([
            'ad_id' => $this->id,
            'user_id' => $userId,
            'page' => $page,
            'device_type' => $deviceType,
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        // Increment counter
        $this->increment('clicks');
        $this->updateCtr();
    }

    /**
     * Update click-through rate
     */
    protected function updateCtr(): void
    {
        if ($this->impressions > 0) {
            $ctr = ($this->clicks / $this->impressions) * 100;
            $this->updateQuietly(['ctr' => $ctr]);
        }
    }

    /**
     * Get impressions count for date range
     */
    public function getImpressionsForPeriod($startDate, $endDate)
    {
        return $this->hasMany(AdImpression::class)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get clicks count for date range
     */
    public function getClicksForPeriod($startDate, $endDate)
    {
        return $this->hasMany(AdClick::class)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Relationships
     */
    public function impressions()
    {
        return $this->hasMany(AdImpression::class);
    }

    public function clicks()
    {
        return $this->hasMany(AdClick::class);
    }
}
