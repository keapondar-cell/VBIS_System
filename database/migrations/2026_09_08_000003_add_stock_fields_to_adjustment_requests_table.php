<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adjustment_requests', function (Blueprint $table) {
            $table->integer('quantity_change')->nullable()->after('new_quantity');
            $table->string('reason')->nullable()->after('quantity_change');
            $table->unsignedBigInteger('performed_by')->nullable()->after('reason');

            $table->foreign('performed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('adjustment_requests', function (Blueprint $table) {
            $table->dropForeign(['performed_by']);
            $table->dropColumn(['quantity_change', 'reason', 'performed_by']);
        });
    }
};
