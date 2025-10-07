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
<style>
.details-section {
    margin-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 15px;
}

.details-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.section-header {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 10px 0;
    padding: 5px 0;
    border-bottom: 2px solid #3498db;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.medicine-item {
    background: #f8f9fa;
    padding: 12px;
    margin: 8px 0;
    border-radius: 6px;
    border-left: 4px solid #28a745;
    font-size: 13px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.medicine-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.medicine-header strong {
    color: #2c3e50;
    font-size: 14px;
}

.medicine-quantity {
    background: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
}

.medicine-details {
    font-size: 12px;
    color: #666;
    line-height: 1.4;
}

.medicine-price {
    font-weight: 600;
    color: #17a2b8;
    margin-bottom: 2px;
}

.medicine-meta {
    color: #888;
    font-style: italic;
}

.medicine-notes {
    margin-top: 6px;
    padding: 6px 8px;
    background: #e9ecef;
    border-radius: 4px;
    font-size: 11px;
}

.loading-medicines, .no-medicines, .error-medicines {
    padding: 12px;
    text-align: center;
    font-style: italic;
    color: #666;
    background: #f8f9fa;
    border-radius: 4px;
    margin: 8px 0;
}

.loading-medicines {
    color: #007bff;
}

.error-medicines {
    color: #dc3545;
    background: #f8d7da;
}

.no-medicines {
    color: #6c757d;
}

.view-more-medicines {
    padding: 10px;
    text-align: center;
    margin-top: 8px;
}

.view-medicine-summary-btn {
    background: #fff;
    border: 1px solid #007bff;
    color: #007bff;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.view-medicine-summary-btn:hover {
    background: #007bff;
    color: white;
}

.patient-details dt {
    font-weight: 600;
    color: #555;
}

.patient-details dd {
    color: #333;
    margin-bottom: 8px;
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
                                    'date_of_birth' => $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : null,
                                    'province' => $p->province,
                                    'city' => $p->city,
                                    'barangay' => $p->barangay,
                                    'nationality' => $p->nationality,
                                    'room_no' => $p->room_no,
                                    'admission_type' => $p->admission_type,
                                    'service' => $p->service,
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
                                    <button type="button" class="request-btn btn" onclick="openLabRequestModal({{ $p->id }}, '{{ $p->first_name }} {{ $p->last_name }}', '{{ $p->patient_no }}')">Request Lab</button>
                                    <button type="button" class="request-btn btn" onclick="openMedicineRequestModal({{ $p->id }}, '{{ $p->first_name }} {{ $p->last_name }}', '{{ $p->patient_no }}')">Request Medicine</button>
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
                        <dt>Date of Birth</dt><dd id="md-dob">-</dd>
                        <dt>Age</dt><dd id="md-age">-</dd>
                        <dt>Location</dt><dd id="md-location">-</dd>
                        <dt>Nationality</dt><dd id="md-nationality">-</dd>
                    </dl>
                </div>
                
                <!-- Admission Details Section -->
                <div class="details-section">
                    <h4 class="section-header">Admission Details</h4>
                    <dl class="patient-details">
                        <dt>Room No.</dt><dd id="md-room_no">-</dd>
                        <dt>Admission Type</dt><dd id="md-admission_type">-</dd>
                        <dt>Service</dt><dd id="md-service">-</dd>
                        <dt>Doctor</dt><dd id="md-doctor_name">-</dd>
                        <dt>Doctor Type</dt><dd id="md-doctor_type">-</dd>
                        <dt>Diagnosis</dt><dd id="md-admission_diagnosis">-</dd>
                        <dt>Admitted</dt><dd id="md-created_at">-</dd>
                    </dl>
                </div>
                
                <!-- Medicine Details Section -->
                <div class="details-section" id="medicine-section" style="display:none;">
                    <h4 class="section-header">Medicine Details</h4>
                    <div id="md-medicines">No medicines dispensed</div>
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
        
        // Format location
        const locationParts = [
            formatName(patient.barangay),
            formatName(patient.city),
            formatName(patient.province)
        ].filter(part => part && part !== '-');
        document.getElementById('md-location').textContent = locationParts.length ? locationParts.join(', ') : '-';
        
        document.getElementById('md-nationality').textContent = formatName(patient.nationality);
        
        // Admission Details Section
        document.getElementById('md-room_no').textContent = or(patient.room_no);
        document.getElementById('md-admission_type').textContent = formatName(patient.admission_type);
        document.getElementById('md-service').textContent = formatName(patient.service);
        document.getElementById('md-doctor_name').textContent = formatName(patient.doctor_name);
        document.getElementById('md-doctor_type').textContent = formatName(patient.doctor_type);
        document.getElementById('md-admission_diagnosis').textContent = or(patient.admission_diagnosis);
        document.getElementById('md-created_at').textContent = formatDateTime(patient.created_at);
        
        // Load patient medicines
        loadPatientMedicines(patient.id);
    }
    
    // Function to load and display patient medicines
    function loadPatientMedicines(patientId) {
        const medicineSection = document.getElementById('medicine-section');
        const medicinesDiv = document.getElementById('md-medicines');
        
        if (!patientId) {
            medicineSection.style.display = 'none';
            return;
        }
        
        console.log('Loading medicines for patient ID:', patientId);
        
        // Show loading state
        medicinesDiv.innerHTML = '<div class="loading-medicines">Loading medicines...</div>';
        medicineSection.style.display = 'block';
        
        // Fetch patient medicines via API
        fetch(`/api/patients/${patientId}/medicines`)
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
                    // Limit display to first 3 medicines
                    const displayMedicines = data.medicines.slice(0, 3);
                    const hasMore = data.medicines.length > 3;
                    
                    // Display medicines
                    medicinesDiv.innerHTML = displayMedicines.map(med => {
                        const medicineName = med.medicine_name || 'Unknown Medicine';
                        const quantity = med.quantity || 0;
                        const unitPrice = med.unit_price ? `₱${parseFloat(med.unit_price).toFixed(2)}` : 'No price';
                        const totalPrice = med.total_price ? `₱${parseFloat(med.total_price).toFixed(2)}` : 'No total';
                        const dispensedAt = med.dispensed_at ? formatDateTime(med.dispensed_at) : 'Unknown date';
                        const dispensedBy = med.dispensed_by ? formatName(med.dispensed_by) : 'Unknown';
                        const notes = med.notes ? `<div class="medicine-notes"><strong>Notes:</strong> ${med.notes}</div>` : '';
                        
                        return `
                            <div class="medicine-item">
                                <div class="medicine-header">
                                    <strong>${medicineName}</strong>
                                    <span class="medicine-quantity">${quantity} units</span>
                                </div>
                                <div class="medicine-details">
                                    <div class="medicine-price">Unit: ${unitPrice} | Total: ${totalPrice}</div>
                                    <div class="medicine-meta">Dispensed: ${dispensedAt} by ${dispensedBy}</div>
                                    ${notes}
                                </div>
                            </div>
                        `;
                    }).join('');
                    
                    // Add "View Medicine Summary" button if there are more than 3 medicines
                    if (hasMore) {
                        medicinesDiv.innerHTML += `
                            <div class="view-more-medicines">
                                <button type="button" class="btn btn-outline-primary btn-sm view-medicine-summary-btn">
                                    <i class="fas fa-pills"></i> 
                                    View Medicine Summary (${data.medicines.length} total)
                                </button>
                            </div>
                        `;
                    }
                    
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
                    nurseError('Data Error', 'Failed to load patient details: ' + error.message);
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
            
            safeSetValue('edit_province', patient.province);
            safeSetValue('edit_city', patient.city);
            safeSetValue('edit_barangay', patient.barangay);
            safeSetValue('edit_nationality', patient.nationality);
            safeSetValue('edit_room_no', patient.room_no);
            safeSetValue('edit_admission_type', patient.admission_type);
            
            // Note: edit_service field doesn't exist in the modal, so we skip it
            // safeSetValue('edit_service', patient.service);
            
            safeSetValue('edit_doctor_name', patient.doctor_name);
            safeSetValue('edit_doctor_type', patient.doctor_type);
            safeSetValue('edit_admission_diagnosis', patient.admission_diagnosis);
        
        // Dynamically populate admission diagnosis description based on the ICD-10 code
        const diagDescField = document.getElementById('edit_admission_diagnosis_description');
        if (diagDescField && patient.admission_diagnosis) {
            // Clear the field first
            diagDescField.value = 'Loading description...';
            
            // Fetch the description from ICD-10 lookup
            fetch('{{ route("icd10.search") }}?q=' + encodeURIComponent(patient.admission_diagnosis))
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
        
        // Initialize doctor field to ensure suggestions are hidden
        if (typeof window.initializeDoctorField === 'function') {
            window.initializeDoctorField();
        }
        
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
            const roomValidation = fetch('{{ route("rooms.validate") }}', {
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
            const icdValidation = fetch('{{ route("icd10.validate") }}', {
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
    // Set global flag to prevent sidebar height adjustments
    window.isModalOpen = true;
    
    document.getElementById('requestPatientId').value = patientId;
    document.getElementById('requestPatientInfo').textContent = `${patientName} (ID: ${patientNo})`;
    const modal = document.getElementById('labRequestModal');
    modal.classList.add('show');
    // Reset form
    document.getElementById('labRequestForm').reset();
    document.getElementById('requestPatientId').value = patientId; // Reset this after form reset
    
    // Ensure additional tests textarea is disabled by default
    const additionalTestsCheckbox = document.getElementById('enableAdditionalTests');
    const additionalTestsTextarea = document.getElementById('additionalTests');
    additionalTestsCheckbox.checked = false;
    additionalTestsTextarea.disabled = true;
    
    updateTestOptions(); // Reset the specific test dropdown
}

function closeLabRequestModal() {
    const modal = document.getElementById('labRequestModal');
    modal.classList.remove('show');
    document.getElementById('labRequestForm').reset();
    
    // Clear global flag after a delay to ensure modal is fully closed
    setTimeout(() => {
        window.isModalOpen = false;
    }, 300);
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
    
    // Combine test category and specific test into a single test_requested field
    const category = formData.get('test_category');
    const specificTest = formData.get('specific_test');
    const additionalTests = formData.get('additional_tests');
    
    let testRequested = '';
    if (category && specificTest) {
        testRequested = `${category.toUpperCase()}: ${specificTest}`;
        if (additionalTests) {
            testRequested += `\n\nAdditional: ${additionalTests}`;
        }
    }
    
    // Update the FormData with combined test_requested
    formData.set('test_requested', testRequested);
    
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

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('labRequestModal');
    if (event.target === modal) {
        closeLabRequestModal();
    }
}
</script>
@endpush

@include('nurse.modals.notification_system')

@endsection