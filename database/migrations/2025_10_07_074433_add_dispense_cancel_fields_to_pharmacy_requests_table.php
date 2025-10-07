<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->timestamp('dispensed_at')->nullable();
            $table->unsignedBigInteger('dispensed_by')->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            
            // Add foreign key constraints
            $table->foreign('dispensed_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacy_requests', function (Blueprint $table) {
            $table->dropForeign(['dispensed_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['dispensed_at', 'dispensed_by', 'cancelled_by']);
        });
    }
};
