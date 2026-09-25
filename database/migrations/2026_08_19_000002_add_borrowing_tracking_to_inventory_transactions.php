<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->foreignId('related_transaction_id')->nullable()->after('transaction_type')->constrained('inventory_transactions')->nullOnDelete();
            $table->timestamp('expected_return_at')->nullable()->after('notes');
            $table->timestamp('returned_at')->nullable()->after('expected_return_at');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['related_transaction_id']);
            $table->dropColumn(['related_transaction_id', 'expected_return_at', 'returned_at']);
        });
    }
};
