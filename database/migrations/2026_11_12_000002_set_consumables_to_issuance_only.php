<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('items')
            ->whereRaw('LOWER(TRIM(category)) = ?', ['consumable'])
            ->update(['usage_type' => 'issuance_only']);
    }

    public function down(): void
    {
        DB::table('items')
            ->whereRaw('LOWER(TRIM(category)) = ?', ['consumable'])
            ->where('usage_type', 'issuance_only')
            ->update(['usage_type' => 'both']);
    }
};
