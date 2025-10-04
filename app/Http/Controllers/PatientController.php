<?php

namespace App\Http\Controllers;

use App\Models\Patient;
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

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'last_name' => 'required|string|max:191',
            'date_of_birth' => 'required|date',
            'province' => 'required|string|max:191',
            'city' => 'required|string|max:191',
            'barangay' => 'required|string|max:191',
            'nationality' => 'required|string|max:191',
            // admission fields
            'room_no' => 'nullable|string|max:50',
            'admission_type' => 'nullable|string|max:100',
            'service' => 'nullable|string|max:100',
            'doctor_name' => 'nullable|string|max:191',
            'doctor_type' => 'nullable|string|max:100',
            'admission_diagnosis' => 'nullable|string|max:2000',
        ]);

        $patient = Patient::create($data);

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
            'date_of_birth' => 'nullable|date',
            'province' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'barangay' => 'nullable|string|max:191',
            'nationality' => 'nullable|string|max:191',
            'room_no' => 'nullable|string|max:50',
            'admission_type' => 'nullable|string|max:100',
            'service' => 'nullable|string|max:100',
            'doctor_name' => 'nullable|string|max:191',
            'doctor_type' => 'nullable|string|max:100',
            'admission_diagnosis' => 'nullable|string|max:2000',
        ]);

        $patient->update($data);

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
}