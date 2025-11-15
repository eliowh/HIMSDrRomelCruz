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
            // Make item_name nullable since we're using description field
            $table->string('item_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {
            // Revert item_name to not nullable
            $table->string('item_name')->nullable(false)->change();
        });
    }
};
