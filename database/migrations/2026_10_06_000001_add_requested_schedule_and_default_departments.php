<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('material_issues', function (Blueprint $table) {
            $table->dateTime('requested_for_at')->nullable()->after('department_id');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dateTime('requested_for_at')->nullable()->after('department_id');
        });

        $now = now();
        foreach (['Computer Lab', 'Science Lab', 'Library', 'Teachers'] as $name) {
            DB::table('departments')->insertOrIgnore([
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('material_issues', function (Blueprint $table) {
            $table->dropColumn('requested_for_at');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('requested_for_at');
        });
    }
};
