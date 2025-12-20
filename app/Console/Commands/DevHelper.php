<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DevHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:help {action? : Action to perform}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Development helper with common tasks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        if (! $action) {
            $this->showMenu();

            return;
        }

        match ($action) {
            'fresh' => $this->freshStart(),
            'clear' => $this->clearCaches(),
            'routes' => $this->showRoutes(),
            'test' => $this->runTests(),
            'optimize' => $this->optimize(),
            'analyze' => $this->analyze(),
            default => $this->error("Unknown action: {$action}"),
        };
    }

    protected function showMenu()
    {
        $this->info('🚀 BideshGomon Development Helper');
        $this->newLine();
        $this->line('Available actions:');
        $this->newLine();
        $this->table(
            ['Command', 'Description'],
            [
                ['php artisan dev:help fresh', 'Fresh database with seeders'],
                ['php artisan dev:help clear', 'Clear all caches'],
                ['php artisan dev:help routes', 'Show all routes with middleware'],
                ['php artisan dev:help test', 'Run test suite'],
                ['php artisan dev:help optimize', 'Optimize for production'],
                ['php artisan dev:help analyze', 'Analyze codebase health'],
            ]
        );
    }

    protected function freshStart()
    {
        $this->warn('⚠️  This will wipe your database!');
        if (! $this->confirm('Continue?', false)) {
            $this->info('Cancelled.');

            return;
        }

        $this->info('🔄 Resetting database...');
        Artisan::call('migrate:fresh --seed');
        $this->info(Artisan::output());

        $this->info('🔑 Generating Ziggy routes...');
        Artisan::call('ziggy:generate');

        $this->info('✅ Fresh start complete!');
        $this->newLine();
        $this->line('Default users created:');
        $this->table(
            ['Email', 'Password', 'Role'],
            [
                ['admin@bideshgomon.com', 'password', 'admin'],
                ['user@bideshgomon.com', 'password', 'user'],
                ['agency@bideshgomon.com', 'password', 'agency'],
            ]
        );
    }

    protected function clearCaches()
    {
        $this->info('🧹 Clearing caches...');

        Artisan::call('config:clear');
        $this->line('✓ Config cache cleared');

        Artisan::call('route:clear');
        $this->line('✓ Route cache cleared');

        Artisan::call('view:clear');
        $this->line('✓ View cache cleared');

        Artisan::call('cache:clear');
        $this->line('✓ Application cache cleared');

        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->line('✓ OPCache cleared');
        }

        $this->info('✅ All caches cleared!');
    }

    protected function showRoutes()
    {
        $this->info('🛣️  Generating route list...');
        $this->newLine();

        $filter = $this->choice(
            'Filter routes by:',
            ['All', 'Admin', 'User', 'Agency', 'API', 'Auth'],
            0
        );

        $command = 'route:list';
        if ($filter !== 'All') {
            $command .= ' | Select-String -Pattern "'.strtolower($filter).'"';
        }

        Artisan::call('route:list', ['--columns' => 'method,uri,name,middleware']);
        $this->line(Artisan::output());
    }

    protected function runTests()
    {
        $this->info('🧪 Running test suite...');
        $this->newLine();

        $type = $this->choice(
            'Test type:',
            ['All', 'Feature', 'Unit', 'Specific'],
            0
        );

        match ($type) {
            'All' => Artisan::call('test'),
            'Feature' => Artisan::call('test', ['--testsuite' => 'Feature']),
            'Unit' => Artisan::call('test', ['--testsuite' => 'Unit']),
            'Specific' => $this->runSpecificTest(),
        };

        $this->line(Artisan::output());
    }

    protected function runSpecificTest()
    {
        $testFiles = collect(File::allFiles(base_path('tests')))
            ->filter(fn ($file) => $file->getExtension() === 'php')
            ->map(fn ($file) => str_replace([base_path('tests').'\\', '.php'], '', $file->getPathname()))
            ->values()
            ->toArray();

        $selected = $this->choice('Select test:', $testFiles);
        Artisan::call('test', ['--filter' => basename($selected, '.php')]);
    }

    protected function optimize()
    {
        $this->info('⚡ Optimizing application...');

        Artisan::call('config:cache');
        $this->line('✓ Config cached');

        Artisan::call('route:cache');
        $this->line('✓ Routes cached');

        Artisan::call('view:cache');
        $this->line('✓ Views cached');

        Artisan::call('optimize');
        $this->line('✓ Application optimized');

        $this->info('✅ Optimization complete!');
        $this->warn('⚠️  Remember to clear caches in development: php artisan dev:help clear');
    }

    protected function analyze()
    {
        $this->info('📊 Analyzing codebase...');
        $this->newLine();

        // Database stats
        try {
            $driver = config('database.default');
            if ($driver === 'sqlite') {
                $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            } else {
                $tables = \DB::select('SHOW TABLES');
            }
            $this->line('📦 Database: '.count($tables).' tables');
        } catch (\Exception $e) {
            $this->line('📦 Database: Unable to count tables');
        }

        // Route stats
        $routes = \Route::getRoutes();
        $this->line("🛣️  Routes: {$routes->count()} registered");

        // Model stats
        $models = File::glob(app_path('Models/*.php'));
        $this->line('🗂️  Models: '.count($models));

        // Controller stats
        $controllers = File::allFiles(app_path('Http/Controllers'));
        $this->line('🎮 Controllers: '.count($controllers));

        // Migration stats
        $migrations = File::files(database_path('migrations'));
        $this->line('🔄 Migrations: '.count($migrations));

        // Seeder stats
        $seeders = File::files(database_path('seeders'));
        $this->line('🌱 Seeders: '.count($seeders));

        // Test stats
        $tests = File::allFiles(base_path('tests'));
        $this->line('🧪 Tests: '.count($tests));

        $this->newLine();

        // Check for common issues
        $this->info('🔍 Checking for issues...');

        $envExample = File::exists(base_path('.env.example'));
        $this->line($envExample ? '✓ .env.example exists' : '✗ .env.example missing');

        $storageLinked = File::exists(public_path('storage'));
        $this->line($storageLinked ? '✓ Storage linked' : '✗ Storage not linked (run: php artisan storage:link)');

        $ziggy = File::exists(resource_path('js/ziggy.js'));
        $this->line($ziggy ? '✓ Ziggy routes generated' : '✗ Ziggy routes missing (run: php artisan ziggy:generate)');

        $nodeModules = File::exists(base_path('node_modules'));
        $this->line($nodeModules ? '✓ Node modules installed' : '✗ Node modules missing (run: npm install)');

        $vendor = File::exists(base_path('vendor'));
        $this->line($vendor ? '✓ Composer dependencies installed' : '✗ Vendor missing (run: composer install)');

        $this->newLine();
        $this->info('✅ Analysis complete!');
    }
}
