<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Prometheus Metrics Exporter
 *
 * Exposes application metrics in Prometheus format
 * Access: /metrics (should be restricted to monitoring systems)
 */
class MetricsController extends Controller
{
    /**
     * Export metrics in Prometheus format
     */
    public function __invoke(): Response
    {
        $metrics = [];

        // Application info
        $metrics[] = $this->gauge('app_info', [
            'version' => config('app.version', 'unknown'),
            'environment' => config('app.env'),
        ], 1);

        // HTTP metrics (from cache/storage)
        $metrics[] = $this->counter('http_requests_total', $this->getHttpRequestsTotal());
        $metrics[] = $this->histogram('http_request_duration_seconds', $this->getHttpDurations());

        // Database metrics
        $dbMetrics = $this->getDatabaseMetrics();
        $metrics[] = $this->gauge('db_connections_active', [], $dbMetrics['active_connections']);
        $metrics[] = $this->gauge('db_connections_idle', [], $dbMetrics['idle_connections']);
        $metrics[] = $this->histogram('db_query_duration_seconds', $this->getDbQueryDurations());

        // Cache metrics
        $cacheMetrics = $this->getCacheMetrics();
        $metrics[] = $this->counter('cache_hits_total', [], $cacheMetrics['hits']);
        $metrics[] = $this->counter('cache_misses_total', [], $cacheMetrics['misses']);

        // Queue metrics
        $queueMetrics = $this->getQueueMetrics();
        $metrics[] = $this->gauge('queue_jobs_pending', [], $queueMetrics['pending']);
        $metrics[] = $this->gauge('queue_jobs_failed', [], $queueMetrics['failed']);
        $metrics[] = $this->counter('queue_jobs_processed_total', [], $queueMetrics['processed']);

        // Memory usage
        $metrics[] = $this->gauge('php_memory_usage_bytes', [], memory_get_usage(true));
        $metrics[] = $this->gauge('php_memory_peak_bytes', [], memory_get_peak_usage(true));

        // Generate Prometheus text format
        $output = implode("\n", array_filter($metrics));

        return response($output, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    /**
     * Format counter metric
     */
    private function counter(string $name, array $labels = [], float $value = 0): string
    {
        $help = "# HELP {$name} Total count of {$name}\n";
        $type = "# TYPE {$name} counter\n";
        $metric = $this->formatMetric($name, $labels, $value);

        return $help.$type.$metric;
    }

    /**
     * Format gauge metric
     */
    private function gauge(string $name, array $labels = [], float $value = 0): string
    {
        $help = "# HELP {$name} Current value of {$name}\n";
        $type = "# TYPE {$name} gauge\n";
        $metric = $this->formatMetric($name, $labels, $value);

        return $help.$type.$metric;
    }

    /**
     * Format histogram metric
     */
    private function histogram(string $name, array $buckets): string
    {
        if (empty($buckets)) {
            return '';
        }

        $help = "# HELP {$name} Histogram of {$name}\n";
        $type = "# TYPE {$name} histogram\n";
        $metrics = [];

        foreach ($buckets as $bucket) {
            $metrics[] = $this->formatMetric($name.'_bucket', array_merge($bucket['labels'], ['le' => $bucket['le']]), $bucket['count']);
        }

        $metrics[] = $this->formatMetric($name.'_sum', [], $buckets[0]['sum'] ?? 0);
        $metrics[] = $this->formatMetric($name.'_count', [], $buckets[0]['total'] ?? 0);

        return $help.$type.implode("\n", $metrics);
    }

    /**
     * Format individual metric line
     */
    private function formatMetric(string $name, array $labels, float $value): string
    {
        if (empty($labels)) {
            return "{$name} {$value}";
        }

        $labelStr = [];
        foreach ($labels as $key => $val) {
            $labelStr[] = $key.'="'.addslashes($val).'"';
        }

        return $name.'{'.implode(',', $labelStr).'} '.$value;
    }

    /**
     * Get HTTP request counts from cache
     */
    private function getHttpRequestsTotal(): array
    {
        // This would be populated by middleware
        // For now, return mock data structure
        return Cache::remember('metrics:http_requests', 60, function () {
            return [];
        });
    }

    /**
     * Get HTTP duration histogram buckets
     */
    private function getHttpDurations(): array
    {
        // Buckets: 0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1, 2.5, 5, 10, +Inf
        return Cache::remember('metrics:http_durations', 60, function () {
            return [
                ['labels' => [], 'le' => '0.005', 'count' => 0, 'sum' => 0, 'total' => 0],
                ['labels' => [], 'le' => '0.01', 'count' => 0],
                ['labels' => [], 'le' => '0.025', 'count' => 0],
                ['labels' => [], 'le' => '0.05', 'count' => 0],
                ['labels' => [], 'le' => '0.1', 'count' => 0],
                ['labels' => [], 'le' => '0.25', 'count' => 0],
                ['labels' => [], 'le' => '0.5', 'count' => 0],
                ['labels' => [], 'le' => '1', 'count' => 0],
                ['labels' => [], 'le' => '2.5', 'count' => 0],
                ['labels' => [], 'le' => '5', 'count' => 0],
                ['labels' => [], 'le' => '10', 'count' => 0],
                ['labels' => [], 'le' => '+Inf', 'count' => 0],
            ];
        });
    }

    /**
     * Get database query duration histogram
     */
    private function getDbQueryDurations(): array
    {
        return Cache::remember('metrics:db_durations', 60, function () {
            return [
                ['labels' => [], 'le' => '0.001', 'count' => 0, 'sum' => 0, 'total' => 0],
                ['labels' => [], 'le' => '0.005', 'count' => 0],
                ['labels' => [], 'le' => '0.01', 'count' => 0],
                ['labels' => [], 'le' => '0.05', 'count' => 0],
                ['labels' => [], 'le' => '0.1', 'count' => 0],
                ['labels' => [], 'le' => '0.5', 'count' => 0],
                ['labels' => [], 'le' => '+Inf', 'count' => 0],
            ];
        });
    }

    /**
     * Get database connection metrics
     */
    private function getDatabaseMetrics(): array
    {
        try {
            // This is MySQL-specific, adjust for your database
            $result = DB::select("SHOW STATUS WHERE Variable_name IN ('Threads_connected', 'Threads_running')");

            $metrics = [
                'active_connections' => 0,
                'idle_connections' => 0,
            ];

            foreach ($result as $row) {
                if ($row->Variable_name === 'Threads_connected') {
                    $metrics['active_connections'] = (int) $row->Value;
                }
                if ($row->Variable_name === 'Threads_running') {
                    $metrics['idle_connections'] = $metrics['active_connections'] - (int) $row->Value;
                }
            }

            return $metrics;
        } catch (\Exception $e) {
            return ['active_connections' => 0, 'idle_connections' => 0];
        }
    }

    /**
     * Get cache hit/miss metrics
     */
    private function getCacheMetrics(): array
    {
        return Cache::remember('metrics:cache', 60, function () {
            return [
                'hits' => 0,
                'misses' => 0,
            ];
        });
    }

    /**
     * Get queue metrics
     */
    private function getQueueMetrics(): array
    {
        try {
            $connection = config('queue.default');

            if ($connection === 'database') {
                $pending = DB::table('jobs')->count();
                $failed = DB::table('failed_jobs')->count();
            } else {
                $pending = 0;
                $failed = 0;
            }

            $processed = Cache::get('metrics:queue_processed', 0);

            return [
                'pending' => $pending,
                'failed' => $failed,
                'processed' => $processed,
            ];
        } catch (\Exception $e) {
            return ['pending' => 0, 'failed' => 0, 'processed' => 0];
        }
    }
}
