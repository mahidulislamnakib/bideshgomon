<?php

namespace App\Services;

use App\Models\JobApplication;
use App\Models\JobPosting;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get user registration chart data for a date range
     * Optimized to use single query instead of N queries in a loop
     *
     * @param  int  $days  Number of days to include
     */
    public function getUserGrowthChart(int $days = 30): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        // Single query with grouping - 1 query instead of N queries
        $data = User::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates with zeros for consistent chart display
        $chart = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chart[] = [
                'date' => $date->format('M d'),
                'count' => $data[$date->format('Y-m-d')]->count ?? 0,
            ];
        }

        return $chart;
    }

    /**
     * Get revenue chart data for a date range
     * Optimized to use single query instead of N queries in a loop
     *
     * @param  int  $days  Number of days to include
     */
    public function getRevenueChart(int $days = 30): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        // Single query with aggregation - 1 query instead of N queries
        $data = WalletTransaction::where('type', 'credit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount) as amount')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates with zeros
        $chart = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chart[] = [
                'date' => $date->format('M d'),
                'amount' => (float) ($data[$date->format('Y-m-d')]->amount ?? 0),
            ];
        }

        return $chart;
    }

    /**
     * Get job application chart data
     *
     * @param  int  $days  Number of days to include
     */
    public function getJobApplicationChart(int $days = 30): array
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        $data = JobApplication::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chart = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $chart[] = [
                'date' => $date->format('M d'),
                'count' => $data[$date->format('Y-m-d')]->count ?? 0,
            ];
        }

        return $chart;
    }

    /**
     * Cache analytics data for performance
     *
     * @param  string  $key  Cache key
     * @param  callable  $callback  Function to execute if cache miss
     * @param  int  $minutes  Cache duration in minutes
     * @return mixed
     */
    public function cached(string $key, callable $callback, int $minutes = 15)
    {
        return Cache::remember("analytics:{$key}", $minutes * 60, $callback);
    }

    /**
     * Clear all analytics caches
     * Useful when data is updated and fresh analytics are needed
     */
    public function clearCache(): void
    {
        Cache::forget('analytics:user_growth_30');
        Cache::forget('analytics:user_growth_7');
        Cache::forget('analytics:revenue_chart_30');
        Cache::forget('analytics:revenue_chart_7');
        Cache::forget('analytics:job_applications_30');
    }

    /**
     * Get weekly analytics summary
     * Optimized for dashboard quick view
     */
    public function getWeeklySummary(): array
    {
        $startOfWeek = now()->startOfWeek();

        return [
            'new_users' => User::where('created_at', '>=', $startOfWeek)->count(),
            'revenue' => WalletTransaction::where('type', 'credit')
                ->where('status', 'completed')
                ->where('created_at', '>=', $startOfWeek)
                ->sum('amount'),
            'job_applications' => JobApplication::where('created_at', '>=', $startOfWeek)->count(),
            'active_jobs' => JobPosting::where('is_active', true)
                ->where('application_deadline', '>=', now())
                ->count(),
        ];
    }

    /**
     * Get monthly comparison data
     * Current month vs last month
     */
    public function getMonthlyComparison(): array
    {
        $currentMonthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        // Current month
        $currentUsers = User::where('created_at', '>=', $currentMonthStart)->count();
        $currentRevenue = WalletTransaction::where('type', 'credit')
            ->where('status', 'completed')
            ->where('created_at', '>=', $currentMonthStart)
            ->sum('amount');

        // Last month
        $lastUsers = User::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastRevenue = WalletTransaction::where('type', 'credit')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        return [
            'current_month' => [
                'users' => $currentUsers,
                'revenue' => (float) $currentRevenue,
            ],
            'last_month' => [
                'users' => $lastUsers,
                'revenue' => (float) $lastRevenue,
            ],
            'growth' => [
                'users_percent' => $lastUsers > 0
                    ? round((($currentUsers - $lastUsers) / $lastUsers) * 100, 1)
                    : 100,
                'revenue_percent' => $lastRevenue > 0
                    ? round((($currentRevenue - $lastRevenue) / $lastRevenue) * 100, 1)
                    : 100,
            ],
        ];
    }
}
