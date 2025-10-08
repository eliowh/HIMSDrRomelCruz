<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update first few patients with admission diagnoses and ensure they have data
        $patientUpdates = [
            1 => 'A00.0', // Cholera
            2 => 'A01.0', // Typhoid fever
            3 => 'B00.9', // Herpesviral infection, unspecified
        ];
        
        foreach ($patientUpdates as $patientId => $diagnosis) {
            // Update patient admission diagnosis
            DB::table('patients')->where('id', $patientId)->update([
                'admission_diagnosis' => $diagnosis
            ]);
            
            // Ensure lab orders exist and are completed
            $existingOrders = DB::table('lab_orders')->where('patient_id', $patientId)->count();
            
            if ($existingOrders == 0) {
                // Add lab orders
                $patient = DB::table('patients')->where('id', $patientId)->first();
                if ($patient) {
                    DB::table('lab_orders')->insert([
                        [
                            'patient_id' => $patientId,
                            'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                            'patient_no' => $patient->patient_no ?? '250001',
                            'test_requested' => 'Complete Blood Count (CBC)',
                            'status' => 'completed',
                            'priority' => 'normal',
                            'price' => 500.00,
                            'requested_by' => 1,
                            'requested_at' => now(),
                            'completed_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                        [
                            'patient_id' => $patientId,
                            'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                            'patient_no' => $patient->patient_no ?? '250001',
                            'test_requested' => 'Urinalysis',
                            'status' => 'completed',
                            'priority' => 'normal',
                            'price' => 200.00,
                            'requested_by' => 1,
                            'requested_at' => now(),
                            'completed_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    ]);
                }
            }
        }
    }

    public function down()
    {
        // Reset data
        DB::table('patients')->whereIn('id', [1, 2, 3])->update(['admission_diagnosis' => null]);
        DB::table('lab_orders')->whereIn('patient_id', [1, 2, 3])->delete();
    }
};