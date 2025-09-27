@extends('layouts.app')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

    <link rel="stylesheet" href="{{ url('css/nursecss/nurse_patients.css') }}">
    <link rel="stylesheet" href="{{ url('css/pagination.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="{{ url('css/nursecss/edit_patient_modal.css') }}">

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
                            <tr class="patient-row" data-patient='@json($p)'>
                                <td class="col-no">{{ $p->patient_no }}</td>
                                <td class="col-name">{{ $p->last_name }}, {{ $p->first_name }}{{ $p->middle_name ? ' '.$p->middle_name : '' }}</td>
                                <td class="col-dob">
                                    {{ $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-' }}<br>
                                    <small class="text-muted">{{ $p->age_years ?? '-' }}y {{ $p->age_months ?? '-' }}m {{ $p->age_days ?? '-' }}d</small>
                                </td>
                                <td class="col-location">{{ $p->barangay ? $p->barangay.',' : '' }} {{ $p->city }}, {{ $p->province }}</td>
                                <td class="col-natl">{{ $p->nationality }}</td>
                                <td class="col-actions">
                                    <button type="button" class="btn view-btn js-open-patient">View</button>
                                    <button type="button" class="request-btn btn" onclick="openLabRequestModal({{ $p->id }}, '{{ $p->first_name }} {{ $p->last_name }}', '{{ $p->patient_no }}')">Request</button>
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
                <dl class="patient-details">
                    <dt>Patient No</dt><dd id="md-patient_no">-</dd>
                    <dt>Full Name</dt><dd id="md-name">-</dd>
                    <dt>Date of Birth</dt><dd id="md-dob">-</dd>
                    <dt>Age</dt><dd id="md-age">-</dd>
                    <dt>Province / City / Barangay</dt><dd id="md-location">-</dd>
                    <dt>Nationality</dt><dd id="md-nationality">-</dd>
                    <dt>Room No.</dt><dd id="md-room_no">-</dd>
                    <dt>Admission Diagnosis</dt><dd id="md-admission_diagnosis">-</dd>
                    <dt>Admission Type</dt><dd id="md-admission_type">-</dd>
                    <dt>Service</dt><dd id="md-service">-</dd>
                    <dt>Doctor</dt><dd id="md-doctor_name">-</dd>
                    <dt>Doctor Type</dt><dd id="md-doctor_type">-</dd>
                    <dt>Created At</dt><dd id="md-created_at">-</dd>
                </dl>

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

    function renderPatient(patient){
        document.getElementById('md-patient_no').textContent = or(patient.patient_no);
        document.getElementById('md-name').textContent = or([patient.last_name, patient.first_name, patient.middle_name].filter(Boolean).join(', '));
        document.getElementById('md-dob').textContent = or(patient.date_of_birth);
        const years = patient.age_years ?? '-';
        const months = patient.age_months ?? '-';
        const days = patient.age_days ?? '-';
        document.getElementById('md-age').textContent = `${years}y ${months}m ${days}d`;
        document.getElementById('md-location').textContent = or((patient.barangay ? patient.barangay + ', ' : '') + or(patient.city) + ', ' + or(patient.province));
        document.getElementById('md-nationality').textContent = or(patient.nationality);
        document.getElementById('md-room_no').textContent = or(patient.room_no);
        document.getElementById('md-admission_diagnosis').textContent = or(patient.admission_diagnosis);
        document.getElementById('md-admission_type').textContent = or(patient.admission_type);
        document.getElementById('md-service').textContent = or(patient.service);
        document.getElementById('md-doctor_name').textContent = or(patient.doctor_name);
        document.getElementById('md-doctor_type').textContent = or(patient.doctor_type);
        document.getElementById('md-created_at').textContent = or(patient.created_at);
    }

    function clearActive(){
        rows.forEach(r => r.classList.remove('active'));
    }

    rows.forEach(row => {
        const btn = row.querySelector('.js-open-patient');
        btn.addEventListener('click', function(){
            const payload = row.getAttribute('data-patient');
            try {
                const patient = JSON.parse(payload);
                clearActive();
                row.classList.add('active');
                detailsEmpty.style.display = 'none';
                detailsContent.style.display = '';
                renderPatient(patient);

                // update "Open Full" link to go to patient page if route exists
                const btnFull = document.getElementById('detailsViewFull');
                if (btnFull) {
                    // now handled via edit button/modal
                    btnFull.href = `#`;
                }
            } catch(e){
                console.error('Invalid patient JSON', e);
            }
        });
    });

    // optionally auto-select first row
    if (rows.length && !document.querySelector('.patient-row.active')) {
        rows[0].querySelector('.js-open-patient').click();
    }

    // --- Edit modal wiring ---
    const btnEdit = document.getElementById('btnEditPatient');
    const modal = document.getElementById('editModal');

    function openEditModal(patient){
        const form = document.getElementById('editPatientForm');
        form.patient_no = patient.patient_no;
        form.elements['first_name'].value = patient.first_name || '';
        form.elements['last_name'].value = patient.last_name || '';
        form.elements['middle_name'].value = patient.middle_name || '';
        form.elements['date_of_birth'].value = patient.date_of_birth ? patient.date_of_birth.split(' ')[0] : '';
        form.elements['province'].value = patient.province || '';
        form.elements['city'].value = patient.city || '';
        form.elements['barangay'].value = patient.barangay || '';
        form.elements['nationality'].value = patient.nationality || '';
        form.elements['room_no'].value = patient.room_no || '';
        form.elements['admission_type'].value = patient.admission_type || '';
        form.elements['service'].value = patient.service || '';
        form.elements['doctor_name'].value = patient.doctor_name || '';
        form.elements['doctor_type'].value = patient.doctor_type || '';
        form.elements['admission_diagnosis'].value = patient.admission_diagnosis || '';
        
        // Set the admission diagnosis description if available
        if (form.elements['admission_diagnosis_description']) {
            form.elements['admission_diagnosis_description'].value = patient.admission_diagnosis_description || '';
        }
        
        modal.classList.add('open');
        modal.classList.add('show');
    }

    function closeModal(){ 
        modal.classList.remove('open'); 
        modal.classList.remove('show'); 
    }
    modal.querySelectorAll('.modal-close').forEach(b => b.addEventListener('click', closeModal));

    // Save button sends PUT to update
    document.getElementById('savePatientBtn').addEventListener('click', function(e){
        e.preventDefault(); const form = document.getElementById('editPatientForm');
        const patient_no = form.patient_no;
        if(!patient_no) return alert('No patient selected');
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
                if(j.ok){ location.reload(); return; }
                throw new Error('Update failed: ' + (j.message || JSON.stringify(j)));
            }
            // non-json but ok response
            location.reload();
        }).catch(e=>{ console.error(e); alert('Update failed: ' + e.message); });
    });

    // wire edit button to open modal with currently selected patient
    btnEdit.addEventListener('click', function(){
        const patientNo = document.getElementById('md-patient_no').textContent;
        if(!patientNo || patientNo === '-') return alert('No patient selected');
        // find the row with that patient_no
        const row = Array.from(rows).find(r => r.querySelector('.col-no')?.textContent.trim() === patientNo.toString());
        if(!row) return alert('Patient not found');
        try{ const patient = JSON.parse(row.getAttribute('data-patient')); openEditModal(patient); }catch(e){ console.error(e); alert('Failed to open edit modal'); }
    });
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
            alert('Lab test request submitted successfully!');
            closeLabRequestModal();
        } else {
            alert('Error submitting request. Please try again.');
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

@endsection