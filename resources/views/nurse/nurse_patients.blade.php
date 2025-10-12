@extends('layouts.app')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

    <link rel="stylesheet" href="{{ asset('css/nursecss/nurse_patients.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/nursecss/edit_patient_modal.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nursecss/two_column_form.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nursecss/suggestion_dropdowns.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">
<div class="patients-grid">
    <div class="list-column">
        <div class="nurse-card">
            <div class="patients-header">
                <h3>Patients</h3>
                <form method="GET" class="patients-search">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search..." class="search-input">
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($patients->count())
                <div class="table-wrap">
                    <table class="patients-table" id="patientsTable">
                        <thead>
                            <tr>
                                <th>Patient No</th>
                                <th>Name</th>
                                <th>DOB / Age</th>
                                <th>Location</th>
                                <th>Nationality</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($patients as $p)
                            @php
                                $patientData = [
                                    'id' => $p->id,
                                    'patient_no' => $p->patient_no,
                                    'first_name' => $p->first_name,
                                    'middle_name' => $p->middle_name,
                                    'last_name' => $p->last_name,
                                    'sex' => $p->sex,
                                    'contact_number' => $p->contact_number,
                                    'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : null,
                                    'province' => $p->province,
                                    'city' => $p->city,
                                    'barangay' => $p->barangay,
                                    'nationality' => $p->nationality,
                                    'room_no' => $p->room_no,
                                    'admission_type' => $p->admission_type,
                                    'doctor_name' => $p->doctor_name,
                                    'doctor_type' => $p->doctor_type,
                                    'admission_diagnosis' => $p->admission_diagnosis,
                                    'general_health_history' => $p->general_health_history,
                                    'social_history' => $p->social_history,
                                    'created_at' => $p->created_at ? $p->created_at->format('Y-m-d H:i:s') : null
                                ];
                            @endphp
                            <tr class="patient-row" data-patient="{{ json_encode($patientData) }}">
                                <td class="col-no">{{ $p->patient_no }}</td>
                                <td class="col-name">{{ $p->last_name }}, {{ $p->first_name }}{{ $p->middle_name ? ' '.$p->middle_name : '' }}</td>
                                <td class="col-dob">
                                    {{ $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-' }}<br>
                                    @php
                                        $ageYears = $p->date_of_birth ? intval($p->date_of_birth->diffInYears(now())) : null;
                                    @endphp
                                    <small class="text-muted">{{ $ageYears !== null ? $ageYears.' years' : '-' }}</small>
                                </td>
                                <td class="col-location">{{ $p->barangay ? $p->barangay.',' : '' }} {{ $p->city }}, {{ $p->province }}</td>
                                <td class="col-natl">{{ $p->nationality }}</td>
                                <td class="col-actions">
                                    <button type="button" class="btn btn-primary btn-sm view-btn js-open-patient"><i class="fas fa-eye"></i> View</button>
                                    <button type="button" class="btn btn-info btn-sm request-btn" onclick="openLabRequestModal({{ $p->id }}, '{{ $p->first_name }} {{ $p->last_name }}', '{{ $p->patient_no }}')"><i class="fas fa-flask"></i> Request Lab</button>
                                    <button type="button" class="btn btn-warning btn-sm request-btn" onclick="openMedicineRequestModal({{ $p->id }}, '{{ $p->first_name }} {{ $p->last_name }}', '{{ $p->patient_no }}')"><i class="fas fa-pills"></i> Request Medicine</button>
                                    
                                    @php
                                        // Check if patient has active admission with paid billing
                                        $activeAdmission = $p->admissions()->where('status', 'active')->first();
                                        $canDischarge = false;
                                        if ($activeAdmission) {
                                            $paidBilling = $activeAdmission->billings()->where('status', 'paid')->first();
                                            $canDischarge = $paidBilling !== null;
                                        }
                                    @endphp
                                    
                                    @if($canDischarge)
                                        <button type="button" class="btn btn-success btn-sm discharge-btn" 
                                                onclick="dischargePatient({{ $activeAdmission->id }}, '{{ $p->first_name }} {{ $p->last_name }}', '{{ $activeAdmission->admission_number }}')"
                                                title="Discharge Patient (Billing Cleared)">
                                            <i class="fas fa-sign-out-alt"></i> Discharge
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>                
            @else
                <div class="alert alert-info">No patients found.</div>
            @endif
        </div>        
    </div>

    <div class="details-column">
        <div class="nurse-card details-card" id="detailsCard">
            

            <div class="details-empty" id="detailsEmpty">Select a patient to view details.</div>

            <div class="details-content" id="detailsContent" style="display:none;">
                <!-- Patient Details Section -->
                <div class="details-section">
                    <h4 class="section-header">Patient Details</h4>
                    <dl class="patient-details">
                        <dt>Patient No</dt><dd id="md-patient_no">-</dd>
                        <dt>Full Name</dt><dd id="md-name">-</dd>
                        <dt>Date of Birth</dt><dd id="md-dob">-</dd>
                        <dt>Age</dt><dd id="md-age">-</dd>
                        <dt>Sex</dt><dd id="md-sex">-</dd>
                        <dt>Contact Number</dt><dd id="md-contact_number">-</dd>
                        <dt>Location</dt><dd id="md-location">-</dd>
                        <dt>Nationality</dt><dd id="md-nationality">-</dd>
                    </dl>
                </div>
                
                <!-- Admission Summary Section -->
                <div class="details-section">
                    <h4 class="section-header">Admission Summary</h4>
                    <div id="md-admission-summary">
                        <div class="admission-loading">Loading admission history...</div>
                    </div>
                </div>
                
                <!-- General Health History Section -->
                <div class="details-section" id="health-history-section" style="display:none;">
                    <h4 class="section-header">General Health History</h4>
                    <div id="md-health-history">No health history information available</div>
                </div>

                <!-- Social History Section -->
                <div class="details-section" id="social-history-section" style="display:none;">
                    <h4 class="section-header">Social History</h4>
                    <div id="md-social-history">No social history information available</div>
                </div>

                <!-- Medicine Details Section -->
                <div class="details-section" id="medicine-section" style="display:none;">
                    <h4 class="section-header">Medicine Details</h4>
                    <div id="md-medicines">No medicines dispensed</div>
                </div>

                <!-- Lab Results Section -->
                <div class="details-section" id="lab-results-section" style="display:none;">
                    <h4 class="section-header">Lab Results</h4>
                    <div id="md-lab-results">No lab results available</div>
                </div>

                <div style="margin-top:12px;text-align:right;display:flex;gap:8px;justify-content:flex-end;">
                    <button id="btnEditPatient" class="btn secondary">Edit</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="pagination-wrapper">
    {{ $patients->links('components.custom-pagination') }}
</div>

@include('nurse.modals.lab_request_modal')
@include('nurse.modals.medicine_request_modal')
@include('nurse.modals.edit_patient_modal')
@include('nurse.modals.medicine_history_modal')
@include('nurse.modals.lab_results_modal')
@include('nurse.modals.new_admission_modal')

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // helper to read CSRF token from meta tag or hidden input
    const _csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value || '';
    const table = document.getElementById('patientsTable');
    const rows = table ? table.querySelectorAll('.patient-row') : [];
    const detailsCard = document.getElementById('detailsCard');
    const detailsEmpty = document.getElementById('detailsEmpty');
    const detailsContent = document.getElementById('detailsContent');

    function or(v){ return v===null||v===undefined||v==='' ? '-' : v; }

    // Helper function to format names with proper capitalization
    function formatName(name) {
        if (!name) return '-';
        return name.toLowerCase().replace(/\b\w/g, l => l.toUpperCase());
    }
    
    // Helper function to format dates without timezone
    function formatDate(dateStr) {
        if (!dateStr) return '-';
        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return '-';
            return date.toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long', 
                day: 'numeric'
            });
        } catch (e) {
            return dateStr.split('T')[0]; // fallback to just date part
        }
    }
    
    // Helper function to format datetime
    function formatDateTime(dateStr) {
        if (!dateStr) return '-';
        try {
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return '-';
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (e) {
            return dateStr.split('T')[0]; // fallback
        }
    }

    function renderPatient(patient){
        console.log('Rendering patient:', patient); // Debug log
        
        // Patient Details Section
        document.getElementById('md-patient_no').textContent = or(patient.patient_no);
        
        // Format name with proper capitalization
        const nameParts = [
            formatName(patient.last_name),
            formatName(patient.first_name), 
            formatName(patient.middle_name)
        ].filter(Boolean);
        document.getElementById('md-name').textContent = nameParts.length ? nameParts.join(', ') : '-';
        
        // Format date without timezone
        document.getElementById('md-dob').textContent = formatDate(patient.date_of_birth);
        
        // Compute age (years) from date_of_birth
        const dob = patient.date_of_birth ? new Date(patient.date_of_birth) : null;
        const now = new Date();
        let ageText = '-';
        if (dob && !isNaN(dob.getTime())) {
            let years = now.getFullYear() - dob.getFullYear();
            const m = now.getMonth() - dob.getMonth();
            const d = now.getDate() - dob.getDate();
            if (m < 0 || (m === 0 && d < 0)) years -= 1;
            ageText = years + ' years';
        }
        document.getElementById('md-age').textContent = ageText;
        
        // Format sex
        const sexText = patient.sex ? patient.sex.charAt(0).toUpperCase() + patient.sex.slice(1) : '-';
        document.getElementById('md-sex').textContent = sexText;
        
        // Format contact number
        document.getElementById('md-contact_number').textContent = or(patient.contact_number);
        
        // Format location
        const locationParts = [
            formatName(patient.barangay),
            formatName(patient.city),
            formatName(patient.province)
        ].filter(part => part && part !== '-');
        document.getElementById('md-location').textContent = locationParts.length ? locationParts.join(', ') : '-';
        
        document.getElementById('md-nationality').textContent = formatName(patient.nationality);
        
        // Render health history
        renderHealthHistory(patient);
        
        // Load admission summary (this will auto-load admission-specific medicine and lab data)
        loadAdmissionSummary(patient.id);
        
        // Note: Medicine and lab results are now loaded admission-specifically 
        // by loadAdmissionSummary -> loadAdmissionSpecificData
    }

    // Function to render health history
    function renderHealthHistory(patient) {
        const healthHistorySection = document.getElementById('health-history-section');
        const socialHistorySection = document.getElementById('social-history-section');
        const healthHistoryDiv = document.getElementById('md-health-history');
        const socialHistoryDiv = document.getElementById('md-social-history');
        
        // Render General Health History
        if (patient.general_health_history) {
            const healthHistory = patient.general_health_history;
            let healthHistoryHtml = '';
            
            // Medical Conditions
            if (healthHistory.medical_conditions) {
                const medConditions = healthHistory.medical_conditions;
                if (hasHealthData(medConditions)) {
                    healthHistoryHtml += '<div class="health-category"><h5>Medical Conditions</h5>';
                    if (medConditions.chronic_illnesses) {
                        healthHistoryHtml += `<div class="health-item"><strong>Chronic Illnesses:</strong> ${escapeHtml(medConditions.chronic_illnesses)}</div>`;
                    }
                    if (medConditions.hospitalization_history) {
                        healthHistoryHtml += `<div class="health-item"><strong>Hospitalization History:</strong> ${escapeHtml(medConditions.hospitalization_history)}</div>`;
                    }
                    if (medConditions.surgery_history) {
                        healthHistoryHtml += `<div class="health-item"><strong>Surgery History:</strong> ${escapeHtml(medConditions.surgery_history)}</div>`;
                    }
                    if (medConditions.accident_injury_history) {
                        healthHistoryHtml += `<div class="health-item"><strong>Accident/Injury History:</strong> ${escapeHtml(medConditions.accident_injury_history)}</div>`;
                    }
                    healthHistoryHtml += '</div>';
                }
            }
            
            // Medications
            if (healthHistory.medications) {
                const medications = healthHistory.medications;
                if (hasHealthData(medications)) {
                    healthHistoryHtml += '<div class="health-category"><h5>Medications</h5>';
                    if (medications.current_medications) {
                        healthHistoryHtml += `<div class="health-item"><strong>Current Medications:</strong> ${escapeHtml(medications.current_medications)}</div>`;
                    }
                    if (medications.long_term_medications) {
                        healthHistoryHtml += `<div class="health-item"><strong>Long-term Medications:</strong> ${escapeHtml(medications.long_term_medications)}</div>`;
                    }
                    healthHistoryHtml += '</div>';
                }
            }
            
            // Allergies
            if (healthHistory.allergies) {
                const allergies = healthHistory.allergies;
                if (hasHealthData(allergies)) {
                    healthHistoryHtml += '<div class="health-category"><h5>Allergies</h5>';
                    if (allergies.known_allergies) {
                        healthHistoryHtml += `<div class="health-item"><strong>Known Allergies:</strong> ${escapeHtml(allergies.known_allergies)}</div>`;
                    }
                    healthHistoryHtml += '</div>';
                }
            }
            
            // Family History
            if (healthHistory.family_history) {
                const familyHistory = healthHistory.family_history;
                if (hasHealthData(familyHistory)) {
                    healthHistoryHtml += '<div class="health-category"><h5>Family History</h5>';
                    if (familyHistory.family_history_chronic) {
                        healthHistoryHtml += `<div class="health-item"><strong>Family History of Chronic Diseases:</strong> ${escapeHtml(familyHistory.family_history_chronic)}</div>`;
                    }
                    healthHistoryHtml += '</div>';
                }
            }
            
            if (healthHistoryHtml) {
                healthHistoryDiv.innerHTML = healthHistoryHtml;
                healthHistorySection.style.display = 'block';
            } else {
                healthHistoryDiv.innerHTML = 'No health history information available';
                healthHistorySection.style.display = 'block';
            }
        } else {
            healthHistoryDiv.innerHTML = 'No health history information available';
            healthHistorySection.style.display = 'block';
        }
        
        // Render Social History
        if (patient.social_history) {
            const socialHistory = patient.social_history;
            let socialHistoryHtml = '';
            
            // Lifestyle Habits
            if (socialHistory.lifestyle_habits) {
                const lifestyleHabits = socialHistory.lifestyle_habits;
                if (hasHealthData(lifestyleHabits)) {
                    socialHistoryHtml += '<div class="health-category"><h5>Lifestyle Habits</h5>';
                    if (lifestyleHabits.smoking_history) {
                        socialHistoryHtml += `<div class="health-item"><strong>Smoking History:</strong> ${escapeHtml(lifestyleHabits.smoking_history)}</div>`;
                    }
                    if (lifestyleHabits.alcohol_consumption) {
                        socialHistoryHtml += `<div class="health-item"><strong>Alcohol Consumption:</strong> ${escapeHtml(lifestyleHabits.alcohol_consumption)}</div>`;
                    }
                    if (lifestyleHabits.recreational_drugs) {
                        socialHistoryHtml += `<div class="health-item"><strong>Recreational Drugs:</strong> ${escapeHtml(lifestyleHabits.recreational_drugs)}</div>`;
                    }
                    if (lifestyleHabits.exercise_activity) {
                        socialHistoryHtml += `<div class="health-item"><strong>Exercise/Physical Activity:</strong> ${escapeHtml(lifestyleHabits.exercise_activity)}</div>`;
                    }
                    socialHistoryHtml += '</div>';
                }
            }
            
            if (socialHistoryHtml) {
                socialHistoryDiv.innerHTML = socialHistoryHtml;
                socialHistorySection.style.display = 'block';
            } else {
                socialHistoryDiv.innerHTML = 'No social history information available';
                socialHistorySection.style.display = 'block';
            }
        } else {
            socialHistoryDiv.innerHTML = 'No social history information available';
            socialHistorySection.style.display = 'block';
        }
    }
    
    // Helper function to check if health data object has any non-empty values
    function hasHealthData(obj) {
        if (!obj) return false;
        return Object.values(obj).some(value => value && value.trim() !== '');
    }
    
    // Helper function to escape HTML
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Function to load and display patient medicines
    function loadPatientMedicines(patientId, patient, admissionId = null) {
        const medicineSection = document.getElementById('medicine-section');
        const medicinesDiv = document.getElementById('md-medicines');
        
        if (!patientId) {
            medicineSection.style.display = 'none';
            return;
        }
        
        console.log('Loading medicines for patient ID:', patientId, 'admission ID:', admissionId);
        
        // Show loading state
        medicinesDiv.innerHTML = '<div class="loading-medicines">Loading medicines...</div>';
        medicineSection.style.display = 'block';
        
        // Build API URL with admission filter if provided
        const apiUrl = admissionId ? 
            `/api/patients/${patientId}/medicines?admission_id=${admissionId}` : 
            `/api/patients/${patientId}/medicines`;
        
        // Fetch patient medicines via API
        fetch(apiUrl)
            .then(response => {
                console.log('API Response status:', response.status);
                console.log('API Response headers:', response.headers);
                console.log('API Response ok:', response.ok);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response body:', text);
                        throw new Error(`HTTP ${response.status}: ${response.statusText} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Medicines loaded:', data);
                console.log('Admission ID filter:', admissionId);
                console.log('API URL used:', apiUrl);
                console.log('Number of medicines returned:', data.medicines ? data.medicines.length : 0);
                
                if (data.success && data.medicines && data.medicines.length > 0) {
                    // Only show "View Medicine Summary" button, no individual medicine display
                    const patientName = `${patient.first_name || ''} ${patient.last_name || ''}`.trim();
                    const patientNo = patient.patient_no || '';
                    const buttonText = admissionId ? 
                        `View Medicines for This Admission (${data.medicines.length} total)` : 
                        `View Medicine Summary (${data.medicines.length} total)`;
                    medicinesDiv.innerHTML = `
                        <div class="view-more-medicines">
                            <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn" onclick="openMedicineHistoryModal(${patientId}, '${patientName}', '${patientNo}')">
                                <i class="fas fa-pills"></i> 
                                ${buttonText}
                            </button>
                        </div>
                    `;
                    
                    medicineSection.style.display = 'block';
                } else {
                    // No medicines found
                    const noMedicineText = admissionId ? 
                        'No medicines dispensed for this admission yet' : 
                        'No medicines dispensed yet';
                    medicinesDiv.innerHTML = `<div class="no-medicines">${noMedicineText}</div>`;
                    medicineSection.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading patient medicines:', error);
                console.error('Error details:', error.message);
                console.error('Error stack:', error.stack);
                medicinesDiv.innerHTML = `<div class="error-medicines">Failed to load medicines: ${error.message}</div>`;
                medicineSection.style.display = 'block';
            });
    }
    
    // Function to load and display patient lab results
    function loadPatientLabResults(patientId, admissionId = null) {
        const labResultsSection = document.getElementById('lab-results-section');
        const labResultsDiv = document.getElementById('md-lab-results');
        
        if (!patientId) {
            labResultsSection.style.display = 'none';
            return;
        }
        
        console.log('Loading lab results for patient ID:', patientId, 'admission ID:', admissionId);
        
        // Show loading state
        labResultsDiv.innerHTML = '<div class="loading-lab-results">Loading lab results...</div>';
        labResultsSection.style.display = 'block';
        
        // Build API URL with admission filter if provided
        const apiUrl = admissionId ? 
            `/api/patients/${patientId}/lab-results?admission_id=${admissionId}` : 
            `/api/patients/${patientId}/lab-results`;
        
        // Fetch patient lab results via API
        fetch(apiUrl)
            .then(response => {
                console.log('Lab Results API Response status:', response.status);
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response body:', text);
                        throw new Error(`HTTP ${response.status}: ${response.statusText} - ${text}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                console.log('Lab results loaded:', data);
                console.log('Admission ID filter:', admissionId);
                console.log('API URL used:', apiUrl);
                console.log('Number of lab tests returned:', data.tests ? data.tests.length : 0);
                
                if (data.success && data.tests && data.tests.length > 0) {
                    // Only show "View Lab Results Summary" button, no individual lab result display
                    const buttonText = admissionId ? 
                        `View Lab Results for This Admission (${data.tests.length} total)` : 
                        `View Lab Results Summary (${data.tests.length} total)`;
                    labResultsDiv.innerHTML = `
                        <div class="view-more-medicines">
                            <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn" onclick="openLabResultsModal(${patientId})">
                                <i class="fas fa-flask"></i> 
                                ${buttonText}
                            </button>
                        </div>
                    `;
                    
                    labResultsSection.style.display = 'block';
                } else {
                    // No lab results found
                    const noLabText = admissionId ? 
                        'No lab results available for this admission yet' : 
                        'No lab results available yet';
                    labResultsDiv.innerHTML = `<div class="no-lab-results">${noLabText}</div>`;
                    labResultsSection.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading patient lab results:', error);
                labResultsDiv.innerHTML = `<div class="error-lab-results">Failed to load lab results: ${error.message}</div>`;
                labResultsSection.style.display = 'block';
            });
    }
    
    // Function to view lab result PDF
    function viewLabResultPdf(labOrderId) {
        // Open the PDF in a new window/tab using nurse-accessible route
        window.open(`/nurse/lab-orders/${labOrderId}/view-pdf`, '_blank');
    }
    
    // Make viewLabResultPdf available globally
    window.viewLabResultPdf = viewLabResultPdf;

    // Function to load admission summary (multiple admissions)
    function loadAdmissionSummary(patientId) {
        const summaryDiv = document.getElementById('md-admission-summary');
        if (!summaryDiv) return;
        
        // Fetch patient admissions via API
        fetch(`/api/patients/${patientId}/admissions`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Admissions loaded:', data);
                
                if (data.success && data.admissions && data.admissions.length > 0) {
                    let admissionsHtml = '';
                    
                    data.admissions.forEach((admission, index) => {
                        const isActive = admission.status === 'active';
                        const statusBadge = isActive ? 
                            '<span class="badge badge-success">Active</span>' : 
                            '<span class="badge badge-secondary">Discharged</span>';
                        
                        admissionsHtml += `
                            <div class="admission-item ${isActive ? 'active-admission' : ''} clickable-admission" 
                                 data-admission-id="${admission.id}" 
                                 onclick="loadAdmissionSpecificData(${admission.id}, ${patientId})"
                                 style="cursor: pointer;">
                                <div class="admission-header">
                                    <span class="admission-title">Admission #${admission.admission_number}</span>
                                    ${statusBadge}
                                </div>
                                <div class="admission-details">
                                    <div class="admission-info">
                                        <strong>Room:</strong> ${admission.room_no || 'N/A'} | 
                                        <strong>Type:</strong> ${admission.admission_type || 'N/A'} | 
                                        <strong>Doctor:</strong> ${admission.doctor_name || 'N/A'}
                                    </div>
                                    <div class="admission-diagnosis">
                                        <strong>Diagnosis:</strong> ${admission.admission_diagnosis || 'N/A'}
                                        ${admission.final_diagnosis ? `<br><strong>Final Diagnosis:</strong> <span style="color: #28a745; font-weight: 600;">${admission.final_diagnosis}</span>` : ''}
                                    </div>
                                    <div class="admission-dates">
                                        <strong>Admitted:</strong> ${formatDateTime(admission.admission_date)} 
                                        ${admission.discharge_date ? `| <strong>Discharged:</strong> ${formatDateTime(admission.discharge_date)}` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    // Check if patient has any active admissions
                    const hasActiveAdmission = data.admissions.some(admission => admission.status === 'active');
                    
                    // Add "New Admission" button if no active admissions
                    const newAdmissionButton = !hasActiveAdmission ? `
                        <div class="new-admission-section">
                            <button type="button" class="btn btn-success btn-sm new-admission-btn" onclick="openNewAdmissionModal(${patientId})">
                                <i class="fas fa-plus"></i> New Admission
                            </button>
                        </div>
                    ` : '';
                    
                    summaryDiv.innerHTML = `
                        <div class="admission-count">Total Admissions: ${data.admissions.length}</div>
                        ${newAdmissionButton}
                        <div class="admissions-list">${admissionsHtml}</div>
                    `;
                    
                    // Auto-load data for the active admission if available
                    const activeAdmission = data.admissions.find(admission => admission.status === 'active');
                    if (activeAdmission) {
                        setTimeout(() => {
                            loadAdmissionSpecificData(activeAdmission.id, patientId);
                        }, 100);
                    } else if (data.admissions.length > 0) {
                        // If no active admission, load the most recent one
                        setTimeout(() => {
                            loadAdmissionSpecificData(data.admissions[0].id, patientId);
                        }, 100);
                    }
                } else {
                    // No admissions found - show new admission button
                    summaryDiv.innerHTML = `
                        <div class="no-admissions">No admission records found</div>
                        <div class="new-admission-section">
                            <button type="button" class="btn btn-success btn-sm new-admission-btn" onclick="openNewAdmissionModal(${patientId})">
                                <i class="fas fa-plus"></i> Create First Admission
                            </button>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading patient admissions:', error);
                summaryDiv.innerHTML = `<div class="error-admissions">Failed to load admission history: ${error.message}</div>`;
            });
    }

    // Make function globally accessible
    window.loadAdmissionSummary = loadAdmissionSummary;

    // Function to load admission-specific medicine and lab data
    function loadAdmissionSpecificData(admissionId, patientId) {
        console.log(`Loading data for admission ${admissionId}, patient ${patientId}`);
        
        // Highlight selected admission
        document.querySelectorAll('.admission-item').forEach(item => {
            item.classList.remove('selected-admission');
        });
        document.querySelector(`[data-admission-id="${admissionId}"]`).classList.add('selected-admission');
        
        // Get patient object from global scope if available
        const patientObj = window.currentPatient || null;
        
        // Load admission-specific medicines
        loadPatientMedicines(patientId, patientObj, admissionId);
        
        // Load admission-specific lab results
        loadPatientLabResults(patientId, admissionId);
    }

    // Function to reset medicine and lab sections for new admissions
    function resetMedicineAndLabSectionsForNewAdmission() {
        // Reset medicine section
        const medicineSection = document.getElementById('medicine-section');
        const medicinesDiv = document.getElementById('md-medicines');
        if (medicineSection && medicinesDiv) {
            medicinesDiv.innerHTML = '<div class="no-medicines">No medicines dispensed for current admission yet</div>';
            medicineSection.style.display = 'block';
        }
        
        // Reset lab results section
        const labResultsSection = document.getElementById('lab-results-section');
        const labResultsDiv = document.getElementById('md-lab-results');
        if (labResultsSection && labResultsDiv) {
            labResultsDiv.innerHTML = '<div class="no-lab-results">No lab results available for current admission yet</div>';
            labResultsSection.style.display = 'block';
        }
    }

    // Make functions globally accessible
    window.loadAdmissionSpecificData = loadAdmissionSpecificData;
    window.resetMedicineAndLabSectionsForNewAdmission = resetMedicineAndLabSectionsForNewAdmission;

    function clearActive(){
        rows.forEach(r => r.classList.remove('active'));
    }

    rows.forEach(row => {
        const btn = row.querySelector('.js-open-patient');
        if (btn) {
            btn.addEventListener('click', function(){
                try {
                    const payload = row.getAttribute('data-patient');
                    console.log('Raw patient data:', payload); // Debug log
                    console.log('Payload length:', payload ? payload.length : 0);
                    console.log('First 100 chars:', payload ? payload.substring(0, 100) : 'null');
                    
                    if (!payload) {
                        throw new Error('No patient data found');
                    }
                    
                    if (payload.trim() === '') {
                        throw new Error('Empty patient data');
                    }
                    
                    const patient = JSON.parse(payload);
                    console.log('Parsed patient:', patient); // Debug log
                    
                    clearActive();
                    row.classList.add('active');
                    detailsEmpty.style.display = 'none';
                    detailsContent.style.display = 'block';
                    
                    // Store patient globally for admission-specific data loading
                    window.currentPatient = patient;
                    
                    renderPatient(patient);
                    // update "Open Full" link to go to patient page if route exists
                    const btnFull = document.getElementById('detailsViewFull');
                    if (btnFull) {
                        // now handled via edit button/modal
                        btnFull.href = `#`;
                    }
                } catch (error) {
                    console.error('Error parsing patient data:', error);
                    console.log('Failed data attribute:', row.getAttribute('data-patient'));
                    nurseError('Data Error', 'Failed to load patient details: ' + error.message);
                }
            });
        }
    });

    // optionally auto-select first row
    if (rows.length && !document.querySelector('.patient-row.active')) {
        rows[0].querySelector('.js-open-patient').click();
    }

    // Function to fetch current admission data for edit modal
    function fetchCurrentAdmissionForEdit(patientId) {
        // Clear admission fields first
        safeSetValue('edit_room_no', '');
        safeSetValue('edit_admission_type', '');
        safeSetValue('edit_doctor_type', '');
        safeSetValue('edit_admission_diagnosis', '');
        safeSetValue('edit_doctor_input', '');
        safeSetValue('edit_doctor_name', '');
        
        const diagDescField = document.getElementById('edit_admission_diagnosis_description');
        if (diagDescField) {
            diagDescField.value = 'Loading admission data...';
        }
        
        // Fetch current admission data
        fetch(`/patients/${patientId}/current-admission`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch admission data');
                }
                return response.json();
            })
            .then(data => {
                console.log('Admission API response:', data);
                if (data.success && data.admission) {
                    const admission = data.admission;
                    console.log('Admission data received:', admission);
                    
                    // Initialize doctor field first to avoid clearing values later
                    if (typeof window.initializeDoctorField === 'function') {
                        console.log('Initializing doctor field...');
                        window.initializeDoctorField();
                    }
                    
                    // Populate admission fields
                    safeSetValue('edit_room_no', admission.room_no);
                    safeSetValue('edit_admission_type', admission.admission_type);
                    safeSetValue('edit_doctor_type', admission.doctor_type);
                    safeSetValue('edit_admission_diagnosis', admission.admission_diagnosis);
                    
                    // Set doctor fields after initialization
                    console.log('Setting doctor fields with value:', admission.doctor_name);
                    const doctorInputResult = safeSetValue('edit_doctor_input', admission.doctor_name);
                    const doctorNameResult = safeSetValue('edit_doctor_name', admission.doctor_name);
                    console.log('Doctor field results - Input:', doctorInputResult, 'Hidden:', doctorNameResult);
                    
                    // Fetch and populate diagnosis description
                    if (admission.admission_diagnosis && diagDescField) {
                        fetch('/icd10/search?q=' + encodeURIComponent(admission.admission_diagnosis))
                            .then(async r => {
                                const ct = (r.headers.get('content-type') || '').toLowerCase();
                                const text = await r.text();
                                
                                if (ct.includes('application/json')) {
                                    try {
                                        const icdData = JSON.parse(text);
                                        if (Array.isArray(icdData) && icdData.length > 0) {
                                            const match = icdData.find(item => 
                                                item.code && item.code.toLowerCase() === admission.admission_diagnosis.toLowerCase()
                                            ) || icdData[0];
                                            
                                            diagDescField.value = match.description || 'Description not found';
                                        } else {
                                            diagDescField.value = 'Description not found';
                                        }
                                    } catch (e) {
                                        console.error('Failed to parse ICD-10 description', e);
                                        diagDescField.value = 'Error loading description';
                                    }
                                } else {
                                    diagDescField.value = 'Error loading description';
                                }
                            })
                            .catch(e => {
                                console.error('ICD-10 description fetch error', e);
                                diagDescField.value = 'Error loading description';
                            });
                    } else if (diagDescField) {
                        diagDescField.value = '';
                    }
                } else {
                    // No active admission found
                    console.log('No active admission found for patient. Response:', data);
                    if (diagDescField) {
                        diagDescField.value = 'No active admission found';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching admission data:', error);
                if (diagDescField) {
                    diagDescField.value = 'Error loading admission data';
                }
            });
    }

    // --- Edit modal wiring ---
    const btnEdit = document.getElementById('btnEditPatient');
    const modal = document.getElementById('editModal');
    
    // Check if modal exists before proceeding
    if (!modal) {
        console.error('Edit modal not found');
        return;
    }

    // Helper function to safely set element value
    function safeSetValue(elementId, value) {
        const element = document.getElementById(elementId);
        if (element) {
            if (element.tagName === 'SELECT') {
                // For select elements, find the option with matching value
                const options = element.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].value === value) {
                        element.selectedIndex = i;
                        return true;
                    }
                }
                // If no match found, set to first option (usually empty/default)
                element.selectedIndex = 0;
            } else {
                // For input elements
                element.value = value || '';
            }
            return true;
        } else {
            console.warn(`Element with id '${elementId}' not found`);
            return false;
        }
    }

    function openEditModal(patient){
        try {
            if (!patient) {
                throw new Error('Patient data is null or undefined');
            }
            
            console.log('Opening edit modal for patient:', patient);
            console.log('Doctor name from patient data:', patient.doctor_name);
            
            const form = document.getElementById('editPatientForm');
            if (!form) {
                throw new Error('Edit patient form not found');
            }
            
            form.patient_no = patient.patient_no;
        
            // Use safeSetValue for modal fields with edit_ prefix
            safeSetValue('edit_first_name', patient.first_name);
            safeSetValue('edit_last_name', patient.last_name);
            safeSetValue('edit_middle_name', patient.middle_name);
            
            // Handle date_of_birth - it may come as ISO string or date object
            let dobValue = '';
            if (patient.date_of_birth) {
                try {
                    // If it's already in YYYY-MM-DD format, use it directly
                    if (patient.date_of_birth.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        dobValue = patient.date_of_birth;
                    } else {
                        // If it's an ISO string, parse it and format for input[type="date"]
                        const dobDate = new Date(patient.date_of_birth);
                        if (!isNaN(dobDate.getTime())) {
                            dobValue = dobDate.toISOString().split('T')[0];
                        }
                    }
                } catch (e) {
                    console.warn('Error parsing date_of_birth:', e);
                    dobValue = '';
                }
            }
            safeSetValue('edit_date_of_birth', dobValue);
            
            console.log('Setting sex value:', patient.sex);
            console.log('Setting contact_number value:', patient.contact_number);
            safeSetValue('edit_sex', patient.sex);
            safeSetValue('edit_contact_number', patient.contact_number);
            safeSetValue('edit_province', patient.province);
            safeSetValue('edit_city', patient.city);
            safeSetValue('edit_barangay', patient.barangay);
            safeSetValue('edit_nationality', patient.nationality);
            
            // Fetch current active admission data instead of using patient data
            fetchCurrentAdmissionForEdit(patient.id);
            
            // Open the modal
            modal.classList.add('open');
            modal.classList.add('show');
        } catch (error) {
            console.error('Error in openEditModal:', error);
            nurseError('Modal Error', 'Failed to open edit modal: ' + error.message);
        }
    }

    function closeModal(){ 
        if (modal) {
            modal.classList.remove('open'); 
            modal.classList.remove('show'); 
        }
    }
    if (modal) {
        modal.querySelectorAll('.modal-close').forEach(b => b.addEventListener('click', closeModal));
    }

    // Save button sends PUT to update with validation
    const saveBtn = document.getElementById('savePatientBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function(e){
        e.preventDefault(); 
        const form = document.getElementById('editPatientForm');
        const patient_no = form.patient_no;
        if(!patient_no) { nurseError('Selection Error', 'No patient selected'); return; }
        
        // Get input values for validation
        const roomInput = document.getElementById('edit_room_no');
        const icdInput = document.getElementById('edit_admission_diagnosis');
        const roomValue = roomInput?.value.trim() || '';
        const icdValue = icdInput?.value.trim() || '';
        
        let validationErrors = [];
        let validationPromises = [];
        
        // Validate room if not empty
        if (roomValue) {
            const roomValidation = fetch('/rooms/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: roomValue })
            })
            .then(async r => {
                const result = await r.json();
                if (!result.valid) {
                    validationErrors.push('Please select a valid room from the list.');
                }
            })
            .catch(e => {
                console.error('Room validation error', e);
                validationErrors.push('Unable to validate room. Please try again.');
            });
            
            validationPromises.push(roomValidation);
        }
        
        // Validate ICD if not empty
        if (icdValue) {
            const icdValidation = fetch('/icd10/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ code: icdValue })
            })
            .then(async r => {
                const result = await r.json();
                if (!result.valid) {
                    validationErrors.push('Please select a valid ICD-10 code from the list.');
                }
            })
            .catch(e => {
                console.error('ICD validation error', e);
                validationErrors.push('Unable to validate ICD-10 code. Please try again.');
            });
            
            validationPromises.push(icdValidation);
        }
        
        // Wait for all validations to complete
        await Promise.all(validationPromises);
        
        // If there are validation errors, show them and stop submission
        if (validationErrors.length > 0) {
            const errorMessage = validationErrors.join(' ');
            nurseError('Validation Error', errorMessage);
            return;
        }
        
        // If validation passes, proceed with form submission
        const data = new FormData(form);
        const token = _csrf();
        // PHP doesn't parse multipart/form-data for PUT; use POST with _method override so Laravel receives fields
        data.set('_token', token);
        data.set('_method', 'PUT');
        fetch(`/nurse/patients/${encodeURIComponent(patient_no)}`, {
            method: 'POST',
            credentials: 'same-origin', // include session cookie so VerifyCsrfToken can validate
            headers: {
                'Accept': 'application/json'
            },
            body: data
        }).then(async r=>{
            const text = await r.text();
            const ct = r.headers.get('content-type') || '';
            if(!r.ok){
                if(r.status === 419) throw new Error('Session expired (419). Please refresh and login again.');
                // show server returned HTML or error
                const snippet = text ? text.slice(0,300) : `HTTP ${r.status}`;
                throw new Error('Update failed: ' + snippet);
            }
            if(ct.includes('application/json')){
                const j = JSON.parse(text);
                if(j.ok){ 
                    nurseSuccess('Patient Updated', j.message || 'Patient information updated successfully!');
                    setTimeout(() => location.reload(), 1500);
                    return; 
                }
                throw new Error('Update failed: ' + (j.message || JSON.stringify(j)));
            }
            // non-json but ok response
            nurseSuccess('Patient Updated', 'Patient information updated successfully!');
            setTimeout(() => location.reload(), 1500);
        }).catch(e=>{ console.error(e); nurseError('Update Failed', e.message); });
        });
    }

    // wire edit button to open modal with currently selected patient
    if (btnEdit) {
        btnEdit.addEventListener('click', function(){
            const patientNo = document.getElementById('md-patient_no').textContent;
            if(!patientNo || patientNo === '-') { nurseError('Selection Error', 'No patient selected'); return; }
            // find the row with that patient_no
            const row = Array.from(rows).find(r => r.querySelector('.col-no')?.textContent.trim() === patientNo.toString());
            if(!row) { nurseError('Search Error', 'Patient not found'); return; }
            try{ 
                const patient = JSON.parse(row.getAttribute('data-patient')); 
                console.log('Patient data:', patient); // Debug log
                openEditModal(patient); 
            } catch(e) { 
                console.error('JSON parse error:', e); 
                console.log('Raw data:', row.getAttribute('data-patient')); 
                nurseError('Modal Error', 'Failed to open edit modal: ' + e.message); 
            }
        });
    }
});

// Lab Request Modal Functions
function openLabRequestModal(patientId, patientName, patientNo) {
    document.getElementById('requestPatientId').value = patientId;
    document.getElementById('requestPatientInfo').textContent = `${patientName} (ID: ${patientNo})`;
    
    // Get the active admission ID for this patient
    fetch(`/api/patients/${patientId}/active-admission`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.admission) {
                document.getElementById('requestAdmissionId').value = data.admission.id;
                
                // Show admission info
                const admissionInfo = `Admission #${data.admission.admission_number} - Room ${data.admission.room_no || 'N/A'} - Dr. ${data.admission.doctor_name || 'N/A'}`;
                document.getElementById('requestAdmissionInfo').textContent = admissionInfo;
                document.getElementById('admissionInfoGroup').style.display = 'block';
            } else {
                // No active admission - show error or warning
                nurseError('No Active Admission', 'This patient has no active admission. Please create an admission first before requesting lab tests.');
                return;
            }
        })
        .catch(error => {
            console.error('Error fetching active admission:', error);
            nurseError('Error', 'Unable to verify patient admission status. Please try again.');
            return;
        });
    
    const modal = document.getElementById('labRequestModal');
    modal.classList.add('show');
    // Reset form
    document.getElementById('labRequestForm').reset();
    document.getElementById('requestPatientId').value = patientId; // Reset this after form reset
    
    // Reset price display
    if (document.getElementById('testPrice')) {
        document.getElementById('testPrice').textContent = '0.00';
        document.getElementById('totalPrice').textContent = '0.00';
        document.getElementById('testPriceValue').value = '0';
    }
    
    // Ensure additional tests textarea is disabled by default
    const additionalTestsCheckbox = document.getElementById('enableAdditionalTests');
    const additionalTestsTextarea = document.getElementById('additionalTests');
    if (additionalTestsCheckbox && additionalTestsTextarea) {
        additionalTestsCheckbox.checked = false;
        additionalTestsTextarea.disabled = true;
    }
    
    // Reset the specific test dropdown
    if (typeof updateTestOptions === 'function') {
        updateTestOptions();
    }
}

function closeLabRequestModal() {
    const modal = document.getElementById('labRequestModal');
    modal.classList.remove('show');
    document.getElementById('labRequestForm').reset();
}

// Update test options based on selected category
function updateTestOptions() {
    const category = document.getElementById('testCategory').value;
    const specificTest = document.getElementById('specificTest');
    
    // Clear existing options
    specificTest.innerHTML = '<option value="">Select specific test</option>';
    
    if (!category) {
        specificTest.disabled = true;
        return;
    }
    
    // Show loading option and disable dropdown temporarily
    specificTest.innerHTML = '<option value="">Loading procedures...</option>';
    specificTest.disabled = true;
    
    // Fetch procedures from database
    fetch(`/procedures/category?category=${category}`)
        .then(response => response.json())
        .then(data => {
            specificTest.innerHTML = '<option value="">Select specific test</option>';
            
            if (data.error) {
                console.error('Error fetching procedures:', data.error);
                specificTest.innerHTML = '<option value="">Error loading procedures</option>';
                specificTest.disabled = true;
                return;
            }
            
            data.forEach(procedure => {
                const option = document.createElement('option');
                option.value = procedure.name;
                option.textContent = procedure.name;
                specificTest.appendChild(option);
            });
            
            // Enable the dropdown after successfully loading procedures
            specificTest.disabled = false;
        })
        .catch(error => {
            console.error('Error fetching procedures:', error);
            specificTest.innerHTML = '<option value="">Error loading procedures</option>';
            specificTest.disabled = true;
        });
}

// Lab Request Form Submission
document.getElementById('labRequestForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Properly separate test request and additional notes
    const category = formData.get('test_category');
    const specificTest = formData.get('specific_test');
    const additionalTests = formData.get('additional_tests');
    
    // Main test request (keep clean for pricing lookup)
    let testRequested = '';
    if (category && specificTest) {
        testRequested = `${category.toUpperCase()}: ${specificTest}`;
    }
    
    // Put additional tests in notes field, not test_requested
    let notes = formData.get('notes') || '';
    if (additionalTests) {
        if (notes) {
            notes += `\n\nAdditional Tests: ${additionalTests}`;
        } else {
            notes = `Additional Tests: ${additionalTests}`;
        }
    }
    
    // Update FormData with proper separation
    formData.set('test_requested', testRequested);
    formData.set('notes', notes);
    
    const submitBtn = this.querySelector('.submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    fetch('/lab-orders', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            nurseSuccess('Request Submitted', 'Lab test request submitted successfully!');
            closeLabRequestModal();
        } else {
            nurseError('Request Failed', 'Error submitting request. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting request. Please try again.');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
});

// Handle additional tests checkbox
document.getElementById('enableAdditionalTests').addEventListener('change', function() {
    const additionalTestsTextarea = document.getElementById('additionalTests');
    additionalTestsTextarea.disabled = !this.checked;
    if (this.checked) {
        additionalTestsTextarea.focus();
    } else {
        additionalTestsTextarea.value = '';
    }
});

// New Admission Modal Functions
function openNewAdmissionModal(patientId) {
    // Get current patient data from the selected row
    const activeRow = document.querySelector('.patient-row.active');
    if (!activeRow) {
        nurseError('Selection Error', 'Please select a patient first');
        return;
    }
    
    try {
        const patient = JSON.parse(activeRow.getAttribute('data-patient'));
        
        // Populate patient info
        document.getElementById('admission_patient_id').value = patientId;
        
        const patientInfoDiv = document.getElementById('admissionPatientInfo');
        patientInfoDiv.innerHTML = `
            <div class="info-item">
                <span class="info-label">Patient No:</span> ${patient.patient_no}
            </div>
            <div class="info-item">
                <span class="info-label">Name:</span> ${patient.first_name} ${patient.last_name}
            </div>
            <div class="info-item">
                <span class="info-label">Age:</span> ${calculateAge(patient.date_of_birth)} years
            </div>
            <div class="info-item">
                <span class="info-label">Sex:</span> ${patient.sex || 'N/A'}
            </div>
        `;
        
        // Reset form
        document.getElementById('newAdmissionForm').reset();
        document.getElementById('admission_patient_id').value = patientId; // Reset this after form reset
        
        // Initialize suggestion dropdowns
        if (typeof initializeAdmissionSuggestions === 'function') {
            initializeAdmissionSuggestions();
        }
        
        // Show modal
        const modal = document.getElementById('newAdmissionModal');
        modal.classList.add('show');
        
    } catch (error) {
        console.error('Error opening new admission modal:', error);
        nurseError('Modal Error', 'Failed to open new admission modal: ' + error.message);
    }
}

function closeNewAdmissionModal() {
    const modal = document.getElementById('newAdmissionModal');
    modal.classList.remove('show');
    document.getElementById('newAdmissionForm').reset();
}

// Calculate age helper function
function calculateAge(dateOfBirth) {
    if (!dateOfBirth) return 0;
    const dob = new Date(dateOfBirth);
    const now = new Date();
    let years = now.getFullYear() - dob.getFullYear();
    const m = now.getMonth() - dob.getMonth();
    const d = now.getDate() - dob.getDate();
    if (m < 0 || (m === 0 && d < 0)) years -= 1;
    return years;
}

// Initialize suggestion dropdowns for admission form
function initializeAdmissionSuggestions() {
    // Room suggestions
    const roomInput = document.getElementById('admission_room_no');
    if (roomInput && typeof initializeSuggestionDropdown === 'function') {
        initializeSuggestionDropdown(roomInput, 'roomSuggestions', '/rooms/search');
    }
    
    // Doctor suggestions  
    const doctorInput = document.getElementById('admission_doctor_name');
    if (doctorInput && typeof initializeSuggestionDropdown === 'function') {
        initializeSuggestionDropdown(doctorInput, 'doctorSuggestions', '/doctors/search');
    }
    
    // ICD-10 suggestions
    const icdInput = document.getElementById('admission_admission_diagnosis');
    const icdDescInput = document.getElementById('admission_diagnosis_description');
    if (icdInput && typeof initializeSuggestionDropdown === 'function') {
        initializeSuggestionDropdown(icdInput, 'icdSuggestions', '/icd10/search', function(selectedItem) {
            // Update description when ICD code is selected
            if (icdDescInput && selectedItem.description) {
                icdDescInput.value = selectedItem.description;
            }
        });
    }
}

// Make functions available globally
window.openNewAdmissionModal = openNewAdmissionModal;
window.closeNewAdmissionModal = closeNewAdmissionModal;
window.initializeAdmissionSuggestions = initializeAdmissionSuggestions;

// New Admission Form Submission
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking close buttons
    const modal = document.getElementById('newAdmissionModal');
    if (modal) {
        modal.querySelectorAll('.modal-close').forEach(btn => {
            btn.addEventListener('click', closeNewAdmissionModal);
        });
    }
    
    // Save button handler
    const saveBtn = document.getElementById('saveAdmissionBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function(e) {
            e.preventDefault();
            
            const form = document.getElementById('newAdmissionForm');
            const patientId = document.getElementById('admission_patient_id').value;
            
            if (!patientId) {
                nurseError('Selection Error', 'No patient selected');
                return;
            }
            
            // Get input values for validation
            const roomInput = document.getElementById('new_admission_room_no');
            const doctorInput = document.getElementById('new_admission_doctor_input');
            const icdInput = document.getElementById('new_admission_admission_diagnosis');
            const roomValue = roomInput?.value.trim() || '';
            const doctorValue = doctorInput?.value.trim() || '';
            const icdValue = icdInput?.value.trim() || '';
            
            // Check basic validation
            if (!roomValue) {
                nurseError('Validation Error', 'Room is required.');
                return;
            }
            
            if (!doctorValue) {
                nurseError('Validation Error', 'Doctor is required.');
                return;
            }
            
            // Check validation functions if available (from the modal's suggestion system)
            if (roomValue && typeof window.validateNewAdmissionRoom === 'function' && !window.validateNewAdmissionRoom()) {
                nurseError('Validation Error', 'Please select a valid room from the list.');
                return;
            }
            
            if (icdValue && typeof window.validateNewAdmissionIcd === 'function' && !window.validateNewAdmissionIcd()) {
                nurseError('Validation Error', 'Please select a valid ICD-10 code from the list.');
                return;
            }
            
            const originalText = saveBtn.textContent;
            saveBtn.textContent = 'Creating...';
            saveBtn.disabled = true;
            
            // If validation passes, proceed with form submission
            const data = new FormData(form);
            
            // Debug: Log the form data being sent
            console.log('Form data being sent:');
            for (let [key, value] of data.entries()) {
                console.log(key + ':', value);
            }
            
            fetch('/nurse/admissions', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: data
            })
            .then(async response => {
                const contentType = response.headers.get('content-type');
                
                if (!contentType || !contentType.includes('application/json')) {
                    // Response is not JSON, possibly HTML error page
                    const text = await response.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response');
                }
                
                const jsonData = await response.json();
                
                if (!response.ok) {
                    // HTTP error status but JSON response (validation errors, etc.)
                    throw new Error(jsonData.message || `HTTP ${response.status}: ${response.statusText}`);
                }
                
                return jsonData;
            })
            .then(data => {
                if (data.success) {
                    nurseSuccess('Admission Created', 'New admission created successfully!');
                    closeNewAdmissionModal();
                    
                    // Refresh the admission summary with fallback
                    if (patientId) {
                        if (typeof loadAdmissionSummary === 'function') {
                            loadAdmissionSummary(patientId);
                        } else if (typeof window.loadAdmissionSummary === 'function') {
                            window.loadAdmissionSummary(patientId);
                        } else {
                            // Fallback: reload the page to show updated data
                            console.log('loadAdmissionSummary function not available, refreshing page');
                            setTimeout(() => {
                                location.reload();
                            }, 1500);
                        }
                        
                        // For new admission, reset medicine and lab sections to show empty state
                        if (typeof window.resetMedicineAndLabSectionsForNewAdmission === 'function') {
                            window.resetMedicineAndLabSectionsForNewAdmission();
                        }
                    }
                } else {
                    nurseError('Creation Failed', data.message || 'Failed to create admission. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                nurseError('Creation Failed', 'Error creating admission: ' + error.message);
            })
            .finally(() => {
                saveBtn.textContent = originalText;
                saveBtn.disabled = false;
            });
        });
    }
});

// Close modal when clicking outside
window.onclick = function(event) {
    const labModal = document.getElementById('labRequestModal');
    const admissionModal = document.getElementById('newAdmissionModal');
    
    if (event.target === labModal) {
        closeLabRequestModal();
    }
    if (event.target === admissionModal) {
        closeNewAdmissionModal();
    }
}

// Discharge Patient Function
function dischargePatient(admissionId, patientName, admissionNumber) {
    if (!confirm(`Are you sure you want to discharge ${patientName}?\n\nAdmission: ${admissionNumber}\nNote: This action cannot be undone.`)) {
        return;
    }

    const dischargeBtn = event.target;
    const originalText = dischargeBtn.innerHTML;
    
    // Disable button and show loading state
    dischargeBtn.disabled = true;
    dischargeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Discharging...';

    fetch(`/nurse/discharge-patient/${admissionId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`${patientName} has been successfully discharged!`);
            // Remove the discharge button or refresh the page
            dischargeBtn.style.display = 'none';
            // Optionally refresh the page to update patient status
            location.reload();
        } else {
            alert(`Failed to discharge patient: ${data.message}`);
            // Restore button
            dischargeBtn.disabled = false;
            dischargeBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while discharging the patient.');
        // Restore button
        dischargeBtn.disabled = false;
        dischargeBtn.innerHTML = originalText;
    });
}
</script>

<style>
/* Admission Summary Styles */
.admission-count {
    font-weight: bold;
    margin-bottom: 12px;
    color: #333;
    font-size: 14px;
}

.admissions-list {
    max-height: 400px;
    overflow-y: auto;
}

.admission-item {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    margin-bottom: 12px;
    background: #fafafa;
    transition: all 0.2s ease;
}

.admission-item.active-admission {
    border-color: #28a745;
    background: #f8fff9;
    box-shadow: 0 2px 4px rgba(40, 167, 69, 0.1);
}

.admission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #f1f1f1;
    border-bottom: 1px solid #e0e0e0;
    border-radius: 6px 6px 0 0;
}

.admission-item.active-admission .admission-header {
    background: #e8f5e8;
    border-bottom-color: #28a745;
}

.admission-title {
    font-weight: bold;
    font-size: 13px;
    color: #333;
}

.badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: bold;
    text-transform: uppercase;
}

.badge-success {
    background: #28a745;
    color: white;
}

.badge-secondary {
    background: #6c757d;
    color: white;
}

/* Clickable admission styles */
.clickable-admission {
    transition: all 0.2s ease;
}

.clickable-admission:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
}

.selected-admission {
    background-color: #e3f2fd;
    border-color: #2196f3;
    box-shadow: 0 2px 4px rgba(33, 150, 243, 0.2);
}

/* Discharge Button Styles */
.discharge-btn {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
    font-size: 11px;
    padding: 4px 8px;
    margin-top: 2px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.discharge-btn:hover {
    background-color: #218838;
    border-color: #1e7e34;
    color: white;
    text-decoration: none;
}

.discharge-btn:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    cursor: not-allowed;
}

/* View Button Styles */
.view-btn {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
    font-size: 11px;
    padding: 4px 8px;
    margin-top: 2px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
}

.view-btn:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
    text-decoration: none;
}

#btnEditPatient:hover {
    background-color: #218838;
    color: white;
    text-decoration: none;
}

/* Request Lab Button Styles */
.request-btn {
    font-size: 11px;
    padding: 4px 8px;
    margin-top: 2px;
    border-radius: 4px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.2s ease;
    color: white;
}

.request-btn:hover {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

.admission-details {
    padding: 10px 12px;
    font-size: 12px;
}

.admission-info, .admission-diagnosis, .admission-dates {
    margin-bottom: 6px;
    line-height: 1.4;
}

.admission-info strong,
.admission-diagnosis strong,
.admission-dates strong {
    color: #555;
}

.no-admissions, .error-admissions, .admission-loading {
    text-align: center;
    padding: 20px;
    color: #666;
    font-style: italic;
}

.error-admissions {
    color: #dc3545;
}

/* Health History Styling */
.health-category {
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}

.health-category h5 {
    background: #f8f9fa;
    color: #495057;
    margin: 0;
    padding: 10px 15px;
    border-bottom: 1px solid #e9ecef;
    font-size: 14px;
    font-weight: 600;
}

.health-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
    line-height: 1.5;
}

.health-item:last-child {
    border-bottom: none;
}

.health-item strong {
    color: #2c5f2d;
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
}

.health-item:nth-child(even) {
    background-color: #fafafa;
}
</style>

@endpush

@include('nurse.modals.notification_system')

@endsection