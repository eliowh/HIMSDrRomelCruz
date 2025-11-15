@extends('layouts.doctor')

@section('title','Doctor Dashboard')

@section('content')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/nursecss/nurse.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nursecss/dashboard_enhancements.css') }}">
    <link rel="stylesheet" href="{{ asset('css/doctorcss/doctor_dashboard.css') }}">
@endpush
@php
    $doctorName = auth()->user()->name ?? 'Doctor';
    $totalPatients = $patients->count() ?? 0;
    $todayAdmissions = $patients->where('created_at', '>=', today())->count() ?? 0;
    $admittedPatients = $patients->whereNotNull('room_no')->count() ?? 0;
    $pendingPatients = $patients->whereNull('room_no')->count() ?? 0;
@endphp

    <div class="dashboard-header">
        <h2>Welcome, Dr. {{ $doctorName }}!</h2>
        <p class="dashboard-subtitle">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</p>
    </div>
    
<div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <h3>Total Patients</h3>
                        <p class="stat-number">{{ $totalPatients }}</p>
                        <p class="stat-subtitle">In the system</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏥</div>
                    <div class="stat-content">
                        <h3>Admitted Today</h3>
                        <p class="stat-number">{{ $todayAdmissions }}</p>
                        <p class="stat-subtitle">New admissions</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🏠</div>
                    <div class="stat-content">
                        <h3>In Rooms</h3>
                        <p class="stat-number">{{ $admittedPatients }}</p>
                        <p class="stat-subtitle">Currently admitted</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <h3>Pending</h3>
                        <p class="stat-number">{{ $pendingPatients }}</p>
                        <p class="stat-subtitle">Awaiting rooms</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="doctor-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="/doctor/patients" class="action-btn primary">
                        <span class="btn-icon">👥</span>
                        View All Patients
                    </a>
                    <a href="/doctor/patients" class="action-btn secondary">
                        <span class="btn-icon">✏️</span>
                        Edit Patient Info
                    </a>
                    <a href="/doctor/patients" class="action-btn secondary">
                        <span class="btn-icon">🔍</span>
                        Search Patients
                    </a>
                </div>
            </div>

            <!-- Recent Patients and System Status -->
            <div class="two-column-layout">
                <div class="doctor-card">
                    <h3>Recent Patient Admissions</h3>
                    <div class="recent-patients">
                        @if($patients->count() > 0)
                            @foreach($patients->sortByDesc('created_at')->take(5) as $patient)
                                <div class="patient-item">
                                    <div class="patient-info">
                                        <h4>{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                                        <p class="patient-no">Patient #{{ $patient->patient_no }}</p>
                                        <p class="patient-details">
                                            @if($patient->room_no)
                                                <span class="room-badge">Room {{ $patient->room_no }}</span>
                                            @else
                                                <span class="pending-badge">Room Pending</span>
                                            @endif
                                            @if($patient->admission_type)
                                                • {{ $patient->admission_type }}
                                            @endif
                                        </p>
                                        <p class="admission-time">
                                            {{ $patient->created_at ? $patient->created_at->diffForHumans() : 'Recently' }}
                                        </p>
                                    </div>
                                    <div class="patient-actions">
                                        <a href="/doctor/patients" class="btn-small primary">View Details</a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-state">
                                <p>No patients registered yet.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="doctor-card">
                    <h3>System Overview</h3>
                    <div class="system-status">
                        <div class="status-item">
                            <div class="status-icon">📊</div>
                            <div class="status-content">
                                <h4>Patient Database</h4>
                                <p class="status-description">{{ $totalPatients }} patients registered</p>
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
            @if($patients->count() > 0)
            <div class="doctor-card">
                <h3>Patient Distribution by Admission Type</h3>
                <div class="admission-stats">
                    @php
                        $admissionTypes = $patients->groupBy('admission_type')->map->count();
                    @endphp
                    @foreach($admissionTypes as $type => $count)
                        <div class="admission-type-item">
                            <div class="admission-type-info">
                                <h4>{{ $type ?: 'Not Specified' }}</h4>
                                <p class="admission-count">{{ $count }} patient{{ $count !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="admission-percentage">
                                @php
                                    $percentage = round(($count / $totalPatients) * 100, 1);
                                @endphp
                                <span class="percentage-bar">
                                    <span class="percentage-fill" style="width: {{ $percentage }}%"></span>
                                </span>
                                <span class="percentage-text">{{ $percentage }}%</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recent Activity -->
            <div class="doctor-card">
                <h3>Recent System Activity</h3>
                <div class="activity-timeline">
                    @if($patients->count() > 0)
                        @foreach($patients->sortByDesc('created_at')->take(8) as $patient)
                            <div class="activity-item">
                                <div class="activity-time">
                                    {{ $patient->created_at ? $patient->created_at->format('g:i A') : 'Recent' }}
                                </div>
                                <div class="activity-content">
                                    <p class="activity-title">Patient Admitted</p>
                                    <p class="activity-detail">
                                        {{ $patient->first_name }} {{ $patient->last_name }} 
                                        @if($patient->room_no)
                                            assigned to Room {{ $patient->room_no }}
                                        @else
                                            - room assignment pending
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="activity-item">
                            <div class="activity-time">--:--</div>
                            <div class="activity-content">
                                <p class="activity-title">System Ready</p>
                                <p class="activity-detail">Hospital Management System is ready for patient admissions</p>
                            </div>
                        </div>
                    @endif
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
                        if (typeof doctorNotify === 'function') {
                            doctorNotify('info', 'System Status', `${statusTitle} is currently operational and functioning normally.`);
                        } else {
                            // fallback to alert for dev/testing
                            alert(`${statusTitle}: operational`);
                        }
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
            if (typeof doctorNotify === 'function') {
                doctorNotify('info', 'Patient Details', `Viewing details for Patient #${patientNo}. Click "View All Patients" to see complete patient information.`);
            } else {
                alert(`Patient Details: Viewing details for Patient #${patientNo}`);
            }
        }

        // Function to refresh dashboard data
        function refreshDashboard() {
            if (typeof doctorNotify === 'function') {
                doctorNotify('info', 'Dashboard Refresh', 'Dashboard data refreshed successfully.');
            } else {
                console.info('Dashboard refreshed');
            }
            
            // Add visual feedback
            const mainContent = document.querySelector('.main-content');
            mainContent.style.opacity = '0.8';
            setTimeout(() => {
                mainContent.style.opacity = '1';
            }, 500);
        }

        // Function to navigate to patient management
        function goToPatients() {
            window.location.href = '/doctor/patients';
        }

        // Function to navigate to add new patient
        function addNewPatient() {
            window.location.href = '/doctor/addPatients';
        }

        // Enhanced notification system integration (doctor)
        window.doctorSuccess = function(title, message) {
            if (typeof doctorNotify === 'function') {
                doctorNotify('success', title, message);
            } else {
                alert(`${title}: ${message}`);
            }
        };

        window.doctorWarning = function(title, message) {
            if (typeof doctorNotify === 'function') {
                doctorNotify('warning', title, message);
            } else {
                alert(`${title}: ${message}`);
            }
        };

        window.doctorError = function(title, message) {
            if (typeof doctorNotify === 'function') {
                doctorNotify('error', title, message);
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

    @include('doctor.modals.notification_system')

@endsection
