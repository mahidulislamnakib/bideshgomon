<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "Checking activity_log table...\n";
$exists = Schema::hasTable('activity_log');
echo 'hasTable: '.($exists ? 'yes' : 'no')."\n";

if ($exists) {
    echo "Attempting insert into activity_log...\n";
    try {
        DB::table('activity_log')->insert([
            'log_name' => 'default',
            'description' => 'Smoke test insert',
            'properties' => json_encode(['test' => 'smoke']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        echo "Insert successful. Latest 3 rows:\n";
        $rows = DB::table('activity_log')->orderBy('id', 'desc')->limit(3)->get();
        foreach ($rows as $r) {
            echo json_encode((array) $r)."\n";
        }
    } catch (Exception $e) {
        echo 'Insert failed: '.$e->getMessage()."\n";
    }
} else {
    echo "activity_log table missing. Run migrations.\n";
}
