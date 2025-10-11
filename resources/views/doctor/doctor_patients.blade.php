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
                                    <button type="button" class="btn view-btn js-open-patient">View</button>
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
                    <button id="btnMessage" class="btn secondary">
                        <i class="fas fa-comments"></i> Message
                    </button>
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

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<style>
/* Admission Summary Styles */
.admissions-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 400px;
    overflow-y: auto;
}

.admission-card {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 16px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.admission-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.admission-card.selected-admission {
    border-color: #007bff;
    background: #f8f9ff;
    box-shadow: 0 4px 12px rgba(0,123,255,0.2);
}

.admission-card.active-admission {
    border-color: #28a745;
}

.admission-card.active-admission.selected-admission {
    border-color: #28a745;
    background: #f8fff9;
}

.admission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.admission-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.active-badge {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.admission-date {
    color: #6c757d;
    font-size: 14px;
}

.admission-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: #495057;
}

.detail-item i {
    color: #007bff;
    width: 16px;
}

.loading-admissions, .no-admissions, .error-admissions {
    text-align: center;
    padding: 20px;
    color: #6c757d;
    font-style: italic;
}

.error-admissions {
    color: #dc3545;
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
        
        // Load admission summary and admission-specific data
        loadAdmissionSummary(patient.id);
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
                            <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn" onclick="openMedicineHistoryModal(${patientId}, '${patientName}', '${patientNo}')">
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

    // Function to render admission summary
    function renderAdmissionSummary(admissions, patientId) {
        const summaryContent = document.getElementById('admission-summary-content');
        
        let html = '<div class="admissions-list">';
        
        admissions.forEach((admission, index) => {
            const isActive = admission.status === 'active';
            const isSelected = index === 0; // Select first (most recent) admission by default
            
            html += `
                <div class="admission-card ${isActive ? 'active-admission' : ''} ${isSelected ? 'selected-admission' : ''}" 
                     data-admission-id="${admission.id}" 
                     onclick="selectAdmission(${admission.id}, ${patientId}, this)">
                    <div class="admission-header">
                        <div class="admission-info">
                            <strong>${admission.admission_type || 'N/A'} - Room ${admission.room_no || 'N/A'}</strong>
                            ${isActive ? '<span class="active-badge">Active</span>' : ''}
                        </div>
                        <div class="admission-date">${formatDate(admission.admission_date)}</div>
                    </div>
                    <div class="admission-details">
                        <div class="detail-item">
                            <i class="fas fa-user-md"></i>
                            <span>${admission.doctor_name || 'N/A'} (${admission.doctor_type || 'N/A'})</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-notes-medical"></i>
                            <span>${admission.admission_diagnosis || 'No diagnosis'}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        summaryContent.innerHTML = html;
        
        // Auto-select first admission and load its data
        if (admissions.length > 0) {
            selectAdmission(admissions[0].id, patientId, null, true);
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
        
        modal.classList.add('open');
        modal.classList.add('show');
        } catch (error) {
            console.error('Error in openEditModal:', error);
            doctorError('Modal Error', 'Failed to open edit modal: ' + error.message);
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

    // wire message button to create/open chat for selected patient
    const btnMessage = document.getElementById('btnMessage');
    if (btnMessage) {
        btnMessage.addEventListener('click', function(){
            const patientNo = document.getElementById('md-patient_no').textContent;
            if(!patientNo || patientNo === '-') { doctorError('Selection Error', 'No patient selected'); return; }
            
            // find the row with that patient_no
            const row = Array.from(rows).find(r => r.querySelector('.col-no')?.textContent.trim() === patientNo.toString());
            if(!row) { doctorError('Search Error', 'Patient not found'); return; }
            
            try{ 
                const patient = JSON.parse(row.getAttribute('data-patient')); 
                console.log('Creating chat for patient:', patient);
                
                // Show loading state
                btnMessage.disabled = true;
                btnMessage.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Chat...';
                
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
                    console.error('Error creating chat:', error);
                    doctorError('Chat Error', 'Failed to create chat room: ' + error.message);
                })
                .finally(() => {
                    // Reset button state
                    btnMessage.disabled = false;
                    btnMessage.innerHTML = '<i class="fas fa-comments"></i> Message';
                });
            } catch(e) { 
                console.error('JSON parse error:', e); 
                doctorError('Data Error', 'Failed to process patient data: ' + e.message); 
            }
        });
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
</script>
@endpush

@include('doctor.modals.notification_system')

@endsection
