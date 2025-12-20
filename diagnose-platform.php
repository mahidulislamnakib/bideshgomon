<?php

/**
 * Platform Diagnostic Script
 * Checks for common issues and errors
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔍 BideshGomon Platform Diagnostic\n";
echo "===================================\n\n";

// 1. Database Connection
echo "1️⃣  Database Connection...\n";
try {
    DB::connection()->getPdo();
    echo '   ✅ Database connected ('.config('database.default').")\n\n";
} catch (Exception $e) {
    echo '   ❌ Database connection failed: '.$e->getMessage()."\n\n";
}

// 2. Check for duplicate tables
echo "2️⃣  Checking for Duplicate Tables...\n";
$pendingMigrations = [
    'service_categories',
    'appointments',
    'faqs',
    'directories',
    'directory_categories',
    'agency_country_assignments',
    'visa_requirements',
    'job_postings',
    'profession_visa_requirements',
    'visa_requirement_documents',
    'admin_impersonation_logs',
    'marketing_campaigns',
    'visa_applications',
    'job_applications',
    'flight_requests',
    'flight_bookings',
    'support_tickets',
    'events',
];

$existingTables = [];
$missingTables = [];

foreach ($pendingMigrations as $table) {
    if (Schema::hasTable($table)) {
        $existingTables[] = $table;
    } else {
        $missingTables[] = $table;
    }
}

if (! empty($existingTables)) {
    echo "   ⚠️  Tables that already exist (will cause migration conflicts):\n";
    foreach ($existingTables as $table) {
        echo "      - $table\n";
    }
} else {
    echo "   ✅ No duplicate table conflicts\n";
}
echo "\n";

// 3. Check critical tables
echo "3️⃣  Checking Critical Tables...\n";
$criticalTables = [
    'users',
    'service_modules',
    'service_categories',
    'service_form_fields',
    'wallets',
    'wallet_transactions',
    'referrals',
    'rewards',
    'agencies',
    'countries',
];

$missingCritical = [];
foreach ($criticalTables as $table) {
    if (! Schema::hasTable($table)) {
        $missingCritical[] = $table;
    }
}

if (! empty($missingCritical)) {
    echo "   ❌ Missing critical tables:\n";
    foreach ($missingCritical as $table) {
        echo "      - $table\n";
    }
} else {
    echo "   ✅ All critical tables exist\n";
}
echo "\n";

// 4. Check active services
echo "4️⃣  Checking Active Services...\n";
try {
    $totalServices = App\Models\ServiceModule::count();
    $activeServices = App\Models\ServiceModule::where('is_active', true)
        ->where('service_type', 'revenue_service')
        ->count();

    echo "   📊 Total Services: $totalServices\n";
    echo "   📊 Active Services: $activeServices\n";

    if ($activeServices == 0) {
        echo "   ⚠️  No active services - users won't see any services\n";
    } else {
        echo "   ✅ Services are active\n";
    }
} catch (Exception $e) {
    echo '   ❌ Error checking services: '.$e->getMessage()."\n";
}
echo "\n";

// 5. Check pending migrations
echo "5️⃣  Checking Pending Migrations...\n";
try {
    $migrator = app('migrator');
    $ran = $migrator->getRepository()->getRan();
    $migrations = $migrator->getMigrationFiles($migrator->paths());
    $pending = array_diff(array_keys($migrations), $ran);

    echo '   📊 Pending Migrations: '.count($pending)."\n";

    if (count($pending) > 0) {
        echo '   ⚠️  You have '.count($pending)." pending migrations\n";
        echo "   💡 Run: php artisan migrate (after resolving conflicts)\n";
    } else {
        echo "   ✅ All migrations up to date\n";
    }
} catch (Exception $e) {
    echo '   ❌ Error checking migrations: '.$e->getMessage()."\n";
}
echo "\n";

// 6. Check routes
echo "6️⃣  Checking Routes...\n";
try {
    $routes = Route::getRoutes();
    $totalRoutes = count($routes);
    echo "   📊 Total Routes: $totalRoutes\n";

    if ($totalRoutes > 0) {
        echo "   ✅ Routes loaded successfully\n";
    } else {
        echo "   ❌ No routes loaded\n";
    }
} catch (Exception $e) {
    echo '   ❌ Error loading routes: '.$e->getMessage()."\n";
}
echo "\n";

// 7. Check storage
echo "7️⃣  Checking Storage...\n";
if (file_exists(public_path('storage'))) {
    echo "   ✅ Storage link exists\n";
} else {
    echo "   ⚠️  Storage link missing\n";
    echo "   💡 Run: php artisan storage:link\n";
}
echo "\n";

// 8. Check environment
echo "8️⃣  Environment Configuration...\n";
echo '   APP_ENV: '.config('app.env')."\n";
echo '   APP_DEBUG: '.(config('app.debug') ? 'true' : 'false')."\n";
echo '   APP_URL: '.config('app.url')."\n";

if (config('app.env') === 'production' && config('app.debug')) {
    echo "   ⚠️  Debug mode enabled in production!\n";
}
echo "\n";

// 9. Recent errors
echo "9️⃣  Checking Recent Errors...\n";
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $errorCount = 0;
    $recentErrors = [];

    foreach (array_reverse($lines) as $line) {
        if (stripos($line, 'ERROR') !== false || stripos($line, 'SQLSTATE') !== false) {
            $errorCount++;
            if (count($recentErrors) < 5) {
                $recentErrors[] = substr($line, 0, 150);
            }
        }
    }

    echo "   📊 Total error lines: $errorCount\n";

    if (! empty($recentErrors)) {
        echo "   ⚠️  Recent errors:\n";
        foreach ($recentErrors as $error) {
            echo '      '.trim($error)."\n";
        }
    }
} else {
    echo "   ℹ️  No log file found\n";
}
echo "\n";

// 10. Summary
echo "🎯 DIAGNOSIS SUMMARY\n";
echo "===================\n";

$issues = [];

if (! empty($missingCritical)) {
    $issues[] = 'Missing critical tables: '.implode(', ', $missingCritical);
}

if (! empty($existingTables)) {
    $issues[] = count($existingTables).' tables exist but migrations are pending (will cause conflicts)';
}

if (isset($activeServices) && $activeServices == 0) {
    $issues[] = 'No active services available';
}

if (! file_exists(public_path('storage'))) {
    $issues[] = 'Storage link missing';
}

if (empty($issues)) {
    echo "✅ No critical issues found!\n";
    echo "Platform appears to be functioning correctly.\n\n";

    if (count($pending ?? []) > 0) {
        echo "ℹ️  Note: You have pending migrations, but this may be intentional.\n";
        echo "   Review the pending migrations list before running them.\n";
    }
} else {
    echo '❌ Found '.count($issues)." issue(s):\n\n";
    foreach ($issues as $i => $issue) {
        echo ($i + 1).". $issue\n";
    }
    echo "\n";
    echo "💡 RECOMMENDED ACTIONS:\n";
    echo "   1. Fix table conflicts (see section 2 above)\n";
    echo "   2. Run pending migrations carefully\n";
    echo "   3. Activate services if needed\n";
    echo "   4. Check storage link\n";
}

echo "\n";
echo "📝 For detailed analysis, check:\n";
echo "   - ERROR_CHECKLIST_360.md (comprehensive checklist)\n";
echo "   - storage/logs/laravel.log (error logs)\n";
echo "\n";
