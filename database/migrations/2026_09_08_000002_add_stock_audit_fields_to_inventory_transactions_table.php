<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->integer('quantity_change')->nullable()->after('quantity');
            $table->integer('old_quantity')->nullable()->after('quantity_change');
            $table->integer('new_quantity')->nullable()->after('old_quantity');
            $table->unsignedBigInteger('performed_by')->nullable()->after('new_quantity');
            $table->unsignedBigInteger('affected_user_id')->nullable()->after('performed_by');
            $table->string('reason')->nullable()->after('affected_user_id');
            $table->text('remarks')->nullable()->after('reason');
            $table->string('reference_number')->nullable()->after('remarks');
            $table->unsignedBigInteger('returned_by')->nullable()->after('reference_number');

            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('affected_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('returned_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropForeign(['affected_user_id']);
            $table->dropForeign(['returned_by']);
            $table->dropColumn(['quantity_change', 'old_quantity', 'new_quantity', 'performed_by', 'affected_user_id', 'reason', 'remarks', 'reference_number', 'returned_by']);
        });
    }
};
