<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FHIR Export - Admin Panel</title>
    <link rel="stylesheet" href="{{asset('css/admincss/admin.css')}}">
    <style>
        .fhir-container {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .fhir-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .fhir-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .fhir-header p {
            color: #666;
            font-size: 16px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #3498db;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .export-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .section-title {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .export-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .export-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .export-card {
                min-height: 200px;
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .export-grid {
                gap: 15px;
            }
            
            .export-card {
                min-height: 180px;
                padding: 15px;
            }
            
            .export-btn {
                padding: 12px 16px;
                font-size: 13px;
            }
        }
        
        .export-card {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 25px;
            background: white;
            display: flex;
            flex-direction: column;
            min-height: 220px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            position: relative;
        }
        
        .export-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .export-card h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            font-weight: 600;
        }
        
        .export-card p {
            color: #666;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.6;
            flex-grow: 1;
        }
        
        .export-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            margin-top: auto;
            box-sizing: border-box;
            line-height: 1.4;
        }
        
        .export-btn:hover {
            background: #2980b9;
        }
        
        .export-btn.primary {
            background: #27ae60;
        }
        
        .export-btn.primary:hover {
            background: #219a52;
        }
        
        .export-btn.secondary {
            background: #f39c12;
        }
        
        .export-btn.secondary:hover {
            background: #e67e22;
        }
        
        .export-btn.info {
            background: #9b59b6;
        }
        
        .export-btn.info:hover {
            background: #8e44ad;
        }
        
        .patient-search {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .search-form {
            display: block;
            gap: 10px;
            align-items: end;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-control {
            width: 97%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-help {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
        
        .alert {
            padding: 12px 16px;
            margin: 15px 0;
            border-radius: 4px;
            border-left: 4px solid;
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #27ae60;
            color: #155724;
        }
        
        .alert-error {
            background: #f8d7da;
            border-color: #e74c3c;
            color: #721c24;
        }
        
        .fhir-info {
            background: #e8f4fd;
            border: 1px solid #3498db;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        .fhir-info h4 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .api-links {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .api-links a {
            display: block;
            color: #3498db;
            text-decoration: none;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        
        .api-links a:hover {
            background: #f8f9fa;
            padding-left: 10px;
        }
        
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    @include('admin.admin_header')
    
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        
        <div class="main-content">
            <div class="fhir-container">
                <div class="fhir-header">
                    <h1>🔗 FHIR Data Export</h1>
                    <p>Export hospital data in FHIR R4 compliant JSON format for healthcare interoperability</p>
                </div>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_patients'] }}</div>
                        <div class="stat-label">Total Patients</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_admissions'] }}</div>
                        <div class="stat-label">Total Admissions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_lab_orders'] }}</div>
                        <div class="stat-label">Lab Orders</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">{{ $stats['total_medications'] }}</div>
                        <div class="stat-label">Medication Records</div>
                    </div>
                </div>

                <!-- Individual Patient Export -->
                <div class="patient-search">
                    <h3 class="section-title">Export Specific Patient Data</h3>
                    <form class="search-form" method="GET" action="{{ route('admin.export.patient.fhir') }}">
                        <div class="form-group">
                            <label for="patient_no">Patient Number</label>
                            <input type="number" id="patient_no" name="patient_no" class="form-control" 
                                   placeholder="Enter patient number (e.g., 250001)" min="250001" required>
                            <small class="form-help">
                                @php
                                    $samplePatients = \App\Models\Patient::select('patient_no', 'first_name', 'last_name')->limit(3)->get();
                                @endphp
                                Available patients: 
                                @foreach($samplePatients as $patient)
                                    {{ $patient->patient_no }} ({{ $patient->first_name }} {{ $patient->last_name }}){{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </small>
                        </div>
                        <button type="submit" class="export-btn primary">Export Patient FHIR</button>
                    </form>
                </div>

                <!-- Bulk Export Section -->
                <div class="export-section">
                    <h3 class="section-title">Bulk FHIR Exports</h3>
                    <div class="export-grid">
                        <div class="export-card">
                            <h4>👥 All Patients</h4>
                            <p>Export all patient records with complete medical history including admissions, lab results, and medications.</p>
                            <a href="{{ route('admin.export.patients.fhir') }}" class="export-btn primary" onclick="showLoading(this)">
                                Export All Patients
                            </a>
                        </div>
                        
                        <div class="export-card">
                            <h4>🏥 Hospital Encounters</h4>
                            <p>Export all hospital admissions and encounter data in FHIR Encounter resource format.</p>
                            <a href="{{ route('admin.export.encounters.fhir') }}" class="export-btn secondary" onclick="showLoading(this)">
                                Export Encounters
                            </a>
                        </div>
                        
                        <div class="export-card">
                            <h4>🧪 Lab Observations</h4>
                            <p>Export all laboratory test results and observations in FHIR Observation resource format.</p>
                            <a href="{{ route('admin.export.observations.fhir') }}" class="export-btn" onclick="showLoading(this)">
                                Export Lab Results
                            </a>
                        </div>
                        
                        <div class="export-card">
                            <h4>💊 Medication Statements</h4>
                            <p>Export all medication records and pharmacy dispensing information in FHIR format.</p>
                            <a href="{{ route('admin.export.medications.fhir') }}" class="export-btn info" onclick="showLoading(this)">
                                Export Medications
                            </a>
                        </div>
                    </div>
                </div>

                <!-- FHIR Server Information -->
                <div class="export-section">
                    <h3 class="section-title">FHIR Server Information</h3>
                    <div class="export-grid">
                        <div class="export-card">
                            <h4>📋 Capability Statement</h4>
                            <p>Download the FHIR server capability statement showing supported resources and operations.</p>
                            <a href="{{ route('admin.fhir.capability') }}" class="export-btn" onclick="showLoading(this)">
                                Download Capability
                            </a>
                        </div>
                        
                        <div class="export-card">
                            <h4>🔧 Test FHIR API</h4>
                            <p>Open the interactive FHIR API tester to test endpoints and view responses.</p>
                            <a href="{{ request()->getScheme() }}://{{ request()->getHost() }}{{ request()->getPort() ? ':' . request()->getPort() : '' }}/fhir-tester.html" target="_blank" class="export-btn secondary">
                                Open API Tester
                            </a>
                        </div>
                    </div>
                </div>

                <!-- FHIR Information -->
                <div class="fhir-info">
                    <h4>ℹ️ About FHIR Export</h4>
                    <p><strong>FHIR (Fast Healthcare Interoperability Resources)</strong> is a standard for health information exchange. 
                       This export functionality converts your hospital's MySQL data into FHIR R4 compliant JSON format, enabling:</p>
                    <ul>
                        <li>Healthcare data interoperability with other systems</li>
                        <li>Standardized patient data exchange</li>
                        <li>Integration with EMR/EHR systems</li>
                        <li>Compliance with healthcare data standards</li>
                        <li>API-based data access for external applications</li>
                    </ul>
                    
                    @if(app()->environment('local'))
                    <div style="margin-top: 15px; padding: 10px; background: #e8f4fd; border-radius: 5px; border-left: 4px solid #3498db;">
                        <strong>Development Mode:</strong> Currently running on <code>{{ config('app.url') }}</code>. 
                        URLs will automatically adjust to <strong>https://romelcruz.up.railway.app</strong> when deployed to production.
                    </div>
                    @else
                    <div style="margin-top: 15px; padding: 10px; background: #e8f5e8; border-radius: 5px; border-left: 4px solid #27ae60;">
                        <strong>Production Mode:</strong> FHIR endpoints are live at <strong>{{ config('app.url') }}</strong>
                    </div>
                    @endif
                </div>

                <!-- API Endpoints -->
                <div class="api-links">
                    <h3 class="section-title">Available FHIR API Endpoints</h3>
                    @php
                        $baseUrl = request()->getScheme() . '://' . request()->getHost() . (request()->getPort() ? ':' . request()->getPort() : '');
                    @endphp
                    <a href="{{ $baseUrl }}/api/fhir/metadata" target="_blank">GET /api/fhir/metadata - Server Capability</a>
                    <a href="{{ $baseUrl }}/api/fhir/Patient" target="_blank">GET /api/fhir/Patient - Search Patients</a>
                    <a href="{{ $baseUrl }}/api/fhir/Patient/1" target="_blank">GET /api/fhir/Patient/{id} - Get Patient</a>
                    <a href="{{ $baseUrl }}/api/fhir/Patient/1/$everything" target="_blank">GET /api/fhir/Patient/{id}/$everything - Patient Bundle</a>
                    <a href="{{ $baseUrl }}/api/fhir/Encounter/1" target="_blank">GET /api/fhir/Encounter/{id} - Get Encounter</a>
                    <a href="{{ $baseUrl }}/api/fhir/Observation/1" target="_blank">GET /api/fhir/Observation/{id} - Get Lab Result</a>
                    <a href="{{ $baseUrl }}/api/fhir/MedicationStatement/pm-1" target="_blank">GET /api/fhir/MedicationStatement/{id} - Get Medication</a>
                </div>
            </div>

            <!-- Loading Modal -->
            <div id="loadingModal" class="loading">
                <div class="spinner"></div>
                <p>Exporting FHIR data... Please wait</p>
            </div>
        </div>
    </div>

    <script>
        function showLoading(element) {
            // Show loading indicator
            document.getElementById('loadingModal').style.display = 'block';
            
            // Hide loading after a reasonable time (exports should complete quickly)
            setTimeout(function() {
                document.getElementById('loadingModal').style.display = 'none';
            }, 3000);
        }

        // Handle form submission with loading
        document.querySelector('.search-form').addEventListener('submit', function() {
            showLoading();
        });

        // Auto-hide alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>