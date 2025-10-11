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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('admission_number')->unique();
            $table->string('room_no')->nullable();
            $table->string('admission_type')->nullable();
            $table->string('service')->nullable();
            $table->string('doctor_name')->nullable();
            $table->string('doctor_type')->nullable();
            $table->text('admission_diagnosis')->nullable();
            $table->datetime('admission_date');
            $table->datetime('discharge_date')->nullable();
            $table->enum('status', ['active', 'discharged', 'transferred'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('patient_id');
            $table->index('admission_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
