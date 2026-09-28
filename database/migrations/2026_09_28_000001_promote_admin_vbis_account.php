<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@vbis.test.com')
            ->where('role', 'user')
            ->update(['role' => 'admin']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@vbis.test.com')
            ->where('role', 'admin')
            ->update(['role' => 'user']);
    }
};