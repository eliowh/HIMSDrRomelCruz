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
        Schema::create('patient_medicines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('pharmacy_request_id');
            $table->string('patient_no')->nullable();
            $table->string('patient_name');
            $table->string('item_code')->nullable();
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('dispensed_by');
            $table->timestamp('dispensed_at');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('pharmacy_request_id')->references('id')->on('pharmacy_requests')->onDelete('cascade');
            $table->foreign('dispensed_by')->references('id')->on('users')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['patient_id', 'dispensed_at']);
            $table->index('pharmacy_request_id');
            $table->index('dispensed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_medicines');
    }
};
