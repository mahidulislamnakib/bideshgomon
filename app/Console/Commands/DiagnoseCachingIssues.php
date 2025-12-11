<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class DiagnoseCachingIssues extends Command
{
    protected $signature = 'system:diagnose-cache';

    protected $description = 'Diagnose caching and session issues across the platform';

    public function handle(): int
    {
        $this->info('🔍 Platform Caching Diagnostic Report');
        $this->info('Generated: '.now()->format('Y-m-d H:i:s'));
        $this->newLine();

        // 1. Cache Configuration
        $this->section('Cache Configuration');
        $this->table(
            ['Setting', 'Value', 'Status'],
            [
                ['Default Driver', config('cache.default'), $this->checkCacheDriver()],
                ['Database Table', config('cache.stores.database.table', 'cache'), $this->checkCacheTable()],
                ['File Path', config('cache.stores.file.path'), File::exists(config('cache.stores.file.path')) ? '✓ Exists' : '✗ Missing'],
            ]
        );

        // 2. Session Configuration
        $this->section('Session Configuration');
        $this->table(
            ['Setting', 'Value', 'Status'],
            [
                ['Driver', config('session.driver'), '✓ OK'],
                ['Lifetime', config('session.lifetime').' minutes', '✓ OK'],
                ['Expire on Close', config('session.expire_on_close') ? 'Yes' : 'No', '✓ OK'],
                ['Database Table', config('session.table', 'sessions'), $this->checkSessionTable()],
                ['Encrypt', config('session.encrypt') ? 'Yes' : 'No', '✓ OK'],
            ]
        );

        // 3. Vite/Asset Status
        $this->section('Asset Build Status');
        $manifestPath = public_path('build/manifest.json');
        if (File::exists($manifestPath)) {
            $manifest = json_decode(File::get($manifestPath), true);
            $this->info('✓ Vite manifest found');
            $this->line('  Build time: '.date('Y-m-d H:i:s', File::lastModified($manifestPath)));
            $this->line('  Entry points: '.count($manifest));
            $this->line('  Version hash: '.substr(md5(json_encode($manifest)), 0, 8));
        } else {
            $this->error('✗ Vite manifest NOT found - run: npm run build');
        }

        // 4. Cache Statistics
        $this->section('Cache Statistics');
        if (config('cache.default') === 'database') {
            try {
                $table = config('cache.stores.database.table', 'cache');
                $totalItems = DB::table($table)->count();
                $expiredItems = DB::table($table)->where('expiration', '<', time())->count();

                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Cached Items', $totalItems],
                        ['Expired Items', $expiredItems],
                        ['Active Items', $totalItems - $expiredItems],
                    ]
                );
            } catch (\Exception $e) {
                $this->error('Cannot read cache table: '.$e->getMessage());
            }
        }

        // 5. Session Statistics
        $this->section('Session Statistics');
        if (config('session.driver') === 'database') {
            try {
                $table = config('session.table', 'sessions');
                $totalSessions = DB::table($table)->count();
                $activeSessions = DB::table($table)
                    ->where('last_activity', '>', now()->subMinutes(config('session.lifetime'))->timestamp)
                    ->count();

                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Total Sessions', $totalSessions],
                        ['Active Sessions', $activeSessions],
                        ['Expired Sessions', $totalSessions - $activeSessions],
                    ]
                );
            } catch (\Exception $e) {
                $this->error('Cannot read session table: '.$e->getMessage());
            }
        }

        // 6. Compiled Files
        $this->section('Compiled Files Status');
        $compiledFiles = [
            'Routes' => base_path('bootstrap/cache/routes-v7.php'),
            'Config' => base_path('bootstrap/cache/config.php'),
            'Services' => base_path('bootstrap/cache/services.php'),
            'Packages' => base_path('bootstrap/cache/packages.php'),
            'Events' => base_path('bootstrap/cache/events.php'),
        ];

        $rows = [];
        foreach ($compiledFiles as $name => $path) {
            if (File::exists($path)) {
                $age = now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp(File::lastModified($path)));
                $rows[] = [$name, '✓ Cached', $age.' mins ago'];
            } else {
                $rows[] = [$name, '✗ Not cached', 'N/A'];
            }
        }
        $this->table(['File', 'Status', 'Last Modified'], $rows);

        // 7. Browser Caching Issues
        $this->section('Browser Caching Detection');
        $this->warn('⚠ Manual Check Required:');
        $this->line('  1. Open browser DevTools (F12)');
        $this->line('  2. Go to Network tab');
        $this->line('  3. Reload page (Ctrl+R)');
        $this->line('  4. Check Cache-Control headers');
        $this->line('  5. Inertia requests should have: no-cache, no-store');
        $this->line('  6. Static assets should have: public, max-age=31536000');

        // 8. Known Issues
        $this->section('Known Issues & Fixes');
        $issues = [];

        // Check for CDN Tailwind
        $appBlade = File::get(resource_path('views/app.blade.php'));
        if (str_contains($appBlade, 'cdn.tailwindcss.com')) {
            $issues[] = ['CDN Tailwind Detected', 'Remove from app.blade.php, use compiled CSS', '⚠ Warning'];
        }

        // Check for disabled CSS
        $appJs = File::get(resource_path('js/app.js'));
        if (str_contains($appJs, '// import \'../css/app.css\'')) {
            $issues[] = ['CSS Imports Disabled', 'Fix PostCSS error and re-enable imports', '🔴 Critical'];
        }

        // Check for window.location.reload
        $walletPage = File::get(resource_path('js/Pages/Wallet/Index.vue'));
        if (str_contains($walletPage, 'window.location.reload')) {
            $issues[] = ['Manual Reload Found', 'Replace with router.reload()', '⚠ Warning'];
        }

        if (count($issues) > 0) {
            $this->table(['Issue', 'Fix', 'Severity'], $issues);
        } else {
            $this->info('✓ No known issues detected');
        }

        // 9. Recommendations
        $this->section('Recommendations');
        $this->line('✅ Immediate Actions:');
        $this->line('  • Run: php artisan system:clear-all');
        $this->line('  • Run: npm run build');
        $this->line('  • Clear browser cache (Ctrl+Shift+Delete)');
        $this->line('  • Restart PHP-FPM if using it');
        $this->newLine();
        $this->line('🔧 Configuration Improvements:');
        $this->line('  • Consider Redis for cache in production');
        $this->line('  • Enable config/route caching in production');
        $this->line('  • Implement CDN for static assets');

        return Command::SUCCESS;
    }

    private function section(string $title): void
    {
        $this->newLine();
        $this->info("━━━ {$title} ━━━");
    }

    private function checkCacheDriver(): string
    {
        $driver = config('cache.default');

        return in_array($driver, ['redis', 'memcached', 'database']) ? '✓ OK' : '⚠ Consider Redis';
    }

    private function checkCacheTable(): string
    {
        if (config('cache.default') !== 'database') {
            return 'N/A';
        }

        try {
            $table = config('cache.stores.database.table', 'cache');
            DB::table($table)->limit(1)->get();

            return '✓ Exists';
        } catch (\Exception $e) {
            return '✗ Missing';
        }
    }

    private function checkSessionTable(): string
    {
        if (config('session.driver') !== 'database') {
            return 'N/A';
        }

        try {
            $table = config('session.table', 'sessions');
            DB::table($table)->limit(1)->get();

            return '✓ Exists';
        } catch (\Exception $e) {
            return '✗ Missing';
        }
    }
}
