<?php

namespace Tests\Unit;

use App\Services\FeatureFlagService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class FeatureFlagServiceTest extends TestCase
{
    private FeatureFlagService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new FeatureFlagService;
        Cache::flush();
    }

    public function test_feature_flag_returns_false_for_non_existent_feature(): void
    {
        $this->assertFalse($this->service->isEnabled('non_existent_feature'));
    }

    public function test_feature_flag_respects_enabled_status(): void
    {
        Config::set('features.test_feature', [
            'enabled' => true,
            'rollout_percentage' => 100,
        ]);

        $this->assertTrue($this->service->isEnabled('test_feature'));
    }

    public function test_feature_flag_respects_disabled_status(): void
    {
        Config::set('features.test_feature', [
            'enabled' => false,
            'rollout_percentage' => 100,
        ]);

        $this->assertFalse($this->service->isEnabled('test_feature'));
    }

    public function test_feature_flag_respects_rollout_percentage(): void
    {
        Config::set('features.test_feature', [
            'enabled' => true,
            'rollout_percentage' => 0,
        ]);

        $this->assertFalse($this->service->isEnabled('test_feature'));

        Config::set('features.test_feature.rollout_percentage', 100);
        Cache::flush();

        $this->assertTrue($this->service->isEnabled('test_feature'));
    }

    public function test_enable_sets_feature_to_100_percent(): void
    {
        $this->service->enable('test_feature');
        $this->assertTrue($this->service->isEnabled('test_feature'));
    }

    public function test_disable_sets_feature_to_0_percent(): void
    {
        $this->service->enable('test_feature');
        $this->assertTrue($this->service->isEnabled('test_feature'));

        $this->service->disable('test_feature');
        $this->assertFalse($this->service->isEnabled('test_feature'));
    }

    public function test_set_rollout_validates_percentage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service->setRollout('test_feature', 150);
    }

    public function test_feature_caching_works(): void
    {
        $this->service->enable('test_feature');

        // Should be cached
        $this->assertTrue(Cache::has('feature_flag:test_feature'));

        // Should return cached value
        $this->assertTrue($this->service->isEnabled('test_feature'));
    }

    public function test_clear_all_caches_removes_all_feature_caches(): void
    {
        $this->service->enable('feature_1');
        $this->service->enable('feature_2');

        $this->service->clearAllCaches();

        $this->assertFalse(Cache::has('feature_flag:feature_1'));
        $this->assertFalse(Cache::has('feature_flag:feature_2'));
    }
}
