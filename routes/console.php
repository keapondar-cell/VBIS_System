<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use App\Models\Item;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\InventoryReportGeneratedNotification;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('reports:generate', function () {
    $items = Item::orderBy('name')->get();
    $pdf = Pdf::loadView('inventory.pdf_items', ['items' => $items]);
    $path = 'reports/inventory-'.now()->format('Ymd_His').'.pdf';
    Storage::disk('local')->put($path, $pdf->output());
    User::where('role', 'admin')->get()->each->notify(new InventoryReportGeneratedNotification($path));
    $this->info('Generated '.$path);
})->purpose('Generate the daily inventory monitoring report');
