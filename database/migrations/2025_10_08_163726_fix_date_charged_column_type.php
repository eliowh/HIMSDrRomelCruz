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
        Schema::table('billing_items', function (Blueprint $table) {
            // First check if date_charged exists, if not rename service_date to date_charged
            if (Schema::hasColumn('billing_items', 'service_date') && !Schema::hasColumn('billing_items', 'date_charged')) {
                $table->renameColumn('service_date', 'date_charged');
            }
            
            // Check if total_amount exists, if not rename total_price to total_amount
            if (Schema::hasColumn('billing_items', 'total_price') && !Schema::hasColumn('billing_items', 'total_amount')) {
                $table->renameColumn('total_price', 'total_amount');
            }
        });
        
        // Handle type changes in separate schema call
        Schema::table('billing_items', function (Blueprint $table) {
            // Then ensure date_charged is datetime type
            if (Schema::hasColumn('billing_items', 'date_charged')) {
                $table->datetime('date_charged')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {
            // Change back to date type
            if (Schema::hasColumn('billing_items', 'date_charged')) {
                $table->date('date_charged')->change();
            }
        });
        
        Schema::table('billing_items', function (Blueprint $table) {
            // Rename columns back
            if (Schema::hasColumn('billing_items', 'date_charged')) {
                $table->renameColumn('date_charged', 'service_date');
            }
            if (Schema::hasColumn('billing_items', 'total_amount')) {
                $table->renameColumn('total_amount', 'total_price');
            }
        });
    }
};
