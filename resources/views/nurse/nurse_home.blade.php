<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
    <link rel="stylesheet" href="{{url('css/nursecss/nurse.css')}}">
</head>
<body>
    @php
        $nurseName = auth()->user()->name ?? 'Nurse';
    @endphp
    @include('nurse.nurse_header')
    <div class="nurse-layout">
        @include('nurse.nurse_sidebar')
        <div class="main-content">
            <h2>Welcome, Nurse {{ $nurseName }}!</h2>
            
            <!-- Dashboard Statistics Cards -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">🏥</div>
                    <div class="stat-content">
                        <h3>Patients Today</h3>
                        <p class="stat-number">12</p>
                        <p class="stat-subtitle">Under your care</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">💉</div>
                    <div class="stat-content">
                        <h3>Medications Due</h3>
                        <p class="stat-number">6</p>
                        <p class="stat-subtitle">Next 2 hours</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <h3>Vital Signs</h3>
                        <p class="stat-number">4</p>
                        <p class="stat-subtitle">Pending checks</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🚨</div>
                    <div class="stat-content">
                        <h3>Priority Alerts</h3>
                        <p class="stat-number">2</p>
                        <p class="stat-subtitle">Require attention</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="nurse-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="/nurse/patients" class="action-btn primary">
                        <span class="btn-icon">👥</span>
                        View Patients
                    </a>
                    <a href="/nurse/addPatients" class="action-btn secondary">
                        <span class="btn-icon">➕</span>
                        Add New Patient
                    </a>
                    <button class="action-btn secondary" onclick="nurseNotify('info', 'Lab Request', 'Feature coming soon - Request lab tests for patients')">
                        <span class="btn-icon">🧪</span>
                        Lab Requests
                    </button>
                    <button class="action-btn secondary" onclick="nurseNotify('info', 'Vital Signs', 'Feature coming soon - Record patient vital signs')">
                        <span class="btn-icon">🩺</span>
                        Vital Signs
                    </button>
                </div>
            </div>

            <!-- Priority Alerts Card -->
            <div class="nurse-card alert-card">
                <h3>🚨 Priority Alerts</h3>
                <div class="alert-list">
                    <div class="alert-item high-priority">
                        <div class="alert-content">
                            <h4>Room 205 - Mary Johnson</h4>
                            <p class="alert-type">High Blood Pressure Alert</p>
                            <p class="alert-detail">BP: 180/95 mmHg - Requires immediate attention</p>
                            <p class="alert-time">5 minutes ago</p>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-small urgent">Respond</button>
                            <button class="btn-small">Call Doctor</button>
                        </div>
                    </div>
                    
                    <div class="alert-item medium-priority">
                        <div class="alert-content">
                            <h4>Room 312 - Robert Davis</h4>
                            <p class="alert-type">Medication Overdue</p>
                            <p class="alert-detail">Pain medication scheduled for 2:00 PM</p>
                            <p class="alert-time">15 minutes ago</p>
                        </div>
                        <div class="alert-actions">
                            <button class="btn-small primary">Administer</button>
                            <button class="btn-small">Reschedule</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Schedule and Tasks -->
            <div class="two-column-layout">
                <div class="nurse-card">
                    <h3>Today's Medication Schedule</h3>
                    <div class="medication-schedule">
                        <div class="med-item">
                            <div class="med-time">
                                <span class="time">3:00 PM</span>
                                <span class="room">Room 205</span>
                            </div>
                            <div class="med-content">
                                <h4>Mary Johnson</h4>
                                <p class="medication">Lisinopril 10mg - Oral</p>
                                <p class="indication">Hypertension</p>
                            </div>
                            <div class="med-actions">
                                <button class="btn-small primary">Given</button>
                                <button class="btn-small">Defer</button>
                            </div>
                        </div>
                        
                        <div class="med-item">
                            <div class="med-time">
                                <span class="time">3:30 PM</span>
                                <span class="room">Room 108</span>
                            </div>
                            <div class="med-content">
                                <h4>James Wilson</h4>
                                <p class="medication">Metformin 500mg - Oral</p>
                                <p class="indication">Diabetes</p>
                            </div>
                            <div class="med-actions">
                                <button class="btn-small primary">Given</button>
                                <button class="btn-small">Defer</button>
                            </div>
                        </div>
                        
                        <div class="med-item">
                            <div class="med-time">
                                <span class="time">4:00 PM</span>
                                <span class="room">Room 312</span>
                            </div>
                            <div class="med-content">
                                <h4>Susan Lee</h4>
                                <p class="medication">Morphine 5mg - IV</p>
                                <p class="indication">Post-operative pain</p>
                            </div>
                            <div class="med-actions">
                                <button class="btn-small primary">Given</button>
                                <button class="btn-small">Defer</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="nurse-card">
                    <h3>Vital Signs Schedule</h3>
                    <div class="vitals-schedule">
                        <div class="vital-item">
                            <div class="vital-patient">
                                <h4>Room 205 - Mary Johnson</h4>
                                <p class="vital-type">Blood Pressure & Temperature</p>
                                <p class="vital-time">Due: 3:15 PM</p>
                            </div>
                            <button class="btn-small primary">Record</button>
                        </div>
                        
                        <div class="vital-item completed">
                            <div class="vital-patient">
                                <h4>Room 108 - James Wilson</h4>
                                <p class="vital-type">Full Vitals Check</p>
                                <p class="vital-time">Completed: 2:30 PM</p>
                            </div>
                            <button class="btn-small success">✓ Done</button>
                        </div>
                        
                        <div class="vital-item">
                            <div class="vital-patient">
                                <h4>Room 312 - Susan Lee</h4>
                                <p class="vital-type">Pain Assessment</p>
                                <p class="vital-time">Due: 4:00 PM</p>
                            </div>
                            <button class="btn-small primary">Record</button>
                        </div>
                        
                        <div class="vital-item">
                            <div class="vital-patient">
                                <h4>Room 215 - Michael Brown</h4>
                                <p class="vital-type">Blood Glucose</p>
                                <p class="vital-time">Due: 4:30 PM</p>
                            </div>
                            <button class="btn-small primary">Record</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Status Overview -->
            <div class="nurse-card">
                <h3>Patient Status Overview</h3>
                <div class="patient-status-grid">
                    <div class="status-item stable">
                        <div class="status-info">
                            <h4>Room 101 - Alice Cooper</h4>
                            <p class="diagnosis">Post-surgical recovery</p>
                            <p class="status-label">Stable</p>
                        </div>
                        <div class="status-vitals">
                            <span class="vital">BP: 120/80</span>
                            <span class="vital">HR: 72</span>
                            <span class="vital">Temp: 98.6°F</span>
                        </div>
                    </div>
                    
                    <div class="status-item attention">
                        <div class="status-info">
                            <h4>Room 205 - Mary Johnson</h4>
                            <p class="diagnosis">Hypertension management</p>
                            <p class="status-label">Needs Attention</p>
                        </div>
                        <div class="status-vitals">
                            <span class="vital high">BP: 180/95</span>
                            <span class="vital">HR: 88</span>
                            <span class="vital">Temp: 99.2°F</span>
                        </div>
                    </div>
                    
                    <div class="status-item stable">
                        <div class="status-info">
                            <h4>Room 108 - James Wilson</h4>
                            <p class="diagnosis">Diabetes monitoring</p>
                            <p class="status-label">Stable</p>
                        </div>
                        <div class="status-vitals">
                            <span class="vital">BP: 118/76</span>
                            <span class="vital">HR: 68</span>
                            <span class="vital">BG: 145 mg/dL</span>
                        </div>
                    </div>
                    
                    <div class="status-item recovery">
                        <div class="status-info">
                            <h4>Room 312 - Susan Lee</h4>
                            <p class="diagnosis">Post-operative care</p>
                            <p class="status-label">Recovering</p>
                        </div>
                        <div class="status-vitals">
                            <span class="vital">BP: 115/72</span>
                            <span class="vital">HR: 75</span>
                            <span class="vital">Pain: 3/10</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="nurse-card">
                <h3>Recent Activity</h3>
                <div class="activity-timeline">
                    <div class="activity-item">
                        <div class="activity-time">2:45 PM</div>
                        <div class="activity-content">
                            <p class="activity-title">Medication Administered</p>
                            <p class="activity-detail">Gave Insulin to James Wilson (Room 108)</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-time">2:30 PM</div>
                        <div class="activity-content">
                            <p class="activity-title">Vital Signs Recorded</p>
                            <p class="activity-detail">Full vitals check for James Wilson (Room 108)</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-time">2:15 PM</div>
                        <div class="activity-content">
                            <p class="activity-title">Patient Assessment</p>
                            <p class="activity-detail">Completed wound dressing change for Alice Cooper (Room 101)</p>
                        </div>
                    </div>
                    
                    <div class="activity-item">
                        <div class="activity-time">1:45 PM</div>
                        <div class="activity-content">
                            <p class="activity-title">Alert Responded</p>
                            <p class="activity-detail">Addressed high BP alert for Mary Johnson (Room 205)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Quick Action Modal Functions
        function showVitalSignsModal() {
            // This would open a modal for recording vital signs
            nurseNotify('info', 'Coming Soon', 'Vital Signs Modal - This will be implemented with a proper form for recording patient vital signs');
        }

        function showMedicationModal() {
            // This would open a modal for medication administration
            nurseNotify('info', 'Coming Soon', 'Medication Administration Modal - This will be implemented with proper medication tracking');
        }

        function showIncidentModal() {
            // This would open a modal for incident reporting
            nurseNotify('info', 'Coming Soon', 'Incident Report Modal - This will be implemented with incident reporting form');
        }

        // Dashboard interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add click handlers for alert response buttons
            const alertButtons = document.querySelectorAll('.alert-item button');
            alertButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    const alertItem = this.closest('.alert-item');
                    const patientName = alertItem.querySelector('h4').textContent;
                    
                    if (action === 'Respond') {
                        nurseNotify('warning', 'Alert Response', `Responding to alert for ${patientName}`);
                        // Mark as responded
                        alertItem.style.opacity = '0.7';
                        this.textContent = 'Responded';
                        this.classList.remove('urgent');
                        this.classList.add('success');
                    } else if (action === 'Call Doctor') {
                        nurseNotify('info', 'Doctor Called', `Calling doctor for ${patientName}`);
                    }
                });
            });

            // Add click handlers for medication buttons
            const medButtons = document.querySelectorAll('.med-item button');
            medButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const action = this.textContent.trim();
                    const medItem = this.closest('.med-item');
                    const patientName = medItem.querySelector('h4').textContent;
                    const medication = medItem.querySelector('.medication').textContent;
                    
                    if (action === 'Given') {
                        const confirmGiven = confirm(`Confirm medication administration:\n${medication}\nTo: ${patientName}`);
                        if (confirmGiven) {
                            medItem.style.opacity = '0.7';
                            medItem.style.background = '#e8f5e8';
                            this.textContent = '✓ Given';
                            this.classList.remove('primary');
                            this.classList.add('success');
                            
                            // Update activity timeline
                            addActivityItem(`Medication Administered`, `Gave ${medication} to ${patientName}`);
                        }
                    } else if (action === 'Defer') {
                        const reason = prompt('Reason for deferring medication:');
                        if (reason) {
                            nurseNotify('warning', 'Medication Deferred', `Medication deferred for ${patientName}. Reason: ${reason}`);
                        }
                    }
                });
            });

            // Add click handlers for vital signs buttons
            const vitalButtons = document.querySelectorAll('.vital-item button');
            vitalButtons.forEach(button => {
                if (button.textContent.trim() === 'Record') {
                    button.addEventListener('click', function() {
                        const vitalItem = this.closest('.vital-item');
                        const patientName = vitalItem.querySelector('h4').textContent;
                        const vitalType = vitalItem.querySelector('.vital-type').textContent;
                        
                        // Simulate recording vital signs
                        const recordVitals = confirm(`Record vital signs:\n${vitalType}\nFor: ${patientName}`);
                        if (recordVitals) {
                            vitalItem.classList.add('completed');
                            vitalItem.querySelector('.vital-time').textContent = 'Completed: ' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            this.textContent = '✓ Done';
                            this.classList.remove('primary');
                            this.classList.add('success');
                            
                            // Update activity timeline
                            addActivityItem(`Vital Signs Recorded`, `${vitalType} for ${patientName}`);
                        }
                    });
                }
            });

            // Auto-refresh dashboard data every 2 minutes
            setInterval(function() {
                // In a real application, this would fetch updated data
                console.log('Refreshing nurse dashboard data...');
                updateDashboardStats();
            }, 120000); // 2 minutes

            // Add hover effects for stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });

            // Initialize real-time clock
            updateCurrentTime();
            setInterval(updateCurrentTime, 1000);
        });

        // Function to add new activity to timeline
        function addActivityItem(title, detail) {
            const timeline = document.querySelector('.activity-timeline');
            const currentTime = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            
            const newActivity = document.createElement('div');
            newActivity.className = 'activity-item';
            newActivity.innerHTML = `
                <div class="activity-time">${currentTime}</div>
                <div class="activity-content">
                    <p class="activity-title">${title}</p>
                    <p class="activity-detail">${detail}</p>
                </div>
            `;
            
            // Insert at the top of the timeline
            timeline.insertBefore(newActivity, timeline.firstChild);
            
            // Limit to 10 activities
            const activities = timeline.querySelectorAll('.activity-item');
            if (activities.length > 10) {
                timeline.removeChild(activities[activities.length - 1]);
            }
        }

        // Function to update dashboard statistics
        function updateDashboardStats() {
            // This would fetch real data from the server
            // For now, we'll simulate some updates
            const stats = document.querySelectorAll('.stat-number');
            
            // Simulate minor changes in numbers
            stats.forEach((stat, index) => {
                let currentValue = parseInt(stat.textContent);
                if (Math.random() > 0.7) { // 30% chance to update
                    let change = Math.floor(Math.random() * 3) - 1; // -1, 0, or 1
                    let newValue = Math.max(0, currentValue + change);
                    stat.textContent = newValue;
                    
                    // Flash animation for changes
                    if (change !== 0) {
                        stat.style.background = '#fff3cd';
                        setTimeout(() => {
                            stat.style.background = 'transparent';
                        }, 1000);
                    }
                }
            });
        }

        // Function to update current time display
        function updateCurrentTime() {
            // Update any time-dependent elements
            const now = new Date();
            
            // Update overdue medications highlighting
            const medItems = document.querySelectorAll('.med-item');
            medItems.forEach(item => {
                const timeElement = item.querySelector('.time');
                if (timeElement) {
                    const schedTime = timeElement.textContent;
                    // Simple check for overdue (this would be more sophisticated in real app)
                    if (isOverdue(schedTime)) {
                        item.style.borderColor = '#dc3545';
                        item.style.background = '#fff5f5';
                    }
                }
            });
        }

        // Helper function to check if medication time is overdue
        function isOverdue(timeString) {
            // Simplified check - in real app, this would parse actual times
            const now = new Date();
            const currentHour = now.getHours();
            
            // Extract hour from time string (assuming format like "3:00 PM")
            const timePart = timeString.includes('PM') ? 
                parseInt(timeString.split(':')[0]) + 12 : 
                parseInt(timeString.split(':')[0]);
            
            return currentHour > timePart;
        }

        // Function to handle emergency alerts
        function handleEmergencyAlert(patientRoom, alertType) {
            // This would be called by server-sent events or WebSocket in real application
            const alertsContainer = document.querySelector('.alert-list');
            
            const newAlert = document.createElement('div');
            newAlert.className = 'alert-item high-priority';
            newAlert.innerHTML = `
                <div class="alert-content">
                    <h4>${patientRoom}</h4>
                    <p class="alert-type">${alertType}</p>
                    <p class="alert-detail">Emergency alert - Immediate attention required</p>
                    <p class="alert-time">Just now</p>
                </div>
                <div class="alert-actions">
                    <button class="btn-small urgent">Respond</button>
                    <button class="btn-small">Call Doctor</button>
                </div>
            `;
            
            // Insert at top
            alertsContainer.insertBefore(newAlert, alertsContainer.firstChild);
            
            // Flash notification
            newAlert.style.animation = 'flash 1s ease-in-out 3 alternate';
            
            // Play alert sound (if browser allows)
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+D...');
                audio.play();
            } catch (e) {
                console.log('Audio notification not available');
            }
        }

        // CSS animation for alerts
        const style = document.createElement('style');
        style.textContent = `
            @keyframes flash {
                0% { background-color: #fff5f5; }
                100% { background-color: #ffebee; }
            }
        `;
        document.head.appendChild(style);
    </script>

    @include('nurse.modals.notification_system')

</body>
</html>
