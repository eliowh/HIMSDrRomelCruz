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
        // If the table already exists (from a duplicate migration), skip creation to avoid SQL errors
        if (Schema::hasTable('patients')) {
            return;
        }

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_id')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('date_of_birth');
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('blood_type', 5)->nullable();
            $table->json('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->json('current_medications')->nullable();
            $table->string('insurance_provider')->nullable();
            $table->string('insurance_number')->nullable();
            $table->string('primary_diagnosis')->nullable();
            $table->datetime('admission_date')->nullable();
            $table->string('room_number', 10)->nullable();
            $table->enum('status', ['admitted', 'outpatient', 'discharged', 'emergency'])->default('admitted');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
