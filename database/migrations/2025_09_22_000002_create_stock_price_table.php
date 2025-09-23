<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_price')) {
            return;
        }

        Schema::create('stock_price', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->nullable()->index();
            $table->string('generic_name')->nullable()->index();
            $table->string('brand_name')->nullable()->index();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('quantity')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_price');
    }
};
