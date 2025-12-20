<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Auto-Fixing All Migration Conflicts\n";
echo "=======================================\n\n";

// Get all pending migrations
$migrator = app('migrator');
$ran = $migrator->getRepository()->getRan();
$migrations = $migrator->getMigrationFiles($migrator->paths());
$pending = array_diff(array_keys($migrations), $ran);

if (empty($pending)) {
    echo "✅ No pending migrations!\n";
    exit(0);
}

echo '📋 Found '.count($pending)." pending migrations\n";
echo "⚙️  Checking which tables already exist...\n\n";

$marked = 0;
$errors = 0;

foreach ($pending as $migration) {
    // Extract table name from migration name
    if (preg_match('/create_(\w+)_table/', $migration, $matches)) {
        $tableName = $matches[1];

        if (Schema::hasTable($tableName)) {
            // Table exists, mark migration as run
            DB::table('migrations')->insert([
                'migration' => $migration,
                'batch' => 99,
            ]);
            echo "✅ Marked: $migration (table '$tableName' exists)\n";
            $marked++;
        }
    } elseif (preg_match('/add_|fix_|alter_|update_/', $migration)) {
        // These are modification migrations, need manual review
        echo "⚠️  Needs review: $migration\n";
    }
}

echo "\n";
echo "📊 Summary:\n";
echo "   ✅ Auto-marked: $marked\n";
echo '   ⚠️  Need review: '.(count($pending) - $marked)."\n";
echo "\n";

// Check remaining
$ran = $migrator->getRepository()->getRan();
$migrations = $migrator->getMigrationFiles($migrator->paths());
$remaining = array_diff(array_keys($migrations), $ran);

if (count($remaining) > 0) {
    echo '📋 Remaining Pending: '.count($remaining)."\n";
    foreach (array_slice($remaining, 0, 5) as $mig) {
        echo "   - $mig\n";
    }
    echo "\n💡 Run: php artisan migrate (to apply safe migrations)\n";
} else {
    echo "✅ All migrations resolved!\n";
}

echo "\n";
