<?php
$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($autoload)) { echo "vendor/autoload.php not found\n"; exit(1); }
require $autoload;
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$issues = App\Models\MaterialIssue::with(['issuedTo','item'])->get();
foreach ($issues as $i) {
    $itemName = $i->item ? $i->item->name : '-';
    $issued = $i->issuedTo ? $i->issuedTo->name : $i->issued_to_user_id;
    echo "{$i->id} | item:{$itemName} | qty:{$i->quantity} | issued_to:{$issued} | status:{$i->status}\n";
}
