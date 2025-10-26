@extends('layouts.doctor')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

    <link rel="stylesheet" href="{{ asset('css/doctorcss/doctor_patients.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/doctorcss/edit_patient_modal.css') }}">
<link rel="stylesheet" href="{{ asset('css/doctorcss/two_column_form.css') }}">
<link rel="stylesheet" href="{{ asset('css/doctorcss/suggestion_dropdowns.css') }}">
<link rel="stylesheet" href="{{ asset('css/pharmacycss/pharmacy.css') }}">

<style>
.btn-group {
    display: flex;
    gap: 8px;
}

.btn-group .btn {
    flex: 1;
    min-width: 110px;
}

.btn.primary {
    background: #367F2B;
    border: none;
    color: white;
    transition: background-color 0.2s ease;
}

.btn.primary:hover {
    background: #2d6624;
    transform: translateY(-1px);
}

.btn.secondary {
    background: #367F2B;
    border: 1px solid #367F2B;
    color: white;
    transition: background-color 0.2s ease;
}

.btn.secondary:hover {
    background: #2d6624;
    border-color: #2d6624;
}

.btn-outline-primary {
    background: transparent;
    border: 2px solid #367F2B;
    color: #367F2B;
    transition: all 0.2s ease;
}

.btn-outline-primary:hover {
    background: #367F2B;
    color: white;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Expanded container and optimized layout - no horizontal scroll */
.patients-grid {
    display: grid;
    grid-template-columns: 1fr 420px;
    gap: 20px;
    align-items: start;
    padding: 16px;
    max-width: 100%;
    margin: 0 auto;
    box-sizing: border-box;
}

/* Wider table container */
.nurse-card {
    max-width: none;
    margin: 0;
    overflow: hidden;
}

/* Table wrapper to prevent horizontal scroll */
.table-wrap {
    overflow-x: auto;
    max-width: 100%;
}

/* Optimized table columns for better information display */
.patients-table {
    width: 100%;
    min-width: 800px;
    table-layout: auto;
}

.patients-table .col-no {
    width: 80px;
    min-width: 80px;
}

.patients-table .col-name {
    width: 170px;
    min-width: 170px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding-right: 20px;
}

.patients-table .col-dob {
    width: 130px;
    min-width: 130px;
    padding-left: 15px;
}

.patients-table .col-location {
    width: 200px;
    min-width: 180px;
    word-wrap: break-word;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.3;
    max-height: 2.6em;
}

.patients-table .col-natl {
    width: 80px;
    min-width: 80px;
}

.patients-table .col-actions {
    width: 160px;
    min-width: 160px;
}

/* Better text wrapping for patient names */
.col-name {
    font-weight: 600;
    color: #2c5f2d;
}

/* Enhanced location display */
.col-location {
    font-size: 0.95rem;
    color: #495057;
}

/* Responsive adjustments - prevent horizontal scroll */
@media (max-width: 1400px) {
    .patients-grid {
        grid-template-columns: 1fr 400px;
        gap: 18px;
    }
}

@media (max-width: 1200px) {
    .patients-grid {
        grid-template-columns: 1fr 380px;
        gap: 16px;
        padding: 12px;
    }
    
    .patients-table .col-location {
        width: 180px;
        min-width: 160px;
    }
    
    .patients-table .col-name {
        width: 150px;
        min-width: 150px;
        padding-right: 15px;
    }
    
    .patients-table .col-dob {
        width: 120px;
        min-width: 120px;
        padding-left: 12px;
    }
    
    .patients-table .col-actions {
        width: 140px;
        min-width: 140px;
    }
    
    .patients-table {
        min-width: 720px;
    }
}

@media (max-width: 960px) {
    .patients-grid {
        grid-template-columns: 1fr;
        gap: 16px;
        padding: 10px;
    }
    
    .details-column {
        order: 2;
    }
    
    .list-column {
        order: 1;
    }
    
    .patients-table {
        min-width: 680px;
    }
    
    .patients-table .col-actions {
        width: 120px;
        min-width: 120px;
    }
}

@media (max-width: 768px) {
    .patients-grid {
        padding: 8px;
    }
    
    .table-wrap {
        overflow-x: scroll;
        -webkit-overflow-scrolling: touch;
    }
    
    .patients-table {
        min-width: 600px;
    }
    
    .patients-table .col-location {
        width: 150px;
        min-width: 130px;
    }
    
    .patients-table .col-name {
        width: 120px;
        min-width: 120px;
    }
    
    .patients-table .col-actions {
        width: 100px;
        min-width: 100px;
    }
    
    .col-actions .btn {
        padding: 6px 8px;
        font-size: 0.85rem;
    }
}
</style>
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
                                    <div style="display:flex;gap:8px;">
                                        <button type="button" class="btn view-btn js-open-patient">View</button>
                                        <button type="button" class="btn finalize-btn" onclick="handleRowFinalizeClick(this)">Finalize</button>
                                    </div>
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
            <div class="patients-header">
                <h3>Patient Details</h3>
            </div>

            <div class="details-empty" id="detailsEmpty">Select a patient to view details.</div>

            <div class="details-content" id="detailsContent" style="display:none;">
                <!-- Patient Details Section -->
                <div class="details-section">
                    <h4 class="section-header">Patient Details</h4>
                    <dl class="patient-details">
                        <dt>Patient No</dt><dd id="md-patient_no">-</dd>
                        <dt>Full Name</dt><dd id="md-name">-</dd>
                        <dt>Sex</dt><dd id="md-sex">-</dd>
                        <dt>Date of Birth</dt><dd id="md-dob">-</dd>
                        <dt>Age</dt><dd id="md-age">-</dd>
                        <dt>Contact Number</dt><dd id="md-contact_number">-</dd>
                        <dt>Location</dt><dd id="md-location">-</dd>
                        <dt>Nationality</dt><dd id="md-nationality">-</dd>
                    </dl>
                </div>
                
                <!-- Admission Summary Section -->
                <div class="details-section">
                    <h4 class="section-header">Admission Summary</h4>
                    <div id="admission-summary-content">
                        <div class="loading-admissions">Loading admissions...</div>
                    </div>
                </div>
                
                <!-- General Health History Section -->
                <div class="details-section" id="health-history-section" style="display:none;">
                    <h4 class="section-header">General Health History</h4>
                    <div class="view-more-medicines">
                        <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn" onclick="openHealthHistoryModal()">
                            <i class="fas fa-notes-medical"></i> 
                            View General Health History
                        </button>
                    </div>
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
                    <div class="btn-group">
                        <button id="btnMessage" class="btn primary">
                            <i class="fas fa-comments"></i> Chat
                        </button>
                    </div>
                    <button id="btnEditPatient" class="btn secondary">Edit</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="pagination-wrapper">
    {{ $patients->links('components.custom-pagination') }}
</div>

@include('doctor.modals.edit_patient_modal')
@include('doctor.modals.medicine_history_modal')
@include('doctor.modals.lab_results_modal')
@include('doctor.modals.notification_system')
@include('doctor.modals.finalize_diagnosis_modal')
@include('doctor.modals.health_history_modal')

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<style>
/* Admission Summary Styles (original design) */
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Global variable to track current selected admission (accessible globally)
    window.currentSelectedAdmissionId = null;
    
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
        
        // Format sex
        document.getElementById('md-sex').textContent = patient.sex ? formatName(patient.sex) : '-';
        
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
        
        // Format contact number
        document.getElementById('md-contact_number').textContent = patient.contact_number || '-';
        
        // Format location
        const locationParts = [
            formatName(patient.barangay),
            formatName(patient.city),
            formatName(patient.province)
        ].filter(part => part && part !== '-');
        document.getElementById('md-location').textContent = locationParts.length ? locationParts.join(', ') : '-';
        
        document.getElementById('md-nationality').textContent = formatName(patient.nationality);
        
        // Show health history section (for the button)
        const healthHistorySection = document.getElementById('health-history-section');
        if (healthHistorySection) {
            healthHistorySection.style.display = 'block';
        }
        
        // Load admission summary and admission-specific data
        loadAdmissionSummary(patient.id);
        
    }

    // Function to render health history - DISABLED: Now using modal instead
    /*
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
    */
    
    // Helper function to check if health data object has any non-empty values
    function hasHealthData(obj) {
        if (!obj) return false;
        return Object.values(obj).some(value => value && value.trim() !== '');
    }
    
    // Helper function to escape HTML (reuse existing function or define if not available)
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Function to load and display patient medicines
    function loadPatientMedicines(patientId, patient = null, admissionId = null) {
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
        
        // Fetch patient medicines via API with admission filter
        const apiUrl = admissionId 
            ? `/doctor/api/patients/${patientId}/medicines?admission_id=${admissionId}`
            : `/doctor/api/patients/${patientId}/medicines`;
        
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
                
                if (data.success && data.medicines && data.medicines.length > 0) {
                    // Only show "View Medicine Summary" button, no individual medicine display
                    // Get patient info from the patient parameter or from the API response
                    let patientName = 'Unknown Patient';
                    let patientNo = 'N/A';
                    
                    if (patient) {
                        patientName = `${patient.first_name || ''} ${patient.last_name || ''}`.trim();
                        patientNo = patient.patient_no || '';
                    } else if (data.patient) {
                        patientName = `${data.patient.first_name || ''} ${data.patient.last_name || ''}`.trim();
                        patientNo = data.patient.patient_no || '';
                    }
                    
                    medicinesDiv.innerHTML = `
                        <div class="view-more-medicines">
                            <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn" onclick="openMedicineHistoryModal(${patientId}, '${patientName}', '${patientNo}', ${admissionId || 'null'})">
                                <i class="fas fa-pills"></i> 
                                View Medicine Summary (${data.medicines.length} total)
                            </button>
                        </div>
                    `;
                    
                    medicineSection.style.display = 'block';
                } else {
                    // No medicines found
                    medicinesDiv.innerHTML = '<div class="no-medicines">No medicines dispensed yet</div>';
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
        
        // Fetch patient lab results via API with admission filter
        const apiUrl = admissionId 
            ? `/doctor/api/patients/${patientId}/lab-results?admission_id=${admissionId}`
            : `/doctor/api/patients/${patientId}/lab-results`;
        
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
                
                if (data.success && data.tests && data.tests.length > 0) {
                    // Only show "View Lab Results Summary" button, no individual lab result display
                    labResultsDiv.innerHTML = `
                        <div class="view-more-medicines">
                            <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn" onclick="openLabResultsModal(${patientId}, window.currentSelectedAdmissionId)">
                                <i class="fas fa-flask"></i> 
                                View Lab Results Summary (${data.tests.length} total)
                            </button>
                        </div>
                    `;
                    
                    labResultsSection.style.display = 'block';
                } else {
                    // No lab results found
                    labResultsDiv.innerHTML = '<div class="no-lab-results">No lab results available yet</div>';
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
        // Open the PDF in a new window/tab using doctor-accessible route
        window.open(`/doctor/lab-orders/${labOrderId}/view-pdf`, '_blank');
    }
    
    // Make viewLabResultPdf available globally
    window.viewLabResultPdf = viewLabResultPdf;

    // Function to load admission summary
    function loadAdmissionSummary(patientId) {
        const summaryContent = document.getElementById('admission-summary-content');
        
        if (!patientId) {
            summaryContent.innerHTML = '<div class="no-admissions">No patient selected</div>';
            return;
        }
        
        console.log('Loading admission summary for patient ID:', patientId);
        
        // Show loading state
        summaryContent.innerHTML = '<div class="loading-admissions">Loading admissions...</div>';
        
        // Fetch patient admissions
        fetch(`/doctor/api/patients/${patientId}/admissions`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to fetch admissions');
                }
                return response.json();
            })
            .then(data => {
                console.log('Admissions data:', data);
                
                if (data.success && data.admissions && data.admissions.length > 0) {
                    renderAdmissionSummary(data.admissions, patientId);
                } else {
                    summaryContent.innerHTML = '<div class="no-admissions">No admissions found for this patient</div>';
                }
            })
            .catch(error => {
                console.error('Error loading admissions:', error);
                summaryContent.innerHTML = `<div class="error-admissions">Failed to load admissions: ${error.message}</div>`;
            });
    }

    // Function to render admission summary (matching nurse layout exactly)
    // Inject server-side role info for client-side scripts
    const isNurse = @json(auth()->user()->role === 'nurse');
    // Expose to global for use in other functions
    window.isNurse = isNurse === true;
    function renderAdmissionSummary(admissions, patientId) {
        const summaryContent = document.getElementById('admission-summary-content');
        if (!summaryContent) return;

        let admissionsHtml = '';
        admissions.forEach((admission, index) => {
            const isActive = admission.status === 'active';
            const statusBadge = isActive ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Discharged</span>';

            admissionsHtml += `
                <div class="admission-item ${isActive ? 'active-admission' : ''} clickable-admission" 
                     data-admission-id="${admission.id}" 
                     onclick="selectAdmission(${admission.id}, ${patientId}, this)"
                     style="cursor: pointer;">
                    <div class="admission-header">
                        <span class="admission-title">Admission #${admission.admission_number || admission.id}</span>
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

        const hasActive = admissions.some(a => a.status === 'active');
        // Only show the New Admission button to nurses. Doctors should not see it.
        const newAdmissionButton = (!hasActive && window.isNurse === true) ? `
            <div class="new-admission-section">
                <button type="button" class="btn btn-success btn-sm new-admission-btn" onclick="openNewAdmissionModal(${patientId})">
                    <i class="fas fa-plus"></i> New Admission
                </button>
            </div>
        ` : '';

        summaryContent.innerHTML = `
            <div class="admission-count">Total Admissions: ${admissions.length}</div>
            ${newAdmissionButton}
            <div class="admissions-list">${admissionsHtml}</div>
        `;

        // Auto-load active admission if present or most recent
        const activeAdmission = admissions.find(a => a.status === 'active');
        if (activeAdmission) {
            setTimeout(() => selectAdmission(activeAdmission.id, patientId, null, true), 100);
        } else if (admissions.length > 0) {
            setTimeout(() => selectAdmission(admissions[0].id, patientId, null, true), 100);
        }
    }

    // Function to select an admission and load its data
    function selectAdmission(admissionId, patientId, element, isAutoSelect = false) {
        console.log('Selecting admission:', admissionId, 'for patient:', patientId);
        
        // Update current selected admission (global variable)
        window.currentSelectedAdmissionId = admissionId;
        
        // Update UI selection
        if (!isAutoSelect && element) {
            document.querySelectorAll('.admission-card').forEach(card => {
                card.classList.remove('selected-admission');
            });
            element.classList.add('selected-admission');
        }
        
        // Load admission-specific data
        loadAdmissionSpecificData(patientId, admissionId);
    }

    // Function to load admission-specific medicine and lab data
    function loadAdmissionSpecificData(patientId, admissionId) {
        console.log('Loading data for patient:', patientId, 'admission:', admissionId);
        
        // Load admission-specific medicines
        loadPatientMedicines(patientId, null, admissionId);
        
        // Load admission-specific lab results
        loadPatientLabResults(patientId, admissionId);
    }

    // Make functions globally accessible
    window.loadAdmissionSummary = loadAdmissionSummary;
    window.selectAdmission = selectAdmission;

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
                    doctorError('Data Error', 'Failed to load patient details: ' + error.message);
                }
            });
        }
    });

    // optionally auto-select first row
    if (rows.length && !document.querySelector('.patient-row.active')) {
        rows[0].querySelector('.js-open-patient').click();
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
            element.value = value || '';
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
            
            // Set sex field (dropdown)
            safeSetValue('edit_sex', patient.sex);
            
            // Set contact number
            safeSetValue('edit_contact_number', patient.contact_number);
            
            safeSetValue('edit_province', patient.province);
            safeSetValue('edit_city', patient.city);
            safeSetValue('edit_barangay', patient.barangay);
            safeSetValue('edit_nationality', patient.nationality);
            safeSetValue('edit_room_no', patient.room_no);
            safeSetValue('edit_admission_type', patient.admission_type);
            
            // Note: edit_service field doesn't exist in the modal, so we skip it
            // safeSetValue('edit_service', patient.service);
            
            safeSetValue('edit_doctor_type', patient.doctor_type);
            safeSetValue('edit_admission_diagnosis', patient.admission_diagnosis);
        
        // Dynamically populate admission diagnosis description based on the ICD-10 code
        const diagDescField = document.getElementById('edit_admission_diagnosis_description');
        if (diagDescField && patient.admission_diagnosis) {
            // Clear the field first
            diagDescField.value = 'Loading description...';
            
            // Fetch the description from ICD-10 lookup
            fetch('/icd10/search?q=' + encodeURIComponent(patient.admission_diagnosis))
                .then(async r => {
                    const ct = (r.headers.get('content-type') || '').toLowerCase();
                    const text = await r.text();
                    
                    if (ct.includes('application/json')) {
                        try {
                            const data = JSON.parse(text);
                            if (Array.isArray(data) && data.length > 0) {
                                // Find exact match or first result
                                const match = data.find(item => 
                                    item.code && item.code.toLowerCase() === patient.admission_diagnosis.toLowerCase()
                                ) || data[0];
                                
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
        
        // Initialize doctor field to ensure suggestions are hidden - but this clears the field!
        if (typeof window.initializeDoctorField === 'function') {
            window.initializeDoctorField();
        }
        
        // Set doctor fields AFTER initialization to prevent them from being cleared
        console.log('Setting doctor input with value:', patient.doctor_name);
        const doctorInputSet = safeSetValue('edit_doctor_input', patient.doctor_name);
        const doctorNameSet = safeSetValue('edit_doctor_name', patient.doctor_name);
        console.log('Doctor input set success:', doctorInputSet, 'Doctor name set success:', doctorNameSet);
        
        // Populate health history fields
        populateHealthHistoryFields(patient);
        
        modal.classList.add('open');
        modal.classList.add('show');
        } catch (error) {
            console.error('Error in openEditModal:', error);
            doctorError('Modal Error', 'Failed to open edit modal: ' + error.message);
        }
    }

    // Function to populate health history fields in edit modal
    function populateHealthHistoryFields(patient) {
        // General Health History
        if (patient.general_health_history) {
            const healthHistory = patient.general_health_history;
            
            // Medical Conditions
            if (healthHistory.medical_conditions) {
                safeSetValue('edit_chronic_illnesses', healthHistory.medical_conditions.chronic_illnesses || '');
                safeSetValue('edit_hospitalization_history', healthHistory.medical_conditions.hospitalization_history || '');
                safeSetValue('edit_surgery_history', healthHistory.medical_conditions.surgery_history || '');
                safeSetValue('edit_accident_injury_history', healthHistory.medical_conditions.accident_injury_history || '');
            }
            
            // Medications
            if (healthHistory.medications) {
                safeSetValue('edit_current_medications', healthHistory.medications.current_medications || '');
                safeSetValue('edit_long_term_medications', healthHistory.medications.long_term_medications || '');
            }
            
            // Allergies
            if (healthHistory.allergies) {
                safeSetValue('edit_known_allergies', healthHistory.allergies.known_allergies || '');
            }
            
            // Family History
            if (healthHistory.family_history) {
                safeSetValue('edit_family_history_chronic', healthHistory.family_history.family_history_chronic || '');
            }
        }
        
        // Social History
        if (patient.social_history && patient.social_history.lifestyle_habits) {
            const lifestyleHabits = patient.social_history.lifestyle_habits;
            safeSetValue('edit_smoking_history', lifestyleHabits.smoking_history || '');
            safeSetValue('edit_alcohol_consumption', lifestyleHabits.alcohol_consumption || '');
            safeSetValue('edit_recreational_drugs', lifestyleHabits.recreational_drugs || '');
            safeSetValue('edit_exercise_activity', lifestyleHabits.exercise_activity || '');
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

    // Health History Modal Functions
    function openHealthHistoryModal() {
        const modal = document.getElementById('healthHistoryModal');
        if (!modal) {
            console.error('Health history modal not found');
            return;
        }
        
        // Try to get patient data from the active row
        const activeRow = document.querySelector('tr.active');
        if (!activeRow) {
            alert('Please select a patient first');
            return;
        }
        
        let selectedPatient;
        try {
            const patientData = activeRow.getAttribute('data-patient');
            selectedPatient = JSON.parse(patientData);
        } catch (error) {
            console.error('Error getting patient data:', error);
            alert('Error loading patient data');
            return;
        }
        
        // Set patient info - check if elements exist
        const patientNameEl = document.getElementById('health-history-patient-name');
        const patientNoEl = document.getElementById('health-history-patient-no');
        
        if (patientNameEl) {
            const fullName = `${selectedPatient.first_name} ${selectedPatient.middle_name} ${selectedPatient.last_name}`.replace(/\s+/g, ' ').trim();
            patientNameEl.textContent = fullName;
        }
        if (patientNoEl) {
            patientNoEl.textContent = `Patient No: ${selectedPatient.patient_no}`;
        }
        
        // Show loading - check if elements exist
        const loadingEl = document.getElementById('healthHistoryLoading');
        const detailsEl = document.getElementById('healthHistoryDetails');
        const emptyEl = document.getElementById('healthHistoryEmpty');
        
        if (loadingEl) loadingEl.style.display = 'block';
        if (detailsEl) detailsEl.style.display = 'none';
        if (emptyEl) emptyEl.style.display = 'none';
        
        // Show modal
        modal.classList.add('show');
        
        // Load health history data
        loadHealthHistoryData(selectedPatient.id);
    }

    function closeHealthHistoryModal() {
        const modal = document.getElementById('healthHistoryModal');
        if (modal) {
            modal.classList.remove('show');
        }
    }

    function loadHealthHistoryData(patientId) {
        fetch(`/doctor/api/patient/${patientId}/health-history`)
            .then(response => response.json())
            .then(data => {
                const loadingEl = document.getElementById('healthHistoryLoading');
                if (loadingEl) loadingEl.style.display = 'none';
                
                if (data.success && data.health_history) {
                    const history = data.health_history;
                    
                    // Populate health history fields - check if elements exist
                    const chronicEl = document.getElementById('chronic-illnesses');
                    if (chronicEl) chronicEl.textContent = history.chronic_illnesses || 'No chronic illnesses reported';
                    
                    const hospitalizationEl = document.getElementById('hospitalization-history');
                    if (hospitalizationEl) hospitalizationEl.textContent = history.hospitalization_history || 'No hospitalization history reported';
                    
                    const surgeryEl = document.getElementById('surgery-history');
                    if (surgeryEl) surgeryEl.textContent = history.surgery_history || 'No surgery history reported';
                    
                    const accidentEl = document.getElementById('accident-history');
                    if (accidentEl) accidentEl.textContent = history.accident_injury_history || 'No accident/injury history reported';
                    
                    const currentMedEl = document.getElementById('current-medications');
                    if (currentMedEl) currentMedEl.textContent = history.current_medications || 'No current medications reported';
                    
                    const longtermMedEl = document.getElementById('longterm-medications');
                    if (longtermMedEl) longtermMedEl.textContent = history.longterm_medications || 'No long-term medications reported';
                    
                    const allergiesEl = document.getElementById('known-allergies');
                    if (allergiesEl) allergiesEl.textContent = history.known_allergies || 'No known allergies reported';
                    
                    const familyEl = document.getElementById('family-history');
                    if (familyEl) familyEl.textContent = history.family_history_chronic_diseases || 'No family history of chronic diseases reported';
                    
                    // Populate social history fields
                    const smokingEl = document.getElementById('smoking-history');
                    if (smokingEl) smokingEl.textContent = history.smoking_history || 'No smoking history reported';
                    
                    const alcoholEl = document.getElementById('alcohol-consumption');
                    if (alcoholEl) alcoholEl.textContent = history.alcohol_consumption || 'No alcohol consumption reported';
                    
                    const drugsEl = document.getElementById('recreational-drugs');
                    if (drugsEl) drugsEl.textContent = history.recreational_drugs || 'No recreational drug use reported';
                    
                    const exerciseEl = document.getElementById('exercise-activity');
                    if (exerciseEl) exerciseEl.textContent = history.exercise_activity || 'No exercise activity reported';
                    
                    const detailsEl = document.getElementById('healthHistoryDetails');
                    if (detailsEl) detailsEl.style.display = 'block';
                } else {
                    const emptyEl = document.getElementById('healthHistoryEmpty');
                    if (emptyEl) emptyEl.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error loading health history:', error);
                const loadingEl = document.getElementById('healthHistoryLoading');
                const emptyEl = document.getElementById('healthHistoryEmpty');
                if (loadingEl) loadingEl.style.display = 'none';
                if (emptyEl) emptyEl.style.display = 'block';
            });
    }

    // Save button sends PUT to update with validation
    const saveBtn = document.getElementById('savePatientBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', async function(e){
        e.preventDefault(); 
        const form = document.getElementById('editPatientForm');
        const patient_no = form.patient_no;
        if(!patient_no) { doctorError('Selection Error', 'No patient selected'); return; }
        
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
            doctorError('Validation Error', errorMessage);
            return;
        }
        
        // If validation passes, proceed with form submission
        const data = new FormData(form);
        const token = _csrf();
        // PHP doesn't parse multipart/form-data for PUT; use POST with _method override so Laravel receives fields
        data.set('_token', token);
        data.set('_method', 'PUT');
        fetch(`/doctor/patients/${encodeURIComponent(patient_no)}`, {
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
        }).catch(e=>{ console.error(e); doctorError('Update Failed', e.message); });
        });
    }

    // wire edit button to open modal with currently selected patient
    if (btnEdit) {
        btnEdit.addEventListener('click', function(){
            const patientNo = document.getElementById('md-patient_no').textContent;
            if(!patientNo || patientNo === '-') { doctorError('Selection Error', 'No patient selected'); return; }
            // find the row with that patient_no
            const row = Array.from(rows).find(r => r.querySelector('.col-no')?.textContent.trim() === patientNo.toString());
            if(!row) { doctorError('Search Error', 'Patient not found'); return; }
            try{ 
                const patient = JSON.parse(row.getAttribute('data-patient')); 
                console.log('Patient data:', patient); // Debug log
                openEditModal(patient); 
            } catch(e) { 
                console.error('JSON parse error:', e); 
                console.log('Raw data:', row.getAttribute('data-patient')); 
                doctorError('Modal Error', 'Failed to open edit modal: ' + e.message); 
            }
        });
    }

    // wire message buttons to create/open chat for selected patient
    const btnMessage = document.getElementById('btnMessage');
    
    // Legacy chat button
    if (btnMessage) {
        btnMessage.addEventListener('click', function(){
            createLegacyChat();
        });
    }
    
    // Function to create legacy chat
    function createLegacyChat() {
        const patientNo = document.getElementById('md-patient_no').textContent;
        if(!patientNo || patientNo === '-') { doctorError('Selection Error', 'No patient selected'); return; }
        
        // find the row with that patient_no
        const row = Array.from(rows).find(r => r.querySelector('.col-no')?.textContent.trim() === patientNo.toString());
        if(!row) { doctorError('Search Error', 'Patient not found'); return; }
        
        try{ 
            const patient = JSON.parse(row.getAttribute('data-patient')); 
            console.log('Creating legacy chat for patient:', patient);
            
            // Show loading state
            btnMessage.disabled = true;
            btnMessage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            
            // Create or get chat room for this patient
            fetch('/doctor/chat/create-for-patient', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': _csrf()
                },
                body: JSON.stringify({
                    patient_id: patient.id
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect to chat room
                    window.location.href = data.redirect_url;
                } else {
                    throw new Error(data.message || 'Failed to create chat room');
                }
            })
            .catch(error => {
                console.error('Error creating legacy chat:', error);
                doctorError('Chat Error', 'Failed to create chat room: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                btnMessage.disabled = false;
                btnMessage.innerHTML = '<i class="fas fa-comments"></i> Legacy Chat';
            });
        } catch(e) { 
            console.error('JSON parse error:', e); 
            doctorError('Data Error', 'Failed to process patient data: ' + e.message); 
        }
    }
});

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
            doctorError('Request Failed', 'Error submitting request. Please try again.');
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

// Simple notification functions for doctor interface
function doctorError(title, message) {
    console.error(`${title}: ${message}`);
    alert(`${title}: ${message}`);
}

function doctorSuccess(title, message) {
    console.log(`${title}: ${message}`);
    alert(`${title}: ${message}`);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    if (event.target === editModal) {
        closeModal();
    }
}

// Handle clicking finalize on a patient row. It fetches active admission and opens modal.
function handleRowFinalizeClick(button) {
    try {
        const row = button.closest('.patient-row');
        if (!row) { alert('No patient row found'); return; }
        const patient = JSON.parse(row.getAttribute('data-patient'));
        if (!patient || !patient.id) { alert('Patient data missing'); return; }
        // fetch active admission for this patient
        fetch(`/doctor/api/patients/${patient.id}/active-admission`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.admission) {
                    const admission = data.admission;
                    // Open finalize modal with admission id
                    if (typeof window.openFinalizeModal === 'function') {
                        window.openFinalizeModal(admission.id);
                    } else {
                        alert('Finalize modal not available');
                    }
                } else {
                    alert(data.message || 'No active admission found for this patient');
                }
            }).catch(e => { console.error('Error fetching active admission', e); alert('Failed to fetch active admission'); });
    } catch (e) { console.error(e); alert('Failed to open finalize modal'); }
}
</script>
@endpush

@include('doctor.modals.notification_system')

@endsection
