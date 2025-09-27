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
        Schema::table('stock_price', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('quantity');
            $table->integer('reorder_level')->default(10)->after('expiry_date');
            $table->string('supplier')->nullable()->after('reorder_level');
            $table->string('batch_number')->nullable()->after('supplier');
            $table->date('date_received')->nullable()->after('batch_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_price', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'reorder_level', 'supplier', 'batch_number', 'date_received']);
        });
    }
};
