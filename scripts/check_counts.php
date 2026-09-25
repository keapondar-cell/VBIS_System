<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Item;
use App\Models\InventoryTransaction;

echo 'users: ' . User::count() . PHP_EOL;
echo 'items: ' . Item::count() . PHP_EOL;
echo 'transactions: ' . InventoryTransaction::count() . PHP_EOL;
