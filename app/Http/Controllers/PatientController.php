<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Admission;
use App\Models\Report;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function create()
    {
        return view('nurse.nurse_addPatients');
    }

    public function index(Request $request)
    {
        $q = $request->query('q');
        $patients = Patient::when($q, function ($query, $q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name','like',"%{$q}%")
                      ->orWhere('last_name','like',"%{$q}%")
                      ->orWhere('middle_name','like',"%{$q}%")
                      ->orWhere('patient_no','like',"%{$q}%");
                });
            })
            ->orderByDesc('patient_no')
            ->paginate(10)
            ->withQueryString();

        return view('nurse.nurse_patients', compact('patients','q'));
    }
    
    public function labtechPatients(Request $request)
    {
        $q = $request->query('q');
        $patients = Patient::when($q, function ($query, $q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name','like',"%{$q}%")
                      ->orWhere('last_name','like',"%{$q}%")
                      ->orWhere('middle_name','like',"%{$q}%")
                      ->orWhere('patient_no','like',"%{$q}%");
                });
            })
            ->orderByDesc('patient_no')
            ->paginate(10)
            ->withQueryString();

        return view('labtech.labtech_patients', compact('patients','q'));
    }
    
    public function doctorIndex(Request $request)
    {
        $q = $request->query('q');
        $patients = Patient::when($q, function ($query, $q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name','like',"%{$q}%")
                      ->orWhere('last_name','like',"%{$q}%")
                      ->orWhere('middle_name','like',"%{$q}%")
                      ->orWhere('patient_no','like',"%{$q}%");
                });
            })
            ->orderByDesc('patient_no')
            ->paginate(10)
            ->withQueryString();

        return view('doctor.doctor_patients', compact('patients','q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'last_name' => 'required|string|max:191',
            'sex' => 'nullable|in:male,female,other',
            'contact_number' => 'nullable|string|max:20',
            'date_of_birth' => 'required|date|before_or_equal:today',
            'province' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'barangay' => 'required|string|max:191',
            'nationality' => 'required|string|max:191',
            // admission fields
            'room_no' => 'nullable|string|max:50',
            'admission_type' => 'nullable|string|max:100',
            'doctor_name' => 'nullable|string|max:191',
            'doctor_type' => 'nullable|string|max:100',
            'admission_diagnosis' => 'nullable|string|max:2000',
            // health history fields
            'chronic_illnesses' => 'nullable|string|max:2000',
            'hospitalization_history' => 'nullable|string|max:2000',
            'surgery_history' => 'nullable|string|max:2000',
            'accident_injury_history' => 'nullable|string|max:2000',
            'current_medications' => 'nullable|string|max:2000',
            'long_term_medications' => 'nullable|string|max:2000',
            'known_allergies' => 'nullable|string|max:2000',
            'family_history_chronic' => 'nullable|string|max:2000',
            // social history fields
            'smoking_history' => 'nullable|string|max:1000',
            'alcohol_consumption' => 'nullable|string|max:1000',
            'recreational_drugs' => 'nullable|string|max:1000',
            'exercise_activity' => 'nullable|string|max:1000',
        ]);

        // Separate patient data from admission data
        $patientData = collect($data)->only([
            'first_name', 'middle_name', 'last_name', 'sex', 'contact_number',
            'date_of_birth', 'province', 'city', 'barangay', 'nationality'
        ])->filter()->toArray();

        // Prepare health history data
        $generalHealthHistory = [
            'medical_conditions' => [
                'chronic_illnesses' => $data['chronic_illnesses'] ?? null,
                'hospitalization_history' => $data['hospitalization_history'] ?? null,
                'surgery_history' => $data['surgery_history'] ?? null,
                'accident_injury_history' => $data['accident_injury_history'] ?? null,
            ],
            'medications' => [
                'current_medications' => $data['current_medications'] ?? null,
                'long_term_medications' => $data['long_term_medications'] ?? null,
            ],
            'allergies' => [
                'known_allergies' => $data['known_allergies'] ?? null,
            ],
            'family_history' => [
                'family_history_chronic' => $data['family_history_chronic'] ?? null,
            ]
        ];

        $socialHistory = [
            'lifestyle_habits' => [
                'smoking_history' => $data['smoking_history'] ?? null,
                'alcohol_consumption' => $data['alcohol_consumption'] ?? null,
                'recreational_drugs' => $data['recreational_drugs'] ?? null,
                'exercise_activity' => $data['exercise_activity'] ?? null,
            ]
        ];

        // Add health history to patient data
        $patientData['general_health_history'] = $generalHealthHistory;
        $patientData['social_history'] = $socialHistory;

        $admissionData = collect($data)->only([
            'room_no', 'admission_type', 'doctor_name', 'doctor_type', 'admission_diagnosis'
        ])->filter()->toArray();

        // Create patient
        $patient = Patient::create($patientData);

        // Audit: Log patient creation
        try {
            Report::log('Patient Created', Report::TYPE_USER_REPORT, 'New patient record created', [
                'patient_id' => $patient->id,
                'patient_no' => $patient->patient_no,
                'name' => $patient->first_name . ' ' . $patient->last_name,
                'created_by' => auth()->id(),
                'created_at' => now()->toDateTimeString(),
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to create patient audit: ' . $e->getMessage());
        }

        // Create admission if admission data provided
        if (!empty($admissionData)) {
            // Add service field for backward compatibility
            if (!empty($admissionData['admission_type'])) {
                $admissionData['service'] = $admissionData['admission_type'];
            }

            $admissionData['patient_id'] = $patient->id;
            $admissionData['admission_date'] = now();
            $admissionData['status'] = 'active';
            Admission::create($admissionData);
        }

        \Log::info('Patient created', ['id' => $patient->id, 'patient_no' => $patient->patient_no]);

        return redirect(url('/nurse/patients'))->with('success', 'Patient created. Patient No: '.$patient->patient_no);
    }

    /**
     * Update patient by patient_no (nurse editable fields)
     */
    public function update(Request $request, $patient_no)
    {
        $patient = Patient::where('patient_no', $patient_no)->firstOrFail();

        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'last_name' => 'required|string|max:191',
            'sex' => 'nullable|in:male,female,other',
            'contact_number' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'province' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'barangay' => 'nullable|string|max:191',
            'nationality' => 'nullable|string|max:191',
            'room_no' => 'nullable|string|max:50',
            'admission_type' => 'nullable|string|max:100',
            'doctor_name' => 'nullable|string|max:191',
            'doctor_type' => 'nullable|string|max:100',
            'admission_diagnosis' => 'nullable|string|max:2000',
            // health history fields
            'chronic_illnesses' => 'nullable|string|max:2000',
            'hospitalization_history' => 'nullable|string|max:2000',
            'surgery_history' => 'nullable|string|max:2000',
            'accident_injury_history' => 'nullable|string|max:2000',
            'current_medications' => 'nullable|string|max:2000',
            'long_term_medications' => 'nullable|string|max:2000',
            'known_allergies' => 'nullable|string|max:2000',
            'family_history_chronic' => 'nullable|string|max:2000',
            // social history fields
            'smoking_history' => 'nullable|string|max:1000',
            'alcohol_consumption' => 'nullable|string|max:1000',
            'recreational_drugs' => 'nullable|string|max:1000',
            'exercise_activity' => 'nullable|string|max:1000',
        ]);

        // Separate patient data from admission data
        $patientData = collect($data)->only([
            'first_name', 'middle_name', 'last_name', 'sex', 'contact_number',
            'date_of_birth', 'province', 'city', 'barangay', 'nationality'
        ])->filter()->toArray();

        // Prepare health history data
        $generalHealthHistory = [
            'medical_conditions' => [
                'chronic_illnesses' => $data['chronic_illnesses'] ?? null,
                'hospitalization_history' => $data['hospitalization_history'] ?? null,
                'surgery_history' => $data['surgery_history'] ?? null,
                'accident_injury_history' => $data['accident_injury_history'] ?? null,
            ],
            'medications' => [
                'current_medications' => $data['current_medications'] ?? null,
                'long_term_medications' => $data['long_term_medications'] ?? null,
            ],
            'allergies' => [
                'known_allergies' => $data['known_allergies'] ?? null,
            ],
            'family_history' => [
                'family_history_chronic' => $data['family_history_chronic'] ?? null,
            ]
        ];

        $socialHistory = [
            'lifestyle_habits' => [
                'smoking_history' => $data['smoking_history'] ?? null,
                'alcohol_consumption' => $data['alcohol_consumption'] ?? null,
                'recreational_drugs' => $data['recreational_drugs'] ?? null,
                'exercise_activity' => $data['exercise_activity'] ?? null,
            ]
        ];

        // Add health history to patient data
        $patientData['general_health_history'] = $generalHealthHistory;
        $patientData['social_history'] = $socialHistory;

        $admissionData = collect($data)->only([
            'room_no', 'admission_type', 'doctor_name', 'doctor_type', 'admission_diagnosis'
        ])->filter()->toArray();

        // Update patient data
        if (!empty($patientData)) {
            $patient->update($patientData);
        }

        // Update or create admission data if provided
        if (!empty($admissionData)) {
            // Add service field for backward compatibility
            if (!empty($admissionData['admission_type'])) {
                $admissionData['service'] = $admissionData['admission_type'];
            }

            // Get current admission or create new one
            $currentAdmission = $patient->currentAdmission;
            
            if ($currentAdmission) {
                // Update existing admission
                $currentAdmission->update($admissionData);
            } else {
                // Create new admission
                $admissionData['patient_id'] = $patient->id;
                $admissionData['admission_date'] = now();
                $admissionData['status'] = 'active';
                Admission::create($admissionData);
            }
        }

        return response()->json(['ok' => true, 'message' => 'Patient updated']);
    }

    /**
     * Delete a patient by patient_no
     */
    public function destroy(Request $request, $patient_no)
    {
        $patient = Patient::where('patient_no', $patient_no)->firstOrFail();
        $patient->delete();
        return response()->json(['ok' => true, 'message' => 'Patient deleted']);
    }

    /**
     * Get patient admissions for API
     */
    public function getPatientAdmissionsApi($patientId)
    {
        try {
            $patient = Patient::findOrFail($patientId);
            
            $admissions = $patient->admissions()
                ->orderBy('admission_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'admissions' => $admissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load patient admissions: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create a new admission for an existing patient
     */
    public function createAdmission(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'room_no' => 'required|string|max:20',
                'admission_type' => 'required|in:Emergency,Outpatient,Inpatient',
                'doctor_name' => 'required|string|max:100',
                'doctor_type' => 'nullable|string|max:50',
                'admission_diagnosis' => 'nullable|string|max:20',
                'admission_diagnosis_description' => 'nullable|string|max:500',
            ]);
            
            // Check if patient already has an active admission
            $activeAdmission = \DB::table('admissions')
                ->where('patient_id', $validated['patient_id'])
                ->where('status', 'active')
                ->first();
                
            if ($activeAdmission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient already has an active admission. Please discharge the current admission first.'
                ], 422);
            }
            
            // Create new admission (exclude admission_diagnosis_description as it doesn't exist in DB)
            $admissionId = \DB::table('admissions')->insertGetId([
                'patient_id' => $validated['patient_id'],
                'admission_number' => 'ADM-' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'room_no' => $validated['room_no'],
                'admission_type' => $validated['admission_type'],
                'doctor_name' => $validated['doctor_name'],
                'doctor_type' => $validated['doctor_type'] ?? null,
                'admission_diagnosis' => $validated['admission_diagnosis'] ?? null,
                'admission_date' => now(),
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Get the created admission for response
            $admission = \DB::table('admissions')->where('id', $admissionId)->first();

            // Audit: Log admission creation
            try {
                Report::log('Patient Admitted', Report::TYPE_USER_REPORT, 'New admission created', [
                    'patient_id' => $validated['patient_id'],
                    'admission_id' => $admissionId,
                    'room_no' => $validated['room_no'],
                    'admission_type' => $validated['admission_type'],
                    'doctor_name' => $validated['doctor_name'],
                    'admission_date' => now()->toDateTimeString(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create admission audit: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'New admission created successfully!',
                'admission' => $admission
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create admission: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get the active admission for a patient
     */
    public function getActiveAdmission($patientId)
    {
        try {
            $activeAdmission = \DB::table('admissions')
                ->where('patient_id', $patientId)
                ->where('status', 'active')
                ->first();
                
            if ($activeAdmission) {
                return response()->json([
                    'success' => true,
                    'admission' => $activeAdmission
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No active admission found for this patient'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active admission: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Discharge patient admission (only if billing is paid)
     */
    public function dischargePatient(Request $request, $admissionId)
    {
        try {
            $admission = Admission::with(['billings', 'patient'])->findOrFail($admissionId);
            
            // Check if admission is still active
            if ($admission->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'This admission is not active and cannot be discharged.'
                ]);
            }
            
            // Check if there's a paid billing for this admission
            $paidBilling = $admission->billings()->where('status', 'paid')->first();
            if (!$paidBilling) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot discharge patient. Billing must be cleared first.'
                ]);
            }
            
            // Discharge the admission
            $admission->discharge();
            
            return response()->json([
                'success' => true,
                'message' => "Patient {$admission->patient->first_name} {$admission->patient->last_name} has been successfully discharged."
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to discharge patient: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current active admission for a patient
     */
    public function getCurrentAdmission($id)
    {
        try {
            $patient = Patient::findOrFail($id);
            $currentAdmission = $patient->currentAdmission;

            if ($currentAdmission) {
                return response()->json([
                    'success' => true,
                    'admission' => [
                        'id' => $currentAdmission->id,
                        'room_no' => $currentAdmission->room_no,
                        'admission_type' => $currentAdmission->admission_type,
                        'doctor_name' => $currentAdmission->doctor_name,
                        'doctor_type' => $currentAdmission->doctor_type,
                        'admission_diagnosis' => $currentAdmission->admission_diagnosis,
                        'admission_date' => $currentAdmission->admission_date,
                        'status' => $currentAdmission->status,
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No active admission found for this patient'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch admission data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize an admission with final diagnosis (doctor action)
     */
    public function finalizeAdmission(Request $request, $admissionId)
    {
        try {
            $validated = $request->validate([
                'final_diagnosis' => 'required|string|max:2000',
                'final_diagnosis_description' => 'nullable|string|max:2000',
            ]);

            $admission = Admission::findOrFail($admissionId);

            $admission->update([
                'final_diagnosis' => $validated['final_diagnosis'],
                'final_diagnosis_description' => $validated['final_diagnosis_description'] ?? null,
            ]);

            // Audit: Log admission finalization
            try {
                Report::log('Admission Finalized', Report::TYPE_USER_REPORT, 'Admission finalized with final diagnosis', [
                    'admission_id' => $admission->id,
                    'patient_id' => $admission->patient_id ?? null,
                    'final_diagnosis' => \Illuminate\Support\Str::limit($validated['final_diagnosis'], 400),
                    'final_diagnosis_description' => isset($validated['final_diagnosis_description']) ? \Illuminate\Support\Str::limit($validated['final_diagnosis_description'], 800) : null,
                    'finalized_by' => auth()->id(),
                    'finalized_at' => now()->toDateTimeString(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to create admission finalize audit: ' . $e->getMessage());
            }

            return response()->json(['success' => true, 'message' => 'Final diagnosis saved']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to finalize admission: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get health history for a specific patient
     */
    public function getHealthHistory($patientId)
    {
        try {
            $patient = Patient::findOrFail($patientId);
            
            // Get health history from the JSON fields
            $generalHealthHistory = $patient->general_health_history ?? [];
            $socialHistory = $patient->social_history ?? [];
            
            // Extract medical conditions
            $medicalConditions = $generalHealthHistory['medical_conditions'] ?? [];
            
            // Extract medications
            $medications = $generalHealthHistory['medications'] ?? [];
            
            // Extract allergies
            $allergies = $generalHealthHistory['allergies'] ?? [];
            
            // Extract family history
            $familyHistory = $generalHealthHistory['family_history'] ?? [];
            
            // Extract social history (lifestyle habits)
            $lifestyleHabits = $socialHistory['lifestyle_habits'] ?? [];
            
            $healthHistory = [
                'chronic_illnesses' => $medicalConditions['chronic_illnesses'] ?? null,
                'hospitalization_history' => $medicalConditions['hospitalization_history'] ?? null,
                'surgery_history' => $medicalConditions['surgery_history'] ?? null,
                'accident_injury_history' => $medicalConditions['accident_injury_history'] ?? null,
                'current_medications' => $medications['current_medications'] ?? null,
                'longterm_medications' => $medications['long_term_medications'] ?? null,
                'known_allergies' => $allergies['known_allergies'] ?? null,
                'family_history_chronic_diseases' => $familyHistory['family_history_chronic'] ?? null,
                'smoking_history' => $lifestyleHabits['smoking_history'] ?? null,
                'alcohol_consumption' => $lifestyleHabits['alcohol_consumption'] ?? null,
                'recreational_drugs' => $lifestyleHabits['recreational_drugs'] ?? null,
                'exercise_activity' => $lifestyleHabits['exercise_activity'] ?? null,
            ];
            
            return response()->json([
                'success' => true,
                'health_history' => $healthHistory
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get health history: ' . $e->getMessage()
            ], 500);
        }
    }
}