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
        Schema::create('lab_analyses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_order_id');
            $table->unsignedBigInteger('doctor_id');
            $table->text('clinical_notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->timestamp('analyzed_at');
            $table->timestamps();
            
            $table->foreign('lab_order_id')->references('id')->on('lab_orders');
            $table->foreign('doctor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_analyses');
    }
};
