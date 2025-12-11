<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Feature Flags Service
 *
 * Manages feature toggles for safe rollouts and A/B testing
 * Naming convention: <domain>_<feature>_<date>
 * Example: visa_new_flow_20250101
 */
class FeatureFlagService
{
    private const CACHE_PREFIX = 'feature_flag:';

    private const CACHE_TTL = 300; // 5 minutes

    /**
     * Check if a feature is enabled
     *
     * @param  string  $feature  Feature flag name
     * @param  mixed  $context  Optional context (user, request, etc.)
     */
    public function isEnabled(string $feature, $context = null): bool
    {
        $cacheKey = self::CACHE_PREFIX.$feature;

        // Check cache first
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $this->evaluateFlag($cached, $context);
        }

        // Load from config or database
        $config = $this->loadFeatureConfig($feature);

        if ($config === null) {
            // Feature not found, default to false
            Log::warning("Feature flag not found: {$feature}");

            return false;
        }

        // Cache the config
        Cache::put($cacheKey, $config, self::CACHE_TTL);

        return $this->evaluateFlag($config, $context);
    }

    /**
     * Enable a feature globally
     */
    public function enable(string $feature): void
    {
        $this->setFeatureConfig($feature, [
            'enabled' => true,
            'rollout_percentage' => 100,
        ]);

        $this->clearCache($feature);
        Log::info("Feature enabled: {$feature}");
    }

    /**
     * Disable a feature globally
     */
    public function disable(string $feature): void
    {
        $this->setFeatureConfig($feature, [
            'enabled' => false,
            'rollout_percentage' => 0,
        ]);

        $this->clearCache($feature);
        Log::info("Feature disabled: {$feature}");
    }

    /**
     * Set gradual rollout percentage (0-100)
     */
    public function setRollout(string $feature, int $percentage): void
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new \InvalidArgumentException('Rollout percentage must be between 0 and 100');
        }

        $this->setFeatureConfig($feature, [
            'enabled' => true,
            'rollout_percentage' => $percentage,
        ]);

        $this->clearCache($feature);
        Log::info("Feature rollout updated: {$feature} => {$percentage}%");
    }

    /**
     * Enable feature for specific users
     */
    public function enableForUsers(string $feature, array $userIds): void
    {
        $config = $this->loadFeatureConfig($feature) ?? [];
        $config['enabled'] = true;
        $config['user_whitelist'] = array_merge($config['user_whitelist'] ?? [], $userIds);

        $this->setFeatureConfig($feature, $config);
        $this->clearCache($feature);

        Log::info("Feature enabled for users: {$feature}", ['users' => $userIds]);
    }

    /**
     * Get all features and their status
     */
    public function getAllFeatures(): array
    {
        return config('features', []);
    }

    /**
     * Evaluate flag based on config and context
     */
    private function evaluateFlag(array $config, $context): bool
    {
        // Check if globally disabled
        if (! ($config['enabled'] ?? false)) {
            return false;
        }

        // Check user whitelist
        if (isset($config['user_whitelist']) && $context && is_object($context) && isset($context->id)) {
            if (in_array($context->id, $config['user_whitelist'])) {
                return true;
            }
        }

        // Check rollout percentage
        $rolloutPercentage = $config['rollout_percentage'] ?? 0;

        if ($rolloutPercentage === 100) {
            return true;
        }

        if ($rolloutPercentage === 0) {
            return false;
        }

        // Consistent hashing for gradual rollout
        $hash = $this->getConsistentHash($config['name'] ?? 'default', $context);

        return ($hash % 100) < $rolloutPercentage;
    }

    /**
     * Generate consistent hash for user/session
     */
    private function getConsistentHash(string $feature, $context): int
    {
        $identifier = 'anonymous';

        if ($context && is_object($context)) {
            if (isset($context->id)) {
                $identifier = 'user_'.$context->id;
            } elseif (isset($context->session)) {
                $identifier = 'session_'.$context->session()->getId();
            }
        }

        return abs(crc32($feature.':'.$identifier));
    }

    /**
     * Load feature config from config file or database
     */
    private function loadFeatureConfig(string $feature): ?array
    {
        // First check config file
        $features = config('features', []);

        if (isset($features[$feature])) {
            return array_merge(['name' => $feature], $features[$feature]);
        }

        // Could also load from database here
        // return FeatureFlag::where('name', $feature)->first()?->toArray();

        return null;
    }

    /**
     * Set feature configuration
     */
    private function setFeatureConfig(string $feature, array $config): void
    {
        // In production, this would update the database
        // For now, we'll use cache
        Cache::put(self::CACHE_PREFIX.$feature, array_merge(['name' => $feature], $config), now()->addHours(24));
    }

    /**
     * Clear feature cache
     */
    private function clearCache(string $feature): void
    {
        Cache::forget(self::CACHE_PREFIX.$feature);
    }

    /**
     * Clear all feature caches
     */
    public function clearAllCaches(): void
    {
        $features = $this->getAllFeatures();
        foreach (array_keys($features) as $feature) {
            $this->clearCache($feature);
        }

        Log::info('All feature flag caches cleared');
    }
}
