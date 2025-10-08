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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('billing_number')->unique();
            $table->enum('status', ['active', 'discharged', 'paid', 'cancelled'])->default('active');
            $table->date('admission_date');
            $table->date('discharge_date')->nullable();
            $table->decimal('room_charges', 10, 2)->default(0);
            $table->decimal('professional_fee', 10, 2)->default(0);
            $table->decimal('medicine_charges', 10, 2)->default(0);
            $table->decimal('lab_charges', 10, 2)->default(0);
            $table->decimal('other_charges', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('senior_pwd_discount', 10, 2)->default(0);
            $table->decimal('philhealth_coverage', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->boolean('is_senior_pwd')->default(false);
            $table->boolean('is_philhealth_member')->default(false);
            $table->string('philhealth_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
