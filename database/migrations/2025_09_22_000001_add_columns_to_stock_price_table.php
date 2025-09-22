<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_price', function (Blueprint $table) {
            if (!Schema::hasColumn('stock_price', 'generic_name')) {
                $table->string('generic_name')->nullable();
            }
            if (!Schema::hasColumn('stock_price', 'brand_name')) {
                $table->string('brand_name')->nullable();
            }
            if (!Schema::hasColumn('stock_price', 'price')) {
                $table->decimal('price', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('stock_price', function (Blueprint $table) {
            if (Schema::hasColumn('stock_price', 'price')) {
                $table->dropColumn('price');
            }
            if (Schema::hasColumn('stock_price', 'brand_name')) {
                $table->dropColumn('brand_name');
            }
            if (Schema::hasColumn('stock_price', 'generic_name')) {
                $table->dropColumn('generic_name');
            }
        });
    }
};
