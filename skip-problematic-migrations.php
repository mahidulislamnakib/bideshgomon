<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔧 Skipping Problematic Migrations\n";
echo "===================================\n\n";

$problematicMigrations = [
    '2025_11_30_103354_fix_user_languages_table_structure',
    '2025_11_30_114018_fix_countries_table_iso_codes',
];

$ranMigrations = DB::table('migrations')->pluck('migration')->toArray();

foreach ($problematicMigrations as $migration) {
    if (! in_array($migration, $ranMigrations)) {
        DB::table('migrations')->insert([
            'migration' => $migration,
            'batch' => 99,
        ]);
        echo "✅ Marked as run: $migration\n";
    } else {
        echo "⏭️  Already marked: $migration\n";
    }
}

echo "\n✅ Done! Now run: php artisan migrate\n";
