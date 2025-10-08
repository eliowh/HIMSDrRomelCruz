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
            // Change date_charged from date to datetime
            $table->datetime('date_charged')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {
            // Change back to date
            $table->date('date_charged')->change();
        });
    }
};
