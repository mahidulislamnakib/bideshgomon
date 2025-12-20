<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Wallet;

echo "🔧 Creating Missing Wallets\n";
echo "===========================\n\n";

$usersWithoutWallets = User::doesntHave('wallet')->get();

if ($usersWithoutWallets->isEmpty()) {
    echo "✅ All users already have wallets!\n";
} else {
    echo "Found {$usersWithoutWallets->count()} users without wallets\n\n";

    foreach ($usersWithoutWallets as $user) {
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0.00,
            'currency' => 'BDT',
            'status' => 'active',
        ]);

        echo "✅ Created wallet for user #{$user->id} ({$user->name})\n";
    }

    echo "\n🎉 Successfully created {$usersWithoutWallets->count()} wallets!\n";
}
