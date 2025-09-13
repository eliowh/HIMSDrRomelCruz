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

    // Added: list patients for nurse
    public function index(Request $request)
    {
        $q = $request->query('q');
        $patients = Patient::when($q, function ($query, $q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('middle_name', 'like', "%{$q}%")
                      ->orWhere('patient_no', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('patient_no')
            ->paginate(20)
            ->withQueryString();

        return view('nurse.nurse_patients', compact('patients', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:191',
            'middle_name' => 'nullable|string|max:191',
            'last_name' => 'required|string|max:191',
            'date_of_birth' => 'nullable|date',
            'age_years' => 'nullable|integer|min:0',
            'age_months' => 'nullable|integer|min:0',
            'age_days' => 'nullable|integer|min:0',
            'province' => 'nullable|string|max:191',
            'city' => 'nullable|string|max:191',
            'barangay' => 'nullable|string|max:191',
            'nationality' => 'nullable|string|max:191',
        ]);

        $patient = Patient::create($data);

        // optional debug log:
        \Log::info('Patient created', ['id' => $patient->id, 'patient_no' => $patient->patient_no]);

        // redirect to patients list so new record is visible immediately
        return redirect(url('/nurse/patients'))->with('success', 'Patient created. Patient No: '.$patient->patient_no);
    }
}