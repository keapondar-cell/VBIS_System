<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Item;
use App\Models\InventoryTransaction;

$u = User::where('email', 'admin@example.com')->first();
if (! $u) {
    $u = User::create([
        'name' => 'Admin',
        'email' => 'admin@example.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
    ]);
    echo "admin created\n";
} else {
    $u->password = bcrypt('password');
    $u->role = 'admin';
    $u->save();
    echo "admin updated\n";
}

echo "admin: " . json_encode(['email' => $u->email, 'role' => $u->role]) . "\n";

// Create sample data if none exists
if (Item::count() < 5) {
    Item::factory()->count(5)->create();
    echo "items seeded\n";
} else {
    echo "items already present: " . Item::count() . "\n";
}

if (InventoryTransaction::count() < 5) {
    InventoryTransaction::factory()->count(5)->create();
    echo "transactions seeded\n";
} else {
    echo "transactions already present: " . InventoryTransaction::count() . "\n";
}

echo "done\n";
