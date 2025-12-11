<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ClearAllCaches extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'system:clear-all
                            {--force : Force cache clearing without confirmation}';

    /**
     * The console command description.
     */
    protected $description = 'Clear ALL caches (application, route, config, view, compiled, sessions)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will clear ALL caches and may log out users. Continue?')) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info('🧹 Starting comprehensive cache clearing...');
        $this->newLine();

        // 1. Application Cache
        $this->info('Clearing application cache...');
        Artisan::call('cache:clear');
        $this->line('  ✓ Done');

        // 2. Route Cache
        $this->info('Clearing route cache...');
        Artisan::call('route:clear');
        $this->line('  ✓ Done');

        // 3. Config Cache
        $this->info('Clearing config cache...');
        Artisan::call('config:clear');
        $this->line('  ✓ Done');

        // 4. View Cache
        $this->info('Clearing compiled views...');
        Artisan::call('view:clear');
        $this->line('  ✓ Done');

        // 5. Event Cache
        $this->info('Clearing cached events...');
        Artisan::call('event:clear');
        $this->line('  ✓ Done');

        // 6. Optimize Clear
        $this->info('Running optimize:clear...');
        Artisan::call('optimize:clear');
        $this->line('  ✓ Done');

        // 7. Database Cache (if using database driver)
        if (config('cache.default') === 'database') {
            $this->info('Clearing database cache table...');
            try {
                DB::table(config('cache.stores.database.table', 'cache'))->truncate();
                $this->line('  ✓ Done');
            } catch (\Exception $e) {
                $this->warn('  ✗ Failed: '.$e->getMessage());
            }
        }

        // 8. Session Cleanup (careful - logs out users)
        if ($this->confirm('Clear all sessions? (This will log out ALL users)', false)) {
            $this->info('Clearing all sessions...');
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))->truncate();
            } elseif (config('session.driver') === 'file') {
                File::cleanDirectory(config('session.files'));
            }
            $this->line('  ✓ Done');
        }

        // 9. Clear compiled class files
        $this->info('Clearing compiled classes...');
        $compiledPath = base_path('bootstrap/cache/compiled.php');
        if (File::exists($compiledPath)) {
            File::delete($compiledPath);
        }
        $this->line('  ✓ Done');

        // 10. Clear services cache
        $this->info('Clearing cached services...');
        $servicesPath = base_path('bootstrap/cache/services.php');
        if (File::exists($servicesPath)) {
            File::delete($servicesPath);
        }
        $this->line('  ✓ Done');

        // 11. Vite manifest check
        $this->info('Checking Vite manifest...');
        $manifestPath = public_path('build/manifest.json');
        if (File::exists($manifestPath)) {
            $this->line('  ✓ Vite manifest found: '.date('Y-m-d H:i:s', File::lastModified($manifestPath)));
        } else {
            $this->warn('  ⚠ Vite manifest missing - run npm run build');
        }

        $this->newLine();
        $this->info('✅ All caches cleared successfully!');
        $this->newLine();

        // Post-clear recommendations
        $this->info('📋 Recommended next steps:');
        $this->line('  1. Run: npm run build (if in production)');
        $this->line('  2. Restart web server if using PHP-FPM');
        $this->line('  3. Clear browser cache (Ctrl+Shift+Delete)');
        $this->line('  4. Test critical features');

        return Command::SUCCESS;
    }
}
