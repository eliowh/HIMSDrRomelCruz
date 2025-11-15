<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, migrate existing admission data from patients to admissions table
        $patients = DB::table('patients')
            ->whereNotNull('room_no')
            ->orWhereNotNull('admission_type')
            ->orWhereNotNull('doctor_name')
            ->get();

        foreach ($patients as $patient) {
            // Generate admission number
            $admissionNumber = 'ADM-' . date('Y') . '-' . str_pad($patient->id, 6, '0', STR_PAD_LEFT);
            
            $admissionId = DB::table('admissions')->insertGetId([
                'patient_id' => $patient->id,
                'admission_number' => $admissionNumber,
                'room_no' => $patient->room_no,
                'admission_type' => $patient->admission_type,
                'service' => $patient->service ?? null,
                'doctor_name' => $patient->doctor_name,
                'doctor_type' => $patient->doctor_type,
                'admission_diagnosis' => $patient->admission_diagnosis,
                'admission_date' => $patient->created_at ?? now(),
                'status' => 'active',
                'created_at' => $patient->created_at ?? now(),
                'updated_at' => $patient->updated_at ?? now(),
            ]);

            // Update billings table to reference the admission
            DB::table('billings')
                ->where('patient_id', $patient->id)
                ->update(['admission_id' => $admissionId]);
        }

        // Now remove admission fields from patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'room_no',
                'admission_type', 
                'service',
                'doctor_name',
                'doctor_type',
                'admission_diagnosis'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add admission fields to patients table
        Schema::table('patients', function (Blueprint $table) {
            $table->string('room_no')->nullable()->after('patient_no');
            $table->string('admission_type')->nullable()->after('room_no');
            $table->string('service')->nullable()->after('admission_type');
            $table->string('doctor_name')->nullable()->after('service');
            $table->string('doctor_type')->nullable()->after('doctor_name');
            $table->text('admission_diagnosis')->nullable()->after('doctor_type');
        });

        // Migrate data back from admissions to patients
        $admissions = DB::table('admissions')->get();
        
        foreach ($admissions as $admission) {
            DB::table('patients')
                ->where('id', $admission->patient_id)
                ->update([
                    'room_no' => $admission->room_no,
                    'admission_type' => $admission->admission_type,
                    'service' => $admission->service,
                    'doctor_name' => $admission->doctor_name,
                    'doctor_type' => $admission->doctor_type,
                    'admission_diagnosis' => $admission->admission_diagnosis,
                ]);
        }

        // Clear admissions table data (will be dropped by previous migration rollback)
        DB::table('admissions')->truncate();
    }
};
