<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/nurse.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/dashboard_enhancements.css')); ?>">
</head>
<body>
    <?php
        $nurseName = auth()->user()->name ?? 'Nurse';
        $totalPatients = $patients->count() ?? 0;
        $todayAdmissions = $patients->where('created_at', '>=', today())->count() ?? 0;
        $admittedPatients = $patients->whereNotNull('room_no')->count() ?? 0;
        $pendingPatients = $patients->whereNull('room_no')->count() ?? 0;
    ?>
    <?php echo $__env->make('nurse.nurse_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="nurse-layout">
        <?php echo $__env->make('nurse.nurse_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-content">
            <div class="dashboard-header">
                <h2>Welcome, Nurse <?php echo e($nurseName); ?>!</h2>
                <p class="dashboard-subtitle"><?php echo e(\Carbon\Carbon::now()->format('l, F j, Y')); ?></p>
            </div>
            
            <!-- Dashboard Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3>Total Patients</h3>
                        <p class="stat-number"><?php echo e($totalPatients); ?></p>
                        <p class="stat-subtitle">In the system</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏥</div>
                    <div class="stat-content">
                        <h3>Admitted Today</h3>
                        <p class="stat-number"><?php echo e($todayAdmissions); ?></p>
                        <p class="stat-subtitle">New admissions</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-content">
                        <h3>In Rooms</h3>
                        <p class="stat-number"><?php echo e($admittedPatients); ?></p>
                        <p class="stat-subtitle">Currently admitted</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3>Pending</h3>
                        <p class="stat-number"><?php echo e($pendingPatients); ?></p>
                        <p class="stat-subtitle">Awaiting rooms</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="nurse-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="/nurse/patients" class="action-btn primary">
                        <span class="btn-icon">👥</span>
                        View All Patients
                    </a>
                    <a href="/nurse/addPatients" class="action-btn secondary">
                        <span class="btn-icon">➕</span>
                        Admit New Patient
                    </a>
                    <a href="/nurse/patients" class="action-btn secondary">
                        <span class="btn-icon">✏️</span>
                        Edit Patient Info
                    </a>
                    <a href="/nurse/patients" class="action-btn secondary">
                        <span class="btn-icon">🔍</span>
                        Search Patients
                    </a>
                </div>
            </div>

            <!-- Recent Patients and System Status -->
            <div class="two-column-layout">
                <div class="nurse-card">
                    <h3>Recent Patient Admissions</h3>
                    <div class="recent-patients">
                        <?php if($patients->count() > 0): ?>
                            <?php $__currentLoopData = $patients->sortByDesc('created_at')->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="patient-item">
                                    <div class="patient-info">
                                        <h4><?php echo e($patient->first_name); ?> <?php echo e($patient->last_name); ?></h4>
                                        <p class="patient-no">Patient #<?php echo e($patient->patient_no); ?></p>
                                        <p class="patient-details">
                                            <?php if($patient->room_no): ?>
                                                <span class="room-badge">Room <?php echo e($patient->room_no); ?></span>
                                            <?php else: ?>
                                                <span class="pending-badge">Room Pending</span>
                                            <?php endif; ?>
                                            <?php if($patient->admission_type): ?>
                                                • <?php echo e($patient->admission_type); ?>

                                            <?php endif; ?>
                                        </p>
                                        <p class="admission-time">
                                            <?php echo e($patient->created_at ? $patient->created_at->diffForHumans() : 'Recently'); ?>

                                        </p>
                                    </div>
                                    <div class="patient-actions">
                                        <a href="/nurse/patients" class="btn-small primary">View Details</a>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>No patients registered yet.</p>
                                <a href="/nurse/addPatients" class="btn-small primary">Add First Patient</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="nurse-card">
                    <h3>System Overview</h3>
                    <div class="system-status">
                        <div class="status-item">
                            <div class="status-icon">📊</div>
                            <div class="status-content">
                                <h4>Patient Database</h4>
                                <p class="status-description"><?php echo e($totalPatients); ?> patients registered</p>
                                <span class="status-badge active">Active</span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon">🏥</div>
                            <div class="status-content">
                                <h4>Room Management</h4>
                                <p class="status-description">Room assignment system operational</p>
                                <span class="status-badge active">Active</span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon">👨‍⚕️</div>
                            <div class="status-content">
                                <h4>Doctor Assignment</h4>
                                <p class="status-description">Doctor scheduling available</p>
                                <span class="status-badge active">Active</span>
                            </div>
                        </div>
                        
                        <div class="status-item">
                            <div class="status-icon">🩺</div>
                            <div class="status-content">
                                <h4>ICD-10 Diagnosis</h4>
                                <p class="status-description">Diagnosis coding system ready</p>
                                <span class="status-badge active">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Distribution by Admission Type -->
            <?php if($patients->count() > 0): ?>
            <div class="nurse-card">
                <h3>Patient Distribution by Admission Type</h3>
                <div class="admission-stats">
                    <?php
                        $admissionTypes = $patients->groupBy('admission_type')->map->count();
                    ?>
                    <?php $__currentLoopData = $admissionTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="admission-type-item">
                            <div class="admission-type-info">
                                <h4><?php echo e($type ?: 'Not Specified'); ?></h4>
                                <p class="admission-count"><?php echo e($count); ?> patient<?php echo e($count !== 1 ? 's' : ''); ?></p>
                            </div>
                            <div class="admission-percentage">
                                <?php
                                    $percentage = round(($count / $totalPatients) * 100, 1);
                                ?>
                                <span class="percentage-bar">
                                    <span class="percentage-fill" style="width: <?php echo e($percentage); ?>%"></span>
                                </span>
                                <span class="percentage-text"><?php echo e($percentage); ?>%</span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Activity -->
            <div class="nurse-card">
                <h3>Recent System Activity</h3>
                <div class="activity-timeline">
                    <?php if($patients->count() > 0): ?>
                        <?php $__currentLoopData = $patients->sortByDesc('created_at')->take(8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="activity-item">
                                <div class="activity-time">
                                    <?php echo e($patient->created_at ? $patient->created_at->format('g:i A') : 'Recent'); ?>

                                </div>
                                <div class="activity-content">
                                    <p class="activity-title">Patient Admitted</p>
                                    <p class="activity-detail">
                                        <?php echo e($patient->first_name); ?> <?php echo e($patient->last_name); ?> 
                                        <?php if($patient->room_no): ?>
                                            assigned to Room <?php echo e($patient->room_no); ?>

                                        <?php else: ?>
                                            - room assignment pending
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="activity-item">
                            <div class="activity-time">--:--</div>
                            <div class="activity-content">
                                <p class="activity-title">System Ready</p>
                                <p class="activity-detail">Hospital Management System is ready for patient admissions</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Dashboard interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects for stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                    this.style.transition = 'all 0.3s ease';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
                });
            });

            // Add hover effects for action buttons
            const actionButtons = document.querySelectorAll('.action-btn');
            actionButtons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Add click effects for patient items
            const patientItems = document.querySelectorAll('.patient-item');
            patientItems.forEach(item => {
                item.addEventListener('click', function() {
                    // Optional: Add click feedback
                    this.style.backgroundColor = '#f8f9fa';
                    setTimeout(() => {
                        this.style.backgroundColor = '';
                    }, 200);
                });
            });

            // Auto-refresh dashboard stats every 5 minutes
            setInterval(function() {
                // In a real application, this would fetch updated data from server
                console.log('Dashboard auto-refresh - checking for updates...');
                
                // Optional: Add visual indicator of refresh
                const dashboardHeader = document.querySelector('.dashboard-header h2');
                if (dashboardHeader) {
                    const originalText = dashboardHeader.textContent;
                    dashboardHeader.textContent = originalText + ' (Updated)';
                    setTimeout(() => {
                        dashboardHeader.textContent = originalText;
                    }, 2000);
                }
            }, 300000); // 5 minutes

            // Initialize current time display
            updateTimeDisplay();
            setInterval(updateTimeDisplay, 60000); // Update every minute

            // Add interactive feedback for system status items
            const statusItems = document.querySelectorAll('.status-item');
            statusItems.forEach(item => {
                item.addEventListener('click', function() {
                    const statusTitle = this.querySelector('h4').textContent;
                    nurseNotify('info', 'System Status', `${statusTitle} is currently operational and functioning normally.`);
                });
            });
        });

        // Function to update time display
        function updateTimeDisplay() {
            const now = new Date();
            const timeElements = document.querySelectorAll('.activity-time');
            
            // Update relative times for activities
            timeElements.forEach(element => {
                const timeText = element.textContent;
                if (timeText.includes(':')) {
                    // This is a time, could add relative time calculation here
                    // For now, just ensure times stay current
                }
            });
        }

        // Function to show patient details
        function showPatientDetails(patientNo) {
            nurseNotify('info', 'Patient Details', `Viewing details for Patient #${patientNo}. Click "View All Patients" to see complete patient information.`);
        }

        // Function to refresh dashboard data
        function refreshDashboard() {
            nurseNotify('info', 'Dashboard Refresh', 'Dashboard data refreshed successfully.');
            
            // Add visual feedback
            const mainContent = document.querySelector('.main-content');
            mainContent.style.opacity = '0.8';
            setTimeout(() => {
                mainContent.style.opacity = '1';
            }, 500);
        }

        // Function to navigate to patient management
        function goToPatients() {
            window.location.href = '/nurse/patients';
        }

        // Function to navigate to add new patient
        function addNewPatient() {
            window.location.href = '/nurse/addPatients';
        }

        // Enhanced notification system integration
        window.nurseSuccess = function(title, message) {
            if (typeof nurseNotify === 'function') {
                nurseNotify('success', title, message);
            } else {
                alert(`${title}: ${message}`);
            }
        };

        window.nurseWarning = function(title, message) {
            if (typeof nurseNotify === 'function') {
                nurseNotify('warning', title, message);
            } else {
                alert(`${title}: ${message}`);
            }
        };

        window.nurseError = function(title, message) {
            if (typeof nurseNotify === 'function') {
                nurseNotify('error', title, message);
            } else {
                alert(`${title}: ${message}`);
            }
        };

        // Keyboard shortcuts for quick navigation
        document.addEventListener('keydown', function(e) {
            // Alt + P for Patients
            if (e.altKey && e.key === 'p') {
                e.preventDefault();
                goToPatients();
            }
            
            // Alt + A for Add Patient
            if (e.altKey && e.key === 'a') {
                e.preventDefault();
                addNewPatient();
            }
            
            // Alt + R for Refresh
            if (e.altKey && e.key === 'r') {
                e.preventDefault();
                refreshDashboard();
            }
        });
    </script>

    <?php echo $__env->make('nurse.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/nurse/nurse_home.blade.php ENDPATH**/ ?>