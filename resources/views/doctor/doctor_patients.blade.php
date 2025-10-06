<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">                                <td class="patient-age">
                                    @php
                                        $ageYears = $patient->date_of_birth ? intval(\Carbon\Carbon::parse($patient->date_of_birth)->diffInYears(now())) : null;
                                    @endphp
                                    {{ $ageYears !== null ? $ageYears.' years' : 'N/A' }}
                                </td>title>Patient Records</title>
    <link rel="stylesheet" href="{{url('css/doctorcss/doctor.css')}}">
    <link rel="stylesheet" href="{{url('css/pagination.css')}}">
</head>
<body>
    @php
        $doctorName = auth()->user()->name ?? 'Doctor';
    @endphp
    @include('doctor.doctor_header')
    <div class="doctor-layout">
        @include('doctor.doctor_sidebar')
        <div class="main-content">
            <div class="patients-header">
                <h2>Patient Records</h2>
                <div class="patients-stats">
                    <div class="stat-item">
                        <span class="stat-number">{{ $totalPatients }}</span>
                        <span class="stat-label">Total Patients</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $admittedPatients }}</span>
                        <span class="stat-label">Admitted</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">{{ $outpatients }}</span>
                        <span class="stat-label">Outpatients</span>
                    </div>
                    <div class="stat-item critical">
                        <span class="stat-number">{{ $criticalPatients }}</span>
                        <span class="stat-label">Critical</span>
                    </div>
                </div>
            </div>

            <!-- Search and Filter Controls -->
            <div class="doctor-card">
                <div class="patients-controls">
                    <div class="search-section">
                        <form action="{{ route('doctor.patients') }}" method="GET" class="search-form">
                            <div class="search-input-group">
                                <input type="text" 
                                       name="query" 
                                       placeholder="Search by name, ID, room, diagnosis, phone, email, blood type..." 
                                       value="{{ request('query') }}"
                                       class="search-input">
                                <!-- Preserve filter when searching -->
                                @if(request('status') && request('status') != 'all')
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                @endif
                                <button type="submit" class="search-btn">
                                    🔍 Search
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="filter-section">
                        <form action="{{ route('doctor.patients') }}" method="GET" class="filter-form">
                            <select name="status" class="filter-select" onchange="this.form.submit()">
                                <option value="all" {{ request('status') == 'all' || !request('status') ? 'selected' : '' }}>All Patients</option>
                                <option value="admitted" {{ request('status') == 'admitted' ? 'selected' : '' }}>Admitted</option>
                                <option value="outpatient" {{ request('status') == 'outpatient' ? 'selected' : '' }}>Outpatients</option>
                                <option value="emergency" {{ request('status') == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                <option value="discharged" {{ request('status') == 'discharged' ? 'selected' : '' }}>Discharged</option>
                            </select>
                            <!-- Preserve search when filtering -->
                            @if(request('query'))
                                <input type="hidden" name="query" value="{{ request('query') }}">
                            @endif
                        </form>
                    </div>
                </div>
            </div>

            <!-- Patients Table -->
            <div class="doctor-card">
                <div class="table-header">
                    <h3>Patient List</h3>
                    @if(request('query'))
                        <p class="search-results">Search results for: "<strong>{{ request('query') }}</strong>" 
                            <a href="{{ route('doctor.patients') }}" class="clear-search">Clear</a>
                        </p>
                    @endif
                    @if(request('status') && request('status') != 'all')
                        <p class="filter-results">Showing: <strong>{{ ucfirst(request('status')) }}</strong> patients
                            <a href="{{ route('doctor.patients') }}" class="clear-filter">Show All</a>
                        </p>
                    @endif
                </div>

                @if($patients->count() > 0)
                <div class="patients-table-container">
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th>Patient ID</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Gender</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Primary Diagnosis</th>
                                <th>Blood Type</th>
                                <th>Admission Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($patients as $patient)
                            <tr class="patient-row {{ $patient->status == 'emergency' ? 'critical' : '' }}">
                                <td class="patient-id">
                                    <strong>{{ $patient->patient_id }}</strong>
                                </td>
                                <td class="patient-name">
                                    <div class="name-info">
                                        <strong>{{ $patient->full_name }}</strong>
                                        @if($patient->phone)
                                            <small>{{ $patient->phone }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="patient-age">
                                    @php
                                        $ageYears = $patient->date_of_birth ? \\Carbon\\Carbon::parse($patient->date_of_birth)->diffInYears(now()) : null;
                                    @endphp
                                    {{ $ageYears !== null ? $ageYears.' years' : 'N/A' }}
                                </td>
                                <td class="patient-gender">{{ $patient->gender }}</td>
                                <td class="patient-room">
                                    @if($patient->room_number)
                                        <span class="room-badge">{{ $patient->room_number }}</span>
                                    @else
                                        <span class="room-badge outpatient">N/A</span>
                                    @endif
                                </td>
                                <td class="patient-status">
                                    <span class="status-badge {{ $patient->status }}">
                                        {{ ucfirst($patient->status) }}
                                    </span>
                                </td>
                                <td class="patient-diagnosis">
                                    <div class="diagnosis-info">
                                        {{ $patient->primary_diagnosis }}
                                        @if($patient->allergies && count(json_decode($patient->allergies, true)) > 0)
                                            <div class="allergies-warning">
                                                ⚠️ Allergies: {{ implode(', ', json_decode($patient->allergies, true)) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="blood-type">
                                    <span class="blood-badge">{{ $patient->blood_type ?? 'Unknown' }}</span>
                                </td>
                                <td class="admission-date">
                                    @if($patient->admission_date)
                                        {{ $patient->admission_date->format('M d, Y') }}
                                        <small>{{ $patient->admission_date->format('H:i') }}</small>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="patient-actions">
                                    <div class="action-buttons">
                                        <button class="btn-action primary" onclick="viewPatient({{ $patient->id }})">
                                            👁️ View
                                        </button>
                                        <button class="btn-action secondary" onclick="showMedications({{ $patient->id }})">
                                            💊 Meds
                                        </button>
                                        <button class="btn-action secondary" onclick="showVitals({{ $patient->id }})">
                                            📊 Vitals
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="pagination-wrapper">
                    {{ $patients->appends(request()->query())->links() }}
                </div>

                @else
                <div class="no-patients">
                    <div class="no-patients-icon">👥</div>
                    <h3>No Patients Found</h3>
                    @if(request('query'))
                        <p>No patients match your search criteria.</p>
                        <a href="{{ route('doctor.patients') }}" class="btn-primary">View All Patients</a>
                    @else
                        <p>You don't have any patients assigned yet.</p>
                    @endif
                </div>
                @endif
            </div>

            <!-- Quick Patient Info Modal (Hidden by default) -->
            <div id="patientModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Patient Information</h3>
                        <span class="close" onclick="closeModal()">&times;</span>
                    </div>
                    <div class="modal-body" id="modalBody">
                        <!-- Patient details will be loaded here -->
                    </div>
                </div>
            </div>
    </div>

    <script>
        // Patient management functions
        function viewPatient(patientId) {
            // In a real application, this would fetch patient details via AJAX
            const modal = document.getElementById('patientModal');
            const modalBody = document.getElementById('modalBody');
            
            modalBody.innerHTML = `
                <div class="loading">Loading patient details...</div>
            `;
            
            modal.style.display = 'block';
            
            // Simulate loading patient data
            setTimeout(() => {
                modalBody.innerHTML = `
                    <div class="patient-details">
                        <h4>Patient Details</h4>
                        <p><strong>Patient ID:</strong> P00${patientId}</p>
                        <p><strong>Full Medical Record:</strong> This would show complete patient information including medical history, current medications, vital signs, and treatment plans.</p>
                        <div class="modal-actions">
                            <button class="btn-action primary" onclick="editPatient(${patientId})">Edit Record</button>
                            <button class="btn-action secondary" onclick="printRecord(${patientId})">Print Record</button>
                        </div>
                    </div>
                `;
            }, 500);
        }

        function showMedications(patientId) {
            alert(`Viewing medications for patient ID: ${patientId}\nThis would open the medication management interface.`);
        }

        function showVitals(patientId) {
            alert(`Viewing vital signs for patient ID: ${patientId}\nThis would open the vital signs tracking interface.`);
        }

        function editPatient(patientId) {
            alert(`Editing patient record for ID: ${patientId}\nThis would open the patient edit form.`);
        }

        function printRecord(patientId) {
            alert(`Printing medical record for patient ID: ${patientId}\nThis would generate a printable medical record.`);
        }

        function closeModal() {
            document.getElementById('patientModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            const modal = document.getElementById('patientModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Auto-refresh functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Enhanced search functionality
            const searchInput = document.querySelector('.search-input');
            const searchForm = document.querySelector('.search-form');
            
            // Add real-time search suggestions (debounced)
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                // Highlight matching text in the table if search is active
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        highlightSearchResults(query);
                    }, 300);
                } else {
                    clearHighlights();
                }
            });

            // Add keyboard shortcuts for search
            document.addEventListener('keydown', function(e) {
                // Ctrl+F or Cmd+F to focus search
                if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }
                
                // Enter to submit search
                if (e.key === 'Enter' && document.activeElement === searchInput) {
                    searchForm.submit();
                }
                
                // Escape to clear search
                if (e.key === 'Escape' && document.activeElement === searchInput) {
                    if (searchInput.value) {
                        searchInput.value = '';
                        window.location.href = "{{ route('doctor.patients') }}";
                    }
                }
            });

            // Highlight critical patients
            const criticalRows = document.querySelectorAll('.patient-row.critical');
            criticalRows.forEach(row => {
                setInterval(() => {
                    row.style.backgroundColor = row.style.backgroundColor === 'rgb(255, 235, 238)' ? '#fff5f5' : '#ffebee';
                }, 2000);
            });

            // Add search result count
            updateSearchResultsCount();
            
            // Auto-submit filter form when changed
            const filterSelect = document.querySelector('.filter-select');
            filterSelect.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Function to highlight search results in the table
        function highlightSearchResults(query) {
            clearHighlights();
            
            if (query.length < 2) return;
            
            const cells = document.querySelectorAll('.patients-table td');
            const regex = new RegExp(`(${escapeRegex(query)})`, 'gi');
            
            cells.forEach(cell => {
                if (cell.textContent.toLowerCase().includes(query.toLowerCase())) {
                    const originalHtml = cell.innerHTML;
                    const highlightedHtml = originalHtml.replace(regex, '<mark class="search-highlight">$1</mark>');
                    cell.innerHTML = highlightedHtml;
                }
            });
        }

        // Function to clear search highlights
        function clearHighlights() {
            const highlights = document.querySelectorAll('.search-highlight');
            highlights.forEach(highlight => {
                const parent = highlight.parentNode;
                parent.replaceChild(document.createTextNode(highlight.textContent), highlight);
                parent.normalize();
            });
        }

        // Function to escape regex special characters
        function escapeRegex(string) {
            return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        // Function to update search results count
        function updateSearchResultsCount() {
            const tableRows = document.querySelectorAll('.patient-row');
            const searchQuery = "{{ request('query') }}";
            const statusFilter = "{{ request('status') }}";
            
            if (searchQuery || (statusFilter && statusFilter !== 'all')) {
                const resultCount = tableRows.length;
                const tableHeader = document.querySelector('.table-header h3');
                
                if (resultCount === 0) {
                    tableHeader.innerHTML = 'Patient List - No Results Found';
                } else {
                    tableHeader.innerHTML = `Patient List - ${resultCount} Result${resultCount !== 1 ? 's' : ''} Found`;
                }
            }
        }
    </script>

</body>
</html>
