<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/labtechcss/labtech.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
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
        
        /* Test history styling */
        .test-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
        }
        
        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .test-header strong {
            color: #2c3e50;
            font-size: 14px;
        }
        
        .test-status {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-completed { background: #d4edda; color: #155724; }
        .status-in-progress { background: #d1ecf1; color: #0c5460; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .test-details {
            font-size: 12px;
            color: #666;
        }
        
        .test-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 6px;
        }
        
        .test-meta span {
            white-space: nowrap;
        }
        
        .test-notes {
            margin: 8px 0;
            padding: 8px;
            background: #e9ecef;
            border-radius: 4px;
            font-size: 11px;
        }
        
        .test-actions {
            margin-top: 8px;
            text-align: right;
        }
        
        .btn-pdf {
            background: #dc3545;
            color: white;
            border: none;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        
        .btn-pdf:hover {
            background: #c82333;
        }
        
        .loading-tests, .no-tests, .error-tests {
            padding: 15px;
            text-align: center;
            font-style: italic;
            color: #666;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .error-tests {
            color: #dc3545;
            background: #f8d7da;
        }
        
        .view-more-indicator {
            padding: 10px;
            text-align: center;
            background: #f8f9fa;
            border: 1px dashed #dee2e6;
            border-radius: 4px;
            margin-top: 10px;
        }
        
        .view-more-indicator .text-muted {
            color: #6c757d !important;
            font-size: 12px;
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
</head>
<body>
    <?php
        $labtechName = auth()->check() ? auth()->user()->name : 'Lab Technician';
        $patients = $patients ?? collect();
        $q = $q ?? '';
    ?>
    <?php echo $__env->make('labtech.labtech_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="labtech-layout">
        <?php echo $__env->make('labtech.labtech_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <h2>Patient Records</h2>
            
            <div class="patients-grid">
                <div class="list-column">
                    <div class="labtech-card">
                        <div class="patients-header">
                            <h3>Patients</h3>
                            <form method="GET" class="patients-search">
                                <input type="search" name="q" value="<?php echo e($q); ?>" placeholder="Search name or patient no" class="search-input">
                            </form>
                        </div>

                        <?php if(session('success')): ?>
                            <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                        <?php endif; ?>

                        <?php if($patients->count()): ?>
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
                                    <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr class="patient-row" data-patient='<?php echo json_encode($p, 15, 512) ?>'>
                                            <td class="col-no"><?php echo e($p->patient_no); ?></td>
                                            <td class="col-name"><?php echo e($p->last_name); ?>, <?php echo e($p->first_name); ?><?php echo e($p->middle_name ? ' '.$p->middle_name : ''); ?></td>
                                            <td class="col-dob">
                                                <?php echo e($p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-'); ?><br>
                                                <?php
                                                    $ageYears = $p->date_of_birth ? intval($p->date_of_birth->diffInYears(now())) : null;
                                                ?>
                                                <small class="text-muted"><?php echo e($ageYears !== null ? $ageYears.' years' : '-'); ?></small>
                                            </td>
                                            <td class="col-location"><?php echo e($p->barangay ? $p->barangay.',' : ''); ?> <?php echo e($p->city); ?>, <?php echo e($p->province); ?></td>
                                            <td class="col-actions">
                                                <button type="button" class="btn view-btn js-open-patient">View</button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>                            
                        <?php else: ?>
                            <div class="alert alert-info">No patients found.</div>
                        <?php endif; ?>
                    </div>                    
                </div>
                <div class="details-column">
                    <div class="labtech-card details-card" id="detailsCard">
                        <div class="patients-header">
                            <h3>Patient Details</h3>
                        </div>

                        <div class="details-empty" id="detailsEmpty">Select a patient to view details.</div>

                        <div class="details-content hidden" id="detailsContent">
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
                            
                            <!-- Test History Details Section -->
                            <div class="details-section" id="test-history-section" style="display:none;">
                                <h4 class="section-header">Test History Details</h4>
                                <div id="md-test-history">
                                    <div class="loading-tests">Loading test history...</div>
                                </div>
                            </div>
                            
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
                <?php echo e($patients->links('components.custom-pagination')); ?>

            </div>          
        </main>
    </div>

    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    
    <?php echo $__env->make('labtech.modals.test_history_modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('labtech.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
                
                // Format name properly
                const fullName = [
                    formatName(patient.last_name),
                    formatName(patient.first_name), 
                    formatName(patient.middle_name)
                ].filter(n => n && n !== '-').join(', ');
                document.getElementById('md-name').textContent = or(fullName);
                
                // Format sex
                document.getElementById('md-sex').textContent = patient.sex ? formatName(patient.sex) : '-';
                
                document.getElementById('md-dob').textContent = formatDate(patient.date_of_birth);
                
                // Compute age (years) from DOB
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
                const location = [
                    formatName(patient.barangay),
                    formatName(patient.city),
                    formatName(patient.province)
                ].filter(l => l && l !== '-').join(', ');
                document.getElementById('md-location').textContent = or(location);
                
                document.getElementById('md-nationality').textContent = formatName(patient.nationality);
                document.getElementById('md-room_no').textContent = or(patient.room_no);
                document.getElementById('md-admission_diagnosis').textContent = or(patient.admission_diagnosis);
                document.getElementById('md-admission_type').textContent = formatName(patient.admission_type);
                document.getElementById('md-service').textContent = formatName(patient.service);
                document.getElementById('md-doctor_name').textContent = formatName(patient.doctor_name);
                document.getElementById('md-doctor_type').textContent = formatName(patient.doctor_type);
                document.getElementById('md-created_at').textContent = formatDateTime(patient.created_at);
                
                // Load test history details
                loadTestHistoryDetails(patient.id);
                
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

            function loadTestHistoryDetails(patientId) {
                const testHistoryDiv = document.getElementById('md-test-history');
                const testHistorySection = document.getElementById('test-history-section');
                
                console.log('Loading test history for patient ID:', patientId);
                
                // Show loading state
                testHistoryDiv.innerHTML = '<div class="loading-tests">Loading test history...</div>';
                testHistorySection.style.display = 'block';
                
                // Fetch patient test history via API
                fetch(`/labtech/patients/${patientId}/test-history`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Test history loaded:', data);
                        
                        if (data.success && data.tests && data.tests.length > 0) {
                            // Limit display to first 3 tests
                            const displayTests = data.tests.slice(0, 3);
                            const hasMore = data.tests.length > 3;
                            
                            // Display test history
                            testHistoryDiv.innerHTML = displayTests.map(test => {
                                const testDate = test.requested_at ? formatDate(test.requested_at) : 'Unknown date';
                                const requestedBy = test.requested_by ? formatName(test.requested_by.name) : 'Unknown';
                                const labTech = test.lab_tech ? formatName(test.lab_tech.name) : 'Not assigned';
                                const status = test.status || 'pending';
                                const statusClass = getStatusClass(status);
                                const hasPdf = test.results_pdf_path ? true : false;
                                
                                return `
                                    <div class="test-item">
                                        <div class="test-header">
                                            <strong>${test.test_requested || 'Unknown Test'}</strong>
                                            <span class="test-status ${statusClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span>
                                        </div>
                                        <div class="test-details">
                                            <div class="test-meta">
                                                <span><strong>Date:</strong> ${testDate}</span>
                                                <span><strong>Requested by:</strong> ${requestedBy}</span>
                                                <span><strong>Lab Tech:</strong> ${labTech}</span>
                                                ${test.priority ? `<span><strong>Priority:</strong> ${formatName(test.priority)}</span>` : ''}
                                            </div>
                                            ${test.notes ? `<div class="test-notes"><strong>Notes:</strong> ${test.notes}</div>` : ''}
                                            ${hasPdf ? `
                                                <div class="test-actions">
                                                    <button onclick="viewTestPdf(${test.id})" class="btn btn-sm btn-pdf">
                                                        <i class="fas fa-file-pdf"></i> View PDF Results
                                                    </button>
                                                </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                `;
                            }).join('');
                            
                            // Add "view more" indicator if there are more than 3 tests
                            if (hasMore) {
                                testHistoryDiv.innerHTML += `
                                    <div class="view-more-indicator">
                                        <small class="text-muted">
                                            <i class="fas fa-ellipsis-h"></i> 
                                            ${data.tests.length - 3} more test(s) available in Test History
                                        </small>
                                    </div>
                                `;
                            }
                            
                            testHistorySection.style.display = 'block';
                        } else {
                            // No test history found
                            testHistoryDiv.innerHTML = '<div class="no-tests">No test history available</div>';
                            testHistorySection.style.display = 'block';
                        }
                    })
                    .catch(error => {
                        console.error('Error loading test history:', error);
                        testHistoryDiv.innerHTML = '<div class="error-tests">Failed to load test history</div>';
                        testHistorySection.style.display = 'block';
                    });
            }

            function getStatusClass(status) {
                switch(status) {
                    case 'completed': return 'status-completed';
                    case 'in_progress': return 'status-in-progress';
                    case 'cancelled': return 'status-cancelled';
                    default: return 'status-pending';
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
                
                // Add modal-open class to body to prevent layout shifts
                document.body.classList.add('modal-open');
                
                // Show the modal
                testHistoryModal.classList.add('show');
                
                // Fetch test history from the server
                fetchPatientTestHistory(currentPatient.id);
            }
            
            function closeTestHistoryModal() {
                testHistoryModal.classList.remove('show');
                // Remove modal-open class from body
                document.body.classList.remove('modal-open');
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
                            <button class="btn view-btn" onclick="viewTestDetails(${test.id})">
                                Details
                            </button>
                        </div>
                    `;
                    
                    historyList.appendChild(testCard);
                });
            }
            
            // Function to view test details - opens the lab orders page and highlights the specific order
            window.viewTestDetails = function(testId) {
                // Store the test ID to highlight in sessionStorage
                sessionStorage.setItem('highlightTestId', testId);
                // Navigate to lab orders page
                window.location.href = '<?php echo e(route("labtech.orders")); ?>';
            }

                        // Function to view test PDF results
            window.viewTestPdf = function(testId) {
                // First check if the PDF exists
                fetch(`/labtech/orders/${testId}/check-pdf`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.pdf_exists) {
                        // Open PDF in new tab - GET route doesn't need CSRF
                        window.open(`/labtech/orders/${testId}/view-pdf`, '_blank');
                    } else {
                        showLabtechWarning('PDF results are not available for this test yet.', 'PDF Not Available');
                    }
                })
                .catch(error => {
                    console.error('Error checking PDF:', error);
                    showLabtechError('Error checking PDF availability. Please try again.', 'Error');
                });
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
            
            // Make closeTestHistoryModal globally accessible for the modal close button
            window.closeTestHistoryModal = closeTestHistoryModal;
        });
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\labtech\labtech_patients.blade.php ENDPATH**/ ?>