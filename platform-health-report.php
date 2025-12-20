<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n";
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                                                           ║\n";
echo "║        🔍 BIDESHGOMON PLATFORM HEALTH REPORT             ║\n";
echo "║                                                           ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo "\n";

$issues = [];
$warnings = [];
$passed = [];

// 1. Database Connection
echo "1️⃣  DATABASE CONNECTION\n";
echo '   '.str_repeat('─', 50)."\n";
try {
    DB::connection()->getPdo();
    $driver = config('database.default');
    echo "   ✅ Connected to $driver database\n";
    $passed[] = 'Database connection';
} catch (Exception $e) {
    echo '   ❌ CRITICAL: '.$e->getMessage()."\n";
    $issues[] = 'Database connection failed';
}
echo "\n";

// 2. Critical Tables
echo "2️⃣  CRITICAL TABLES\n";
echo '   '.str_repeat('─', 50)."\n";
$criticalTables = [
    'users' => 'User accounts',
    'roles' => 'User roles',
    'service_modules' => 'Service definitions',
    'service_categories' => 'Service categories',
    'service_form_fields' => 'Dynamic forms',
    'wallets' => 'User wallets',
    'wallet_transactions' => 'Transaction history',
    'referrals' => 'Referral system',
    'agencies' => 'Agency management',
    'countries' => 'Country data',
];

$missingTables = [];
foreach ($criticalTables as $table => $description) {
    if (Schema::hasTable($table)) {
        $count = DB::table($table)->count();
        echo "   ✅ $table: $count records\n";
    } else {
        echo "   ❌ MISSING: $table ($description)\n";
        $missingTables[] = $table;
        $issues[] = "Missing table: $table";
    }
}

if (empty($missingTables)) {
    $passed[] = 'All critical tables exist';
}
echo "\n";

// 3. Services Status
echo "3️⃣  SERVICES STATUS\n";
echo '   '.str_repeat('─', 50)."\n";
try {
    $totalServices = DB::table('service_modules')->count();
    $activeServices = DB::table('service_modules')
        ->where('is_active', true)
        ->where('service_type', 'revenue_service')
        ->count();

    echo "   📊 Total Services: $totalServices\n";
    echo "   📊 Active Revenue Services: $activeServices\n";

    if ($activeServices == 0) {
        echo "   ❌ CRITICAL: No active services!\n";
        $issues[] = 'No active services available for users';
    } elseif ($activeServices < 5) {
        echo "   ⚠️  WARNING: Only $activeServices active services\n";
        $warnings[] = 'Limited services available';
    } else {
        echo "   ✅ Services are properly configured\n";
        $passed[] = 'Service availability';
    }

    // Check service categories
    $categories = DB::table('service_categories')->where('is_active', true)->count();
    echo "   📊 Active Categories: $categories\n";

} catch (Exception $e) {
    echo '   ❌ ERROR: '.$e->getMessage()."\n";
    $issues[] = 'Services check failed';
}
echo "\n";

// 4. Migrations Status
echo "4️⃣  MIGRATIONS STATUS\n";
echo '   '.str_repeat('─', 50)."\n";
try {
    $migrator = app('migrator');
    $ran = $migrator->getRepository()->getRan();
    $migrations = $migrator->getMigrationFiles($migrator->paths());
    $pending = array_diff(array_keys($migrations), $ran);

    echo '   📊 Total Migrations: '.count($migrations)."\n";
    echo '   📊 Completed: '.count($ran)."\n";
    echo '   📊 Pending: '.count($pending)."\n";

    if (count($pending) == 0) {
        echo "   ✅ All migrations up to date\n";
        $passed[] = 'Database migrations';
    } else {
        echo '   ⚠️  '.count($pending)." pending migrations\n";
        $warnings[] = count($pending).' pending migrations';
    }
} catch (Exception $e) {
    echo '   ❌ ERROR: '.$e->getMessage()."\n";
}
echo "\n";

// 5. Routes Status
echo "5️⃣  ROUTES STATUS\n";
echo '   '.str_repeat('─', 50)."\n";
try {
    $routes = Route::getRoutes();
    $totalRoutes = count($routes);

    echo "   📊 Total Routes: $totalRoutes\n";

    if ($totalRoutes > 1000) {
        echo "   ✅ Routes loaded successfully\n";
        $passed[] = 'Route configuration';
    } elseif ($totalRoutes > 0) {
        echo "   ⚠️  Only $totalRoutes routes (expected 1000+)\n";
        $warnings[] = 'Lower than expected route count';
    } else {
        echo "   ❌ CRITICAL: No routes loaded!\n";
        $issues[] = 'Routes not loading';
    }
} catch (Exception $e) {
    echo '   ❌ ERROR: '.$e->getMessage()."\n";
    $issues[] = 'Route loading failed';
}
echo "\n";

// 6. Storage Status
echo "6️⃣  STORAGE STATUS\n";
echo '   '.str_repeat('─', 50)."\n";

if (file_exists(public_path('storage'))) {
    echo "   ✅ Storage link exists\n";
    $passed[] = 'Storage link';
} else {
    echo "   ⚠️  Storage link missing\n";
    echo "   💡 Run: php artisan storage:link\n";
    $warnings[] = 'Storage link not created';
}

// Check log file size
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $logSize = filesize($logFile) / (1024 * 1024); // MB
    echo '   📊 Log File Size: '.round($logSize, 2)." MB\n";

    if ($logSize > 100) {
        echo "   ⚠️  Log file is very large (>100MB)\n";
        echo "   💡 Consider rotating logs\n";
        $warnings[] = 'Large log file';
    } else {
        echo "   ✅ Log file size acceptable\n";
    }
}
echo "\n";

// 7. Environment Configuration
echo "7️⃣  ENVIRONMENT\n";
echo '   '.str_repeat('─', 50)."\n";
echo '   APP_ENV: '.config('app.env')."\n";
echo '   APP_DEBUG: '.(config('app.debug') ? 'true' : 'false')."\n";
echo '   APP_URL: '.config('app.url')."\n";

if (config('app.env') === 'production' && config('app.debug')) {
    echo "   ❌ CRITICAL: Debug mode enabled in production!\n";
    $issues[] = 'Debug mode in production';
} else {
    echo "   ✅ Environment properly configured\n";
    $passed[] = 'Environment configuration';
}
echo "\n";

// 8. User & Data Status
echo "8️⃣  DATA STATUS\n";
echo '   '.str_repeat('─', 50)."\n";
try {
    $users = DB::table('users')->count();
    $wallets = DB::table('wallets')->count();
    $applications = DB::table('service_applications')->count();

    echo "   📊 Users: $users\n";
    echo "   📊 Wallets: $wallets\n";
    echo "   📊 Applications: $applications\n";

    // Check wallet integrity
    $usersWithoutWallets = DB::table('users')
        ->leftJoin('wallets', 'users.id', '=', 'wallets.user_id')
        ->whereNull('wallets.id')
        ->count();

    if ($usersWithoutWallets > 0) {
        echo "   ⚠️  $usersWithoutWallets users without wallets\n";
        $warnings[] = 'Users missing wallets';
    } else {
        echo "   ✅ All users have wallets\n";
        $passed[] = 'Wallet integrity';
    }
} catch (Exception $e) {
    echo '   ❌ ERROR: '.$e->getMessage()."\n";
}
echo "\n";

// FINAL SUMMARY
echo "╔═══════════════════════════════════════════════════════════╗\n";
echo "║                    SUMMARY REPORT                         ║\n";
echo "╚═══════════════════════════════════════════════════════════╝\n";
echo "\n";

echo '✅ PASSED CHECKS: '.count($passed)."\n";
foreach ($passed as $item) {
    echo "   • $item\n";
}
echo "\n";

if (! empty($warnings)) {
    echo '⚠️  WARNINGS: '.count($warnings)."\n";
    foreach ($warnings as $item) {
        echo "   • $item\n";
    }
    echo "\n";
}

if (! empty($issues)) {
    echo '❌ CRITICAL ISSUES: '.count($issues)."\n";
    foreach ($issues as $item) {
        echo "   • $item\n";
    }
    echo "\n";
} else {
    if (empty($warnings)) {
        echo "🎉 PLATFORM STATUS: EXCELLENT\n";
        echo "   All systems are functioning correctly!\n";
    } else {
        echo "✅ PLATFORM STATUS: GOOD\n";
        echo "   Platform is functional with minor warnings.\n";
    }
}

echo "\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo 'Report generated: '.date('Y-m-d H:i:s')."\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "\n";
