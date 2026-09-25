<?php
require dirname(__DIR__) . '/vendor/autoload.php';
$app = require dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MaterialIssue;

$issues = MaterialIssue::with(['issuedTo','item'])->latest()->limit(50)->get();
echo json_encode($issues->toArray(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
