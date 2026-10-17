<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $departmentNames = [
        'Library',
        'Computer Lab',
        'Science Lab',
        'JHS Department',
        'SHS Department',
    ];

    public function up(): void
    {
        $now = now();

        foreach ($this->departmentNames as $name) {
            DB::table('departments')->updateOrInsert(
                ['name' => $name],
                ['is_active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        DB::table('departments')
            ->whereNotIn('name', $this->departmentNames)
            ->update(['is_active' => false, 'updated_at' => $now]);
    }

    public function down(): void
    {
        $legacyDepartmentNames = ['Computer Lab', 'Library', 'Science Lab', 'Teachers'];

        DB::table('departments')
            ->whereIn('name', $legacyDepartmentNames)
            ->update(['is_active' => true, 'updated_at' => now()]);

        DB::table('departments')
            ->whereIn('name', ['JHS Department', 'SHS Department'])
            ->update(['is_active' => false, 'updated_at' => now()]);
    }
};
