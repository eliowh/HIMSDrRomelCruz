<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="{{asset('css/doctorcss/doctor.css')}}">
</head>
<body>
    @php
        $doctorName = auth()->user()->name ?? 'Doctor';
    @endphp
    @include('doctor.doctor_header')
    <div class="doctor-layout">
        @include('doctor.doctor_sidebar')
        <div class="main-content">
            <h2>Welcome, Dr. {{ $doctorName }}!</h2>
            
            <!-- Dashboard Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3>Today's Patients</h3>
                        <p class="stat-number">8</p>
                        <p class="stat-subtitle">Scheduled appointments</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏰</div>
                    <div class="stat-content">
                        <h3>Next Appointment</h3>
                        <p class="stat-number">2:30 PM</p>
                        <p class="stat-subtitle">John Doe - Checkup</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <h3>Pending Reports</h3>
                        <p class="stat-number">5</p>
                        <p class="stat-subtitle">Lab results to review</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <h3>This Week</h3>
                        <p class="stat-number">42</p>
                        <p class="stat-subtitle">Patients treated</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="doctor-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="/doctor/appointments" class="action-btn primary">
                        <span class="btn-icon">📅</span>
                        View Appointments
                    </a>
                    <button class="action-btn secondary" onclick="showNewPatientModal()">
                        <span class="btn-icon">➕</span>
                        Add New Patient
                    </button>
                    <a href="/doctor/patients" class="action-btn secondary">
                        <span class="btn-icon">👥</span>
                        Patient Records
                    </a>
                    <button class="action-btn secondary" onclick="showPrescriptionModal()">
                        <span class="btn-icon">💊</span>
                        Write Prescription
                    </button>
                </div>
            </div>

            <!-- Today's Schedule Card -->
            <div class="doctor-card">
                <h3>Today's Schedule</h3>
                <div class="schedule-list">
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <span class="time">9:00 AM</span>
                            <span class="duration">30 min</span>
                        </div>
                        <div class="schedule-content">
                            <h4>Sarah Johnson</h4>
                            <p class="appointment-type">General Checkup</p>
                            <p class="patient-info">Age: 34 • ID: P001</p>
                        </div>
                        <div class="schedule-actions">
                            <button class="btn-small primary">View</button>
                            <button class="btn-small">Reschedule</button>
                        </div>
                    </div>
                    
                    <div class="schedule-item current">
                        <div class="schedule-time">
                            <span class="time">10:30 AM</span>
                            <span class="duration">45 min</span>
                        </div>
                        <div class="schedule-content">
                            <h4>Michael Chen</h4>
                            <p class="appointment-type">Follow-up Visit</p>
                            <p class="patient-info">Age: 28 • ID: P002</p>
                        </div>
                        <div class="schedule-actions">
                            <button class="btn-small current">In Progress</button>
                        </div>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <span class="time">2:30 PM</span>
                            <span class="duration">30 min</span>
                        </div>
                        <div class="schedule-content">
                            <h4>John Doe</h4>
                            <p class="appointment-type">Consultation</p>
                            <p class="patient-info">Age: 45 • ID: P003</p>
                        </div>
                        <div class="schedule-actions">
                            <button class="btn-small primary">View</button>
                            <button class="btn-small">Reschedule</button>
                        </div>
                    </div>
                    
                    <div class="schedule-item">
                        <div class="schedule-time">
                            <span class="time">4:00 PM</span>
                            <span class="duration">30 min</span>
                        </div>
                        <div class="schedule-content">
                            <h4>Emma Wilson</h4>
                            <p class="appointment-type">Lab Results Review</p>
                            <p class="patient-info">Age: 52 • ID: P004</p>
                        </div>
                        <div class="schedule-actions">
                            <button class="btn-small primary">View</button>
                            <button class="btn-small">Reschedule</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity & Notifications -->
            <div class="two-column-layout">
                <div class="doctor-card">
                    <h3>Recent Activity</h3>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon">📋</div>
                            <div class="activity-content">
                                <p class="activity-title">Lab results received</p>
                                <p class="activity-detail">Blood test for Patient #P005</p>
                                <p class="activity-time">2 hours ago</p>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">💊</div>
                            <div class="activity-content">
                                <p class="activity-title">Prescription sent</p>
                                <p class="activity-detail">Antibiotics for Maria Garcia</p>
                                <p class="activity-time">4 hours ago</p>
                            </div>
                        </div>
                        
                        <div class="activity-item">
                            <div class="activity-icon">📅</div>
                            <div class="activity-content">
                                <p class="activity-title">Appointment scheduled</p>
                                <p class="activity-detail">New patient - David Kim</p>
                                <p class="activity-time">Yesterday</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="doctor-card">
                    <h3>Important Notifications</h3>
                    <div class="notifications-list">
                        <div class="notification-item urgent">
                            <div class="notification-icon">🚨</div>
                            <div class="notification-content">
                                <p class="notification-title">Critical Lab Result</p>
                                <p class="notification-detail">Patient #P007 requires immediate attention</p>
                                <button class="btn-small urgent">Review Now</button>
                            </div>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-icon">📞</div>
                            <div class="notification-content">
                                <p class="notification-title">Patient Callback Request</p>
                                <p class="notification-detail">Mrs. Anderson needs consultation</p>
                                <button class="btn-small">Contact</button>
                            </div>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-icon">📄</div>
                            <div class="notification-content">
                                <p class="notification-title">Medical Records Update</p>
                                <p class="notification-detail">3 patient files updated</p>
                                <button class="btn-small">View</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Summary Card -->
            <div class="doctor-card">
                <h3>Patient Overview</h3>
                <div class="patient-overview">
                    <div class="overview-item">
                        <span class="overview-label">Total Active Patients:</span>
                        <span class="overview-value">127</span>
                    </div>
                    <div class="overview-item">
                        <span class="overview-label">New Patients This Month:</span>
                        <span class="overview-value">15</span>
                    </div>
                    <div class="overview-item">
                        <span class="overview-label">Follow-up Required:</span>
                        <span class="overview-value">8</span>
                    </div>
                    <div class="overview-item">
                        <span class="overview-label">Prescriptions This Week:</span>
                        <span class="overview-value">23</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick Action Modal Functions
        function showNewPatientModal() {
            // This would open a modal for adding new patients
            // For now, we'll show an alert - you can implement the actual modal later
            alert('New Patient Modal - This will be implemented with a proper modal form');
        }

        function showPrescriptionModal() {
            // This would open a modal for writing prescriptions
            alert('Prescription Modal - This will be implemented with a proper prescription form');
        }

        // Dashboard interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for buttons
            const viewButtons = document.querySelectorAll('.btn-small.primary');
            viewButtons.forEach(button => {
                if (button.textContent.trim() === 'View') {
                    button.addEventListener('click', function() {
                        // This would navigate to patient details
                        console.log('View patient details');
                    });
                }
            });

            // Add click handlers for reschedule buttons
            const rescheduleButtons = document.querySelectorAll('.btn-small');
            rescheduleButtons.forEach(button => {
                if (button.textContent.trim() === 'Reschedule') {
                    button.addEventListener('click', function() {
                        // This would open reschedule modal
                        console.log('Reschedule appointment');
                    });
                }
            });

            // Add handlers for notification action buttons
            const notificationButtons = document.querySelectorAll('.notification-item button');
            notificationButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    console.log(`Notification action: ${action}`);
                    
                    if (action === 'Review Now') {
                        // Handle critical lab result review
                        alert('Opening critical lab results...');
                    } else if (action === 'Contact') {
                        // Handle patient callback
                        alert('Initiating patient contact...');
                    } else if (action === 'View') {
                        // Handle medical records view
                        alert('Opening medical records...');
                    }
                });
            });

            // Auto-refresh dashboard data every 5 minutes
            setInterval(function() {
                // In a real application, this would fetch updated data
                console.log('Refreshing dashboard data...');
                // You can implement AJAX calls here to update the dashboard
            }, 300000); // 5 minutes

            // Add hover effects and animations
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // Function to update time-sensitive elements
        function updateDashboardTime() {
            const now = new Date();
            const timeElements = document.querySelectorAll('.activity-time');
            
            // Update relative time displays
            timeElements.forEach(element => {
                // This is a simplified example - you'd want to implement proper relative time
                if (element.textContent === '2 hours ago') {
                    // Update based on actual time differences
                }
            });
        }

        // Update time displays every minute
        setInterval(updateDashboardTime, 60000);
    </script>

</body>
</html>
