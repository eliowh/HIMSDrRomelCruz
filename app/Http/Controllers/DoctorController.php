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
        return view('doctor.doctor_home', compact('doctorName'));
    }

    /**
     * Display the list of patients assigned to the current doctor.
     */
    public function patients(Request $request)
    {
        $doctor = Auth::user();
        
        // Start with base query
        $patientsQuery = $doctor->patients()->with(['doctors']);
        
        // Apply search filter if provided
        if ($request->has('query') && !empty(trim($request->query))) {
            $query = trim($request->query);
            $patientsQuery->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('patient_id', 'LIKE', "%{$query}%")
                  ->orWhere('room_number', 'LIKE', "%{$query}%")
                  ->orWhere('primary_diagnosis', 'LIKE', "%{$query}%")
                  ->orWhere('medical_history', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('phone', 'LIKE', "%{$query}%")
                  ->orWhere('emergency_contact_name', 'LIKE', "%{$query}%")
                  ->orWhere('blood_type', 'LIKE', "%{$query}%")
                  ->orWhere('insurance_provider', 'LIKE', "%{$query}%")
                  ->orWhere('insurance_number', 'LIKE', "%{$query}%")
                  // Search in allergies JSON field
                  ->orWhereRaw('JSON_SEARCH(allergies, "one", ?) IS NOT NULL', ["%{$query}%"])
                  // Search in current_medications JSON field
                  ->orWhereRaw('JSON_SEARCH(current_medications, "one", ?) IS NOT NULL', ["%{$query}%"]);
            });
        }
        
        // Apply status filter if provided
        if ($request->has('status') && !empty($request->status) && $request->status !== 'all') {
            $patientsQuery->where('status', $request->status);
        }
        
        // Get paginated results
        $patients = $patientsQuery
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(10);

        // Calculate statistics (for all patients, not filtered)
        $totalPatients = $doctor->patients()->count();
        $admittedPatients = $doctor->patients()->where('status', 'admitted')->count();
        $outpatients = $doctor->patients()->where('status', 'outpatient')->count();
        $criticalPatients = $doctor->patients()
            ->whereIn('status', ['emergency'])
            ->count();

        return view('doctor.doctor_patients', compact(
            'patients', 
            'totalPatients', 
            'admittedPatients', 
            'outpatients', 
            'criticalPatients'
        ));
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
