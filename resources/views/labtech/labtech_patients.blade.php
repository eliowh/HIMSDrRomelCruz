<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link rel="stylesheet" href="{{ url('css/labtech.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @php
        $labtechName = auth()->check() ? auth()->user()->name : 'Lab Technician';
        $patients = $patients ?? collect();
        $q = $q ?? '';
    @endphp
    @include('labtech.labtech_header')

    <div class="labtech-layout">
        @include('labtech.labtech_sidebar')

        <main class="main-content">
            <h2>Patient Records</h2>
            
            <div class="patients-grid">
                <div class="list-column">
                    <div class="labtech-card">
                        <div class="patients-header">
                            <h3>Patients</h3>
                            <form method="GET" class="patients-search">
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
                    <div class="labtech-card details-card" id="detailsCard">
                        <div class="patients-header">
                            <h3>Patient Details</h3>
                        </div>

                        <div class="details-empty" id="detailsEmpty">Select a patient to view details.</div>

                        <div class="details-content hidden" id="detailsContent">
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
                            
                            <div class="patient-actions">
                                <button id="viewTestHistoryBtn" class="btn history-btn" type="button" disabled>
                                    <i class="fas fa-flask"></i> View Test History
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>  
            <div class="pagination-wrapper">
                {{ $patients->links('components.custom-pagination') }}
            </div>          
        </main>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @include('labtech.modals.test_history_modal')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const table = document.getElementById('patientsTable');
            const rows = table ? table.querySelectorAll('.patient-row') : [];
            const detailsCard = document.getElementById('detailsCard');
            const detailsEmpty = document.getElementById('detailsEmpty');
            const detailsContent = document.getElementById('detailsContent');
            const testHistoryModal = document.getElementById('testHistoryModal');
            const viewTestHistoryBtn = document.getElementById('viewTestHistoryBtn');
            let currentPatient = null;

            function or(v){ return v===null||v===undefined||v==='' ? '-' : v; }

            // Function to update the test history button with count badge
            function updateTestHistoryButton(testCount) {
                const btn = document.getElementById('viewTestHistoryBtn');
                
                if (testCount > 0) {
                    btn.innerHTML = `
                        <i class="fas fa-flask"></i> View Test History 
                        <span class="test-count-badge">${testCount}</span>
                    `;
                    btn.classList.add('has-tests');
                } else {
                    btn.innerHTML = `<i class="fas fa-flask"></i> View Test History`;
                    btn.classList.remove('has-tests');
                }
            }
            
            function renderPatient(patient){
                currentPatient = patient; // Store the current patient
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
                
                // Enable the view test history button now that we have a patient selected
                viewTestHistoryBtn.disabled = false;
                
                // Check for test history when a patient is selected
                if (patient.id) {
                    fetch(`/labtech/patients/${patient.id}/test-history`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.tests) {
                            updateTestHistoryButton(data.tests.length);
                        } else {
                            updateTestHistoryButton(0);
                        }
                    })
                    .catch(() => {
                        updateTestHistoryButton(0);
                    });
                }
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
                        detailsEmpty.classList.add('hidden');
                        detailsContent.classList.remove('hidden');
                        renderPatient(patient);
                    } catch(e){
                        console.error('Invalid patient JSON', e);
                    }
                });
            });

            // optionally auto-select first row
            if (rows.length && !document.querySelector('.patient-row.active')) {
                rows[0].querySelector('.js-open-patient').click();
            }
            
            // Test History Modal Functions
            function openTestHistoryModal() {
                if (!currentPatient) return;
                
                // Update the modal patient information
                document.getElementById('history-patient-name').textContent = 
                    [currentPatient.last_name, currentPatient.first_name, currentPatient.middle_name]
                        .filter(Boolean).join(', ');
                document.getElementById('history-patient-no').textContent = 
                    `Patient No: ${or(currentPatient.patient_no)}`;
                
                // Show loading state
                document.getElementById('testHistoryLoading').style.display = 'flex';
                document.getElementById('testHistoryEmpty').style.display = 'none';
                document.getElementById('testHistoryList').style.display = 'none';
                
                // Show the modal
                testHistoryModal.classList.add('show');
                
                // Fetch test history from the server
                fetchPatientTestHistory(currentPatient.id);
            }
            
            function closeTestHistoryModal() {
                testHistoryModal.classList.remove('show');
            }
            
            function fetchPatientTestHistory(patientId) {
                fetch(`/labtech/patients/${patientId}/test-history`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    // Hide loading spinner
                    document.getElementById('testHistoryLoading').style.display = 'none';
                    
                    if (data.success) {
                        if (data.tests && data.tests.length > 0) {
                            renderTestHistory(data.tests);
                            document.getElementById('testHistoryList').style.display = 'block';
                            
                            // Update the test history button with count badge
                            updateTestHistoryButton(data.tests.length);
                        } else {
                            document.getElementById('testHistoryEmpty').style.display = 'flex';
                            updateTestHistoryButton(0);
                        }
                    } else {
                        document.getElementById('testHistoryEmpty').style.display = 'flex';
                        updateTestHistoryButton(0);
                        console.error("Error fetching test history:", data.message || "Unknown error");
                    }
                })
                .catch(error => {
                    document.getElementById('testHistoryLoading').style.display = 'none';
                    document.getElementById('testHistoryEmpty').style.display = 'flex';
                    console.error("Error fetching test history:", error);
                });
            }
            
            function renderTestHistory(tests) {
                const historyList = document.getElementById('testHistoryList');
                historyList.innerHTML = ''; // Clear existing content
                
                tests.forEach(test => {
                    const testCard = document.createElement('div');
                    testCard.className = 'test-history-item';
                    
                    // Format date
                    const requestDate = new Date(test.requested_at);
                    const formattedDate = requestDate.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                    
                    const formattedTime = requestDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    // Create status badge
                    const statusClass = `status-badge status-${test.status}`;
                    const statusLabel = test.status.replace('_', ' ').toUpperCase();
                    
                    testCard.innerHTML = `
                        <div class="test-header">
                            <div class="test-id">#${String(test.id).padStart(4, '0')}</div>
                            <span class="${statusClass}">${statusLabel}</span>
                        </div>
                        <div class="test-info">
                            <h4>${test.test_requested}</h4>
                            <p class="test-date">
                                <i class="fas fa-calendar-alt"></i> ${formattedDate} at ${formattedTime}
                            </p>
                            <p class="test-requester">
                                <i class="fas fa-user-md"></i> Requested by: ${test.requested_by ? test.requested_by.name : 'Unknown'}
                            </p>
                            ${test.priority ? `
                                <p class="test-priority">
                                    <span class="priority-badge priority-${test.priority}">${test.priority.toUpperCase()}</span>
                                </p>
                            ` : ''}
                            ${test.notes ? `
                                <div class="test-notes">
                                    <strong>Notes:</strong> ${test.notes}
                                </div>
                            ` : ''}
                        </div>
                        <div class="test-actions">
                            ${test.status === 'completed' && test.results_pdf_path ? `
                                <button class="btn view-pdf-btn" onclick="viewTestPdf(${test.id})">
                                    <i class="fas fa-file-pdf"></i> View Results
                                </button>
                            ` : ''}
                            <button class="btn view-btn" onclick="viewTestDetails(${test.id})">
                                Details
                            </button>
                        </div>
                    `;
                    
                    historyList.appendChild(testCard);
                });
            }
            
            // Function to view test PDF (to be implemented in the controller)
            function viewTestPdf(testId) {
                window.open(`/labtech/orders/view-pdf/${testId}`, '_blank');
            }
            
            // Function to view test details (to be implemented in the controller)
            function viewTestDetails(testId) {
                window.location.href = `/labtech/orders?highlight=${testId}`;
            }
            
            // Event listeners for the test history button and modal
            viewTestHistoryBtn.addEventListener('click', openTestHistoryModal);
            
            // Close modal when clicking the X button
            testHistoryModal.querySelector('.close').addEventListener('click', closeTestHistoryModal);
            
            // Close modal when clicking outside of it
            window.addEventListener('click', function(event) {
                if (event.target === testHistoryModal) {
                    closeTestHistoryModal();
                }
            });
        });
    </script>
</body>
</html>
