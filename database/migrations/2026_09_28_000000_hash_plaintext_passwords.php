<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all users with plaintext passwords (not bcrypt hashes)
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            // Check if password is not already bcrypted
            // Bcrypt hashes start with $2a$, $2b$, or $2y$
            if (!preg_match('/^\$2[aby]\$/', $user->password)) {
                // Hash the plaintext password
                DB::table('users')
                    ->where('id', $user->id)
                    ->update([
                        'password' => Hash::make($user->password),
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is irreversible as we cannot recover plaintext passwords
        // Intentionally left empty
    }
};

