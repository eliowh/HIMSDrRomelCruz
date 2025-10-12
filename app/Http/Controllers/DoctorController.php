<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Display the doctor dashboard.
     */
    public function dashboard()
    {
        $doctorName = Auth::user()->name;
        // Provide the patients collection to the doctor dashboard (same as nurse dashboard)
        $patients = Patient::orderBy('created_at', 'desc')->get();
        return view('doctor.doctor_home', compact('doctorName', 'patients'));
    }

    /**
     * Display the list of patients - same database as nurses for synchronized information
     */
    public function patients(Request $request)
    {
        // Use the exact same logic as PatientController@index for nurses
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

    /**
     * Display the specified patient's details.
     */
    public function showPatient($id)
    {
        $doctor = Auth::user();
        
        // Ensure the patient is assigned to this doctor
        $patient = $doctor->patients()->findOrFail($id);
        
        return view('doctor.patient_details', compact('patient'));
    }
}
