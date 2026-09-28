<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class HashPlaintextPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hash-plaintext-passwords {--dry-run : Preview changes without applying them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hash all plaintext passwords in the users table using Bcrypt';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running in DRY-RUN mode. No changes will be applied.');
        }

        // Get all users with plaintext passwords (not bcrypt hashes)
        $users = DB::table('users')->get();

        $plaintextCount = 0;
        $hashedCount = 0;

        foreach ($users as $user) {
            // Check if password is not already bcrypted
            // Bcrypt hashes start with $2a$, $2b$, or $2y$
            if (!preg_match('/^\$2[aby]\$/', $user->password)) {
                $plaintextCount++;

                if (!$dryRun) {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'password' => Hash::make($user->password),
                        ]);
                    $hashedCount++;
                }

                $this->line("User ID {$user->id} ({$user->email}): Password needs hashing");
            }
        }

        $this->newLine();

        if ($plaintextCount === 0) {
            $this->info('✓ All passwords are already properly hashed!');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("DRY-RUN: Found {$plaintextCount} plaintext password(s) that would be hashed.");
            $this->info('Run without --dry-run to apply the changes.');
        } else {
            $this->info("✓ Successfully hashed {$hashedCount} plaintext password(s)!");
        }

        return self::SUCCESS;
    }
}

