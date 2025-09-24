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
        Schema::create('stocksreference', function (Blueprint $table) {
            $table->id();
            $table->string('COL1')->nullable()->comment('Item Code');
            $table->string('COL2')->nullable()->comment('Generic Name');
            $table->string('COL3')->nullable()->comment('Brand Name');
            $table->decimal('COL4', 10, 2)->nullable()->comment('Price');
            $table->string('COL5')->nullable()->comment('Additional Info');
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('COL1', 'idx_item_code');
            $table->index('COL2', 'idx_generic_name');
            $table->index('COL3', 'idx_brand_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocksreference');
    }
};