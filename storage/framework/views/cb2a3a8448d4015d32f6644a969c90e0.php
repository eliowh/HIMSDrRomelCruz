<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Patient Records Management</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/admincss/admin.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
</head>
<body>
    <?php
        $adminName = auth()->user()->name ?? 'Admin';
    ?>
    <?php echo $__env->make('admin.admin_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="admin-layout">
        <?php echo $__env->make('admin.admin_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-content">
            <h2>Patient Records Management</h2>
            
            <!-- Controls Row -->
            <div class="controls-row">
                <div class="filter-search-controls">
                    <select id="statusFilter" class="role-select">
                        <option value="">All Status</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="discharged" <?php echo e(request('status') == 'discharged' ? 'selected' : ''); ?>>Discharged</option>
                        <option value="deceased" <?php echo e(request('status') == 'deceased' ? 'selected' : ''); ?>>Deceased</option>
                    </select>
                    <input type="text" id="searchInput" placeholder="Search by patient name or patient number..." class="search-input" value="<?php echo e(request('q')); ?>">
                    <button id="searchButton" class="search-btn">Search</button>
                    <?php if(request('q') || request('status')): ?>
                        <button id="clearButton" class="clear-btn">Clear</button>
                    <?php endif; ?>
                </div>
            </div>

            <div class="admin-card">
                <!-- Patients Table -->
                <table class="admin-table" id="patientsTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="patient_no">
                                Patient No
                                <?php if(request('sort') == 'patient_no'): ?>
                                    <span class="sort-indicator <?php echo e(request('direction') == 'asc' ? 'asc' : 'desc'); ?>">
                                        <?php echo e(request('direction') == 'asc' ? '↑' : '↓'); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="sort-indicator">↕</span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-sort="first_name">
                                Patient Name
                                <?php if(request('sort') == 'first_name'): ?>
                                    <span class="sort-indicator <?php echo e(request('direction') == 'asc' ? 'asc' : 'desc'); ?>">
                                        <?php echo e(request('direction') == 'asc' ? '↑' : '↓'); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="sort-indicator">↕</span>
                                <?php endif; ?>
                            </th>
                            <th>DOB / Age</th>
                            <th class="sortable" data-sort="contact_number">
                                Contact Number
                                <?php if(request('sort') == 'contact_number'): ?>
                                    <span class="sort-indicator <?php echo e(request('direction') == 'asc' ? 'asc' : 'desc'); ?>">
                                        <?php echo e(request('direction') == 'asc' ? '↑' : '↓'); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="sort-indicator">↕</span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-sort="status">
                                Status
                                <?php if(request('sort') == 'status'): ?>
                                    <span class="sort-indicator <?php echo e(request('direction') == 'asc' ? 'asc' : 'desc'); ?>">
                                        <?php echo e(request('direction') == 'asc' ? '↑' : '↓'); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="sort-indicator">↕</span>
                                <?php endif; ?>
                            </th>
                            <th class="sortable" data-sort="created_at">
                                Admission Date
                                <?php if(request('sort') == 'created_at' || !request('sort')): ?>
                                    <span class="sort-indicator <?php echo e(request('direction') == 'asc' ? 'asc' : 'desc'); ?>">
                                        <?php echo e(request('direction') == 'asc' ? '↑' : '↓'); ?>

                                    </span>
                                <?php else: ?>
                                    <span class="sort-indicator">↕</span>
                                <?php endif; ?>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr data-status="<?php echo e(strtolower($patient->status ?? 'active')); ?>" data-name="<?php echo e(strtolower(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '') . ' ' . ($patient->patient_no ?? ''))); ?>">
                            <td><?php echo e($patient->patient_no ?? 'N/A'); ?></td>
                            <td><?php echo e(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')); ?></td>
                            <td>
                                <?php
                                    $dobStr = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : 'N/A';
                                    $age = 'N/A';
                                    if ($patient->date_of_birth) {
                                        $dob = \Carbon\Carbon::parse($patient->date_of_birth);
                                        $ageYears = intval($dob->diffInYears(now()));
                                        $age = $ageYears . ' years';
                                    }
                                ?>
                                <?php echo e($dobStr); ?><br>
                                <small class="text-muted"><?php echo e($age); ?></small>
                            </td>
                            <td><?php echo e($patient->contact_number ?? ''); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo e(strtolower($patient->status ?? 'active')); ?>">
                                    <?php echo e(ucfirst($patient->status ?? 'active')); ?>

                                </span>
                            </td>
                            <td><?php echo e($patient->created_at ? \Carbon\Carbon::parse($patient->created_at)->format('M d, Y') : 'N/A'); ?></td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn" onclick="toggleDropdown(<?php echo e($patient->id ?? $loop->index); ?>)">
                                        <span>⋯</span>
                                    </button>
                                    <div class="dropdown-content" id="dropdown-<?php echo e($patient->id ?? $loop->index); ?>">
                                        <a href="#" onclick="viewPatient(<?php echo e($patient->id ?? $loop->index); ?>)">View Details</a>
                                        <a href="#" onclick="updatePatientStatus(<?php echo e($patient->id ?? $loop->index); ?>, 'active')" class="activate-action">Mark Active</a>
                                        <a href="#" onclick="updatePatientStatus(<?php echo e($patient->id ?? $loop->index); ?>, 'discharged')" class="discharge-action">Mark Discharged</a>
                                        <a href="#" onclick="updatePatientStatus(<?php echo e($patient->id ?? $loop->index); ?>, 'deceased')" class="deceased-action">Mark Deceased</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if($patients->count() == 0): ?>
                        <tr class="no-results">
                            <td colspan="7" style="text-align: center; color: #666; padding: 20px;">
                                <?php if(request('q') || request('status')): ?>
                                    No patients found matching your search criteria.
                                <?php else: ?>
                                    No patients found.
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="pagination-wrapper">
                <?php if(isset($patients) && method_exists($patients, 'hasPages') && $patients->hasPages()): ?>
                    <?php echo $__env->make('components.custom-pagination', ['paginator' => $patients], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Patient Details Modal -->
    <div id="patientDetailsModal" class="patient-modal" style="display: none;">
        <div class="patient-modal-content">
            <div class="patient-modal-header">
                <h3>Patient Details</h3>
                <button class="patient-modal-close" onclick="closePatientDetailsModal()">&times;</button>
            </div>
            
            <div id="patientDetailsContent" class="patient-modal-body">
                <!-- Patient details form will be loaded here -->
            </div>
        </div>
    </div>

    <script>
    function openPatientDetailsModal() {
        const modal = document.getElementById('patientDetailsModal');
        if (modal) {
            modal.style.display = 'flex';
        } else {
            adminError('Patient details modal not found. Please refresh the page.');
        }
    }
    
    function closePatientDetailsModal() {
        const modal = document.getElementById('patientDetailsModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }

    // Add click-outside-to-close and ESC key functionality for patient modal
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('patientDetailsModal');
        if (modal) {
            // Close modal when clicking outside
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closePatientDetailsModal();
                }
            });
        }
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.style.display === 'flex') {
                closePatientDetailsModal();
            }
        });
    });

    // Filter and Search Functionality - Server-side search for cross-page results
    const statusFilter = document.getElementById('statusFilter');
    const searchInput = document.getElementById('searchInput');
    const searchButton = document.getElementById('searchButton');
    const clearButton = document.getElementById('clearButton');

    function performSearch() {
        const searchTerm = searchInput.value.trim();
        const selectedStatus = statusFilter.value;
        
        // Build URL with search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('page'); // Reset to page 1 when searching
        
        if (searchTerm) {
            url.searchParams.set('q', searchTerm);
        } else {
            url.searchParams.delete('q');
        }
        
        if (selectedStatus) {
            url.searchParams.set('status', selectedStatus);
        } else {
            url.searchParams.delete('status');
        }
        
        // Redirect to perform server-side search
        window.location.href = url.toString();
    }

    function clearSearch() {
        // Remove all search parameters
        const url = new URL(window.location.href);
        url.searchParams.delete('q');
        url.searchParams.delete('status');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }

    // Event listeners
    statusFilter.addEventListener('change', performSearch);
    searchButton.addEventListener('click', performSearch);
    
    if (clearButton) {
        clearButton.addEventListener('click', clearSearch);
    }
    
    // Allow Enter key to trigger search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Sorting Functionality
    function sortTable(column) {
        const url = new URL(window.location.href);
        const currentSort = url.searchParams.get('sort');
        const currentDirection = url.searchParams.get('direction');
        
        // If clicking the same column, toggle direction
        let newDirection = 'asc';
        if (currentSort === column && currentDirection === 'asc') {
            newDirection = 'desc';
        }
        
        // Set sort parameters
        url.searchParams.set('sort', column);
        url.searchParams.set('direction', newDirection);
        url.searchParams.delete('page'); // Reset to page 1 when sorting
        
        // Redirect with new sort parameters
        window.location.href = url.toString();
    }

    // Add click event listeners to sortable headers
    document.querySelectorAll('.sortable').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const sortColumn = this.getAttribute('data-sort');
            sortTable(sortColumn);
        });
    });

    // Dropdown Actions
    function toggleDropdown(patientId) {
        const dropdown = document.getElementById(`dropdown-${patientId}`);
        
        document.querySelectorAll('.dropdown-content').forEach(dd => {
            if (dd.id !== `dropdown-${patientId}`) {
                dd.classList.remove('show');
            }
        });
        
        dropdown.classList.toggle('show');
    }

    async function viewPatient(patientId) {
        document.getElementById(`dropdown-${patientId}`).classList.remove('show');
        
        console.log('Fetching patient details for ID:', patientId);
        
        try {
            const response = await fetch(`/admin/patients/${patientId}/details`);
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                console.error('HTTP error:', response.status, response.statusText);
                adminError(`HTTP Error: ${response.status} - ${response.statusText}`);
                return;
            }
            
            const result = await response.json();
            console.log('Response data:', result);
            
            if (result.success) {
                document.getElementById('patientDetailsContent').innerHTML = result.html;
                openPatientDetailsModal();
            } else {
                console.error('Server error:', result);
                adminError('Error loading patient details: ' + result.message);
            }
        } catch (error) {
            console.error('JavaScript error:', error);
            adminError('Network or parsing error: ' + error.message);
        }
    }

    async function updatePatientStatus(patientId, newStatus) {
        document.getElementById(`dropdown-${patientId}`).classList.remove('show');
        
        const statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        adminConfirm(
            `Are you sure you want to mark this patient as ${statusText}?`,
            'Confirm Status Change',
            () => performPatientStatusUpdate(patientId, newStatus, statusText),
            () => console.log('Patient status update cancelled')
        );
        return;
    }

    async function performPatientStatusUpdate(patientId, newStatus, statusText) {
        
        try {
            const response = await fetch(`/admin/patients/${patientId}/status`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });
            
            const result = await response.json();
            
            if (result.success) {
                adminSuccess(`Patient status updated to ${statusText} successfully!`);
                location.reload();
            } else {
                adminError('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            adminError('An error occurred while updating the patient status.');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.matches('.action-btn') && !event.target.matches('.action-btn span')) {
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                dropdown.classList.remove('show');
            });
        }
    });
    </script>

    <style>
    /* Patient Details Modal Specific Styling */
    .patient-details-modal {
        max-width: 90vw;
        max-height: 90vh;
        width: 1000px;
        overflow: hidden;
    }
    
    .patient-details-modal .sign {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px 25px;
        margin: -20px -25px 20px -25px;
        font-size: 18px;
        font-weight: 600;
    }
    
    @media (max-width: 768px) {
        .patient-details-modal {
            width: 95vw;
            max-height: 95vh;
        }
    }
    </style>

    <script>
    // Patient modal button functions
    window.savePatientData = function() {
        console.log("Save button clicked");
        const form = document.getElementById("patientForm");
        if (!form) {
            console.error("Form not found");
            adminError("Form not found");
            return;
        }
        
        const formData = new FormData(form);
        const patientId = formData.get("patient_id");
        const saveBtn = document.querySelector(".save-btn");
        
        console.log("Patient ID:", patientId);
        
        if (!patientId) {
            console.error("Patient ID not found");
            adminError("Patient ID not found");
            return;
        }
        
        // Show loading state
        const originalText = saveBtn.textContent;
        saveBtn.textContent = "Saving...";
        saveBtn.disabled = true;
        
        fetch(`/admin/patients/${patientId}/update`, {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
        .then(response => {
            console.log("Response status:", response.status);
            return response.json();
        })
        .then(data => {
            console.log("Response data:", data);
            if (data.success) {
                adminSuccess("Patient updated successfully!");
                closePatientDetailsModal();
                // Refresh the page to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                adminError("Error: " + (data.message || "Unknown error"));
            }
        })
        .catch(error => {
            console.error("JavaScript error:", error);
            adminError("An error occurred while saving: " + error.message);
        })
        .finally(() => {
            saveBtn.textContent = originalText;
            saveBtn.disabled = false;
        });
    };
    
    window.closePatientDetailsModal = function() {
        console.log("Close button clicked");
        const modal = document.getElementById("patientDetailsModal");
        if (modal) {
            modal.style.display = "none";
        } else {
            console.error("Modal not found");
        }
    };

    // Compatibility aliases
    window.savePatient = window.savePatientData;
    window.closeModal = window.closePatientDetailsModal;
    </script>

    <?php echo $__env->make('admin.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\admin\admin_patients.blade.php ENDPATH**/ ?>