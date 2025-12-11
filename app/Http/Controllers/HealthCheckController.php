<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthCheckController extends Controller
{
    /**
     * Comprehensive health check endpoint for deployment validation
     * Returns 200 if all systems operational, 503 if any critical service is down
     */
    public function __invoke(): JsonResponse
    {
        $startTime = microtime(true);
        $checks = [];
        $overallHealthy = true;

        // 1. Database connectivity check
        $checks['database'] = $this->checkDatabase();
        if ($checks['database']['status'] !== 'ok') {
            $overallHealthy = false;
        }

        // 2. Cache connectivity check (Redis/Memcached)
        $checks['cache'] = $this->checkCache();
        if ($checks['cache']['status'] !== 'ok') {
            $overallHealthy = false;
        }

        // 3. Queue connectivity check
        $checks['queue'] = $this->checkQueue();
        if ($checks['queue']['status'] !== 'ok') {
            $overallHealthy = false;
        }

        // 4. Storage check (disk space)
        $checks['storage'] = $this->checkStorage();
        if ($checks['storage']['status'] !== 'ok') {
            $overallHealthy = false;
        }

        // 5. Application readiness
        $checks['application'] = [
            'status' => 'ok',
            'environment' => config('app.env'),
            'debug' => config('app.debug'),
            'version' => config('app.version', 'unknown'),
        ];

        // Response time
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);

        $status = $overallHealthy ? 200 : 503;

        return response()->json([
            'status' => $overallHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toIso8601String(),
            'response_time_ms' => $responseTime,
            'checks' => $checks,
        ], $status);
    }

    /**
     * Check database connectivity and query performance
     */
    private function checkDatabase(): array
    {
        try {
            $startTime = microtime(true);

            // Try simple query
            DB::connection()->getPdo();
            DB::select('SELECT 1');

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'status' => 'ok',
                'connection' => DB::connection()->getName(),
                'response_time_ms' => $duration,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check cache connectivity (Redis/Memcached)
     */
    private function checkCache(): array
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_'.time();
            $testValue = 'test';

            // Write test
            Cache::put($testKey, $testValue, 10);

            // Read test
            $retrieved = Cache::get($testKey);

            // Cleanup
            Cache::forget($testKey);

            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($retrieved !== $testValue) {
                return [
                    'status' => 'error',
                    'message' => 'Cache read/write failed',
                ];
            }

            return [
                'status' => 'ok',
                'driver' => config('cache.default'),
                'response_time_ms' => $duration,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Cache connection failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check queue connectivity
     */
    private function checkQueue(): array
    {
        try {
            // For database queue, check connection
            // For Redis queue, check Redis connection
            $connection = config('queue.default');

            if ($connection === 'database') {
                // Already checked in database check
                return [
                    'status' => 'ok',
                    'driver' => 'database',
                ];
            }

            if ($connection === 'redis') {
                Redis::connection(config('queue.connections.redis.connection', 'default'))->ping();

                return [
                    'status' => 'ok',
                    'driver' => 'redis',
                ];
            }

            return [
                'status' => 'ok',
                'driver' => $connection,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Queue connection failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Connection error',
            ];
        }
    }

    /**
     * Check storage availability and disk space
     */
    private function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $diskFree = disk_free_space($storagePath);
            $diskTotal = disk_total_space($storagePath);
            $percentFree = round(($diskFree / $diskTotal) * 100, 2);

            $status = $percentFree > 10 ? 'ok' : 'warning';

            return [
                'status' => $status,
                'path' => $storagePath,
                'free_space_gb' => round($diskFree / 1024 / 1024 / 1024, 2),
                'total_space_gb' => round($diskTotal / 1024 / 1024 / 1024, 2),
                'percent_free' => $percentFree,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Check error',
            ];
        }
    }
}
