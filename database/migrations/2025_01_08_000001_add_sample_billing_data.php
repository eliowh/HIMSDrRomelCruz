<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Add some sample admission diagnoses to existing patients
        DB::table('patients')->where('id', 1)->update(['admission_diagnosis' => 'A00.0']);
        DB::table('patients')->where('id', 2)->update(['admission_diagnosis' => 'A01.0']);
        DB::table('patients')->where('id', 3)->update(['admission_diagnosis' => 'B00.9']);
        
        // Add some sample lab orders
        $patients = DB::table('patients')->limit(3)->get();
        
        foreach ($patients as $patient) {
            DB::table('lab_orders')->insert([
                'patient_id' => $patient->id,
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'patient_no' => $patient->patient_no,
                'test_requested' => 'Complete Blood Count (CBC)',
                'status' => 'completed',
                'priority' => 'normal',
                'price' => 500.00,
                'requested_by' => 1,
                'requested_at' => now(),
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::table('lab_orders')->insert([
                'patient_id' => $patient->id,
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'patient_no' => $patient->patient_no,
                'test_requested' => 'Urinalysis',
                'status' => 'completed',
                'priority' => 'normal',
                'price' => 200.00,
                'requested_by' => 1,
                'requested_at' => now(),
                'completed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        // Remove test data
        DB::table('patients')->whereIn('id', [1, 2, 3])->update(['admission_diagnosis' => null]);
        DB::table('lab_orders')->whereIn('patient_id', [1, 2, 3])->delete();
    }
};