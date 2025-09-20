@extends('layouts.app')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

<link rel="stylesheet" href="{{ url('css/nursecss/nurse_patients.css') }}">

<div class="patients-grid">
    <div class="list-column">
        <div class="nurse-card">
            <div class="patients-header">
                <h3>Patients</h3>
                <form method="GET" class="patients-search" style="margin-left:auto;">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search name or patient no" class="search-input">
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

                <div class="pagination-wrap">
                    {{ $patients->links() }}
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

                <div style="margin-top:12px;text-align:right;">
                    <a href="#" id="detailsViewFull" class="btn secondary">Open Full</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lab Request Modal -->
<div id="labRequestModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeLabRequestModal()">&times;</span>
        <h3>Request Lab Test</h3>
        <form id="labRequestForm">
            <input type="hidden" id="requestPatientId" name="patient_id">
            
            <div class="form-group">
                <label>Patient:</label>
                <p id="requestPatientInfo" class="patient-info-display"></p>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="testCategory">Test Category: *</label>
                    <select id="testCategory" name="test_category" required onchange="updateTestOptions()">
                        <option value="">Select category</option>
                        <option value="laboratory">Laboratory Tests</option>
                        <option value="xray">X-Ray Procedures</option>
                        <option value="ultrasound">Ultrasound</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="specificTest">Specific Test/Procedure: *</label>
                    <select id="specificTest" name="specific_test" required disabled>
                        <option value="">Select test first</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="additionalTests">Additional Tests/Notes:</label>
                <textarea id="additionalTests" name="additional_tests" rows="3" 
                          placeholder="e.g., Additional lab work, special instructions, or multiple tests"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="priority">Priority: *</label>
                    <select id="priority" name="priority" required>
                        <option value="normal">Normal</option>
                        <option value="urgent">Urgent</option>
                        <option value="stat">STAT</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="scheduledDate">Preferred Date:</label>
                    <input type="date" id="scheduledDate" name="scheduled_date" min="{{ date('Y-m-d') }}">
                </div>
            </div>
            
            <div class="form-group">
                <label for="notes">Clinical Notes:</label>
                <textarea id="notes" name="notes" rows="2" 
                          placeholder="Clinical indications, symptoms, or special instructions for the technician"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn cancel-btn" onclick="closeLabRequestModal()">Cancel</button>
                <button type="submit" class="btn submit-btn">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
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
                    btnFull.href = `/nurse/patients/${encodeURIComponent(patient.patient_no)}`;
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