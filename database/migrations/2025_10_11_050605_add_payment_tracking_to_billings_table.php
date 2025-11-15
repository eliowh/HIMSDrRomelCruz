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
        Schema::table('billings', function (Blueprint $table) {
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_date');
            $table->decimal('change_amount', 10, 2)->nullable()->after('payment_amount');
            $table->unsignedBigInteger('processed_by')->nullable()->after('change_amount');
            
            // Add foreign key constraint for processed_by
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['payment_amount', 'change_amount', 'processed_by']);
        });
    }
};
