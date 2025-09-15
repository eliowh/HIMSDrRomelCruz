<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test patients with realistic medical data
        $patients = [
            [
                'patient_id' => 'P001',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'date_of_birth' => '1989-05-15',
                'gender' => 'Female',
                'phone' => '+1-555-0101',
                'email' => 'sarah.johnson@email.com',
                'address' => '123 Main Street, Cityville, State 12345',
                'emergency_contact_name' => 'Mark Johnson (Husband)',
                'emergency_contact_phone' => '+1-555-0102',
                'blood_type' => 'A+',
                'allergies' => json_encode(['Penicillin', 'Shellfish']),
                'medical_history' => 'Previous appendectomy (2018), Seasonal allergies, Mild asthma',
                'current_medications' => json_encode([
                    'Albuterol Inhaler - As needed for asthma',
                    'Claritin 10mg - Daily for allergies'
                ]),
                'insurance_provider' => 'HealthCare Plus',
                'insurance_number' => 'HCP-123456789',
                'primary_diagnosis' => 'General checkup and preventive care',
                'admission_date' => now()->subDays(2),
                'room_number' => '205',
                'status' => 'admitted',
                'notes' => 'Patient admitted for routine procedure. Recovery going well.'
            ],
            [
                'patient_id' => 'P002',
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'date_of_birth' => '1975-11-22',
                'gender' => 'Male',
                'phone' => '+1-555-0201',
                'email' => 'michael.chen@email.com',
                'address' => '456 Oak Avenue, Townsburg, State 67890',
                'emergency_contact_name' => 'Lisa Chen (Wife)',
                'emergency_contact_phone' => '+1-555-0202',
                'blood_type' => 'O-',
                'allergies' => json_encode(['Latex']),
                'medical_history' => 'Type 2 Diabetes (diagnosed 2015), Hypertension, Family history of heart disease',
                'current_medications' => json_encode([
                    'Metformin 500mg - Twice daily',
                    'Lisinopril 10mg - Once daily',
                    'Atorvastatin 20mg - Daily'
                ]),
                'insurance_provider' => 'MedSecure Insurance',
                'insurance_number' => 'MSI-987654321',
                'primary_diagnosis' => 'Diabetes management and follow-up',
                'admission_date' => now()->subDays(1),
                'room_number' => '108',
                'status' => 'admitted',
                'notes' => 'Blood sugar levels stabilizing. Continue current medication regimen.'
            ],
            [
                'patient_id' => 'P003',
                'first_name' => 'Emma',
                'last_name' => 'Wilson',
                'date_of_birth' => '1993-08-07',
                'gender' => 'Female',
                'phone' => '+1-555-0301',
                'email' => 'emma.wilson@email.com',
                'address' => '789 Pine Road, Villageton, State 54321',
                'emergency_contact_name' => 'Robert Wilson (Father)',
                'emergency_contact_phone' => '+1-555-0302',
                'blood_type' => 'B+',
                'allergies' => json_encode(['None known']),
                'medical_history' => 'No significant medical history. Regular exercise, healthy lifestyle.',
                'current_medications' => json_encode(['Prenatal vitamins - Daily']),
                'insurance_provider' => 'FamilyCare Health',
                'insurance_number' => 'FCH-456789123',
                'primary_diagnosis' => 'Prenatal care - Second trimester',
                'admission_date' => now(),
                'room_number' => '312',
                'status' => 'outpatient',
                'notes' => 'Routine prenatal checkup. All vital signs normal. Next appointment in 4 weeks.'
            ],
            [
                'patient_id' => 'P004',
                'first_name' => 'Robert',
                'last_name' => 'Davis',
                'date_of_birth' => '1958-03-14',
                'gender' => 'Male',
                'phone' => '+1-555-0401',
                'email' => 'robert.davis@email.com',
                'address' => '321 Elm Street, Hamletville, State 98765',
                'emergency_contact_name' => 'Margaret Davis (Wife)',
                'emergency_contact_phone' => '+1-555-0402',
                'blood_type' => 'AB+',
                'allergies' => json_encode(['Aspirin', 'NSAIDs']),
                'medical_history' => 'Coronary artery disease, Previous MI (2019), Chronic kidney disease stage 3',
                'current_medications' => json_encode([
                    'Clopidogrel 75mg - Daily',
                    'Carvedilol 12.5mg - Twice daily',
                    'Furosemide 40mg - Daily',
                    'Pravastatin 40mg - Daily'
                ]),
                'insurance_provider' => 'SeniorCare Plus',
                'insurance_number' => 'SCP-789123456',
                'primary_diagnosis' => 'Post-operative cardiac care',
                'admission_date' => now()->subDays(5),
                'room_number' => '150',
                'status' => 'admitted',
                'notes' => 'Post-operative recovery from cardiac catheterization. Monitoring heart function.'
            ],
            [
                'patient_id' => 'P005',
                'first_name' => 'Alice',
                'last_name' => 'Cooper',
                'date_of_birth' => '1982-12-25',
                'gender' => 'Female',
                'phone' => '+1-555-0501',
                'email' => 'alice.cooper@email.com',
                'address' => '654 Maple Drive, Suburbia, State 13579',
                'emergency_contact_name' => 'James Cooper (Brother)',
                'emergency_contact_phone' => '+1-555-0502',
                'blood_type' => 'O+',
                'allergies' => json_encode(['Morphine', 'Codeine']),
                'medical_history' => 'Previous cesarean section (2020), Anxiety disorder, Migraine headaches',
                'current_medications' => json_encode([
                    'Sertraline 50mg - Daily',
                    'Sumatriptan 50mg - As needed for migraines'
                ]),
                'insurance_provider' => 'ComprehensiveCare',
                'insurance_number' => 'CC-135792468',
                'primary_diagnosis' => 'Post-surgical recovery',
                'admission_date' => now()->subDays(3),
                'room_number' => '101',
                'status' => 'admitted',
                'notes' => 'Recovering from gallbladder surgery. Pain well controlled with non-opioid medications.'
            ]
        ];

        // Insert patients
        foreach ($patients as $patientData) {
            Patient::create($patientData);
        }

        // Get all doctors from the users table
        $doctors = User::where('role', 'doctor')->get();
        
        if ($doctors->isNotEmpty()) {
            // Get all patients
            $patients = Patient::all();
            
            // Assign each patient to all doctors
            foreach ($patients as $patient) {
                foreach ($doctors as $doctor) {
                    DB::table('doctor_patient')->insert([
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patient->id,
                        'assigned_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }
}
