<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Records Management</title>
    <link rel="stylesheet" href="{{url('css/admincss/admin.css')}}">
    <link rel="stylesheet" href="{{url('css/pagination.css')}}">
</head>
<body>
    @php
        $adminName = auth()->user()->name ?? 'Admin';
    @endphp
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <div class="main-content">
            <h2>Patient Records Management</h2>
            
            <!-- Controls Row -->
            <div class="controls-row">
                <div class="filter-search-controls">
                    <select id="statusFilter" class="role-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="discharged" {{ request('status') == 'discharged' ? 'selected' : '' }}>Discharged</option>
                        <option value="deceased" {{ request('status') == 'deceased' ? 'selected' : '' }}>Deceased</option>
                    </select>
                    <input type="text" id="searchInput" placeholder="Search by patient name or patient number..." class="search-input" value="{{ request('q') }}">
                    <button id="searchButton" class="search-btn">Search</button>
                    @if(request('q') || request('status'))
                        <button id="clearButton" class="clear-btn">Clear</button>
                    @endif
                </div>
            </div>

            <div class="admin-card">
                <!-- Patients Table -->
                <table class="admin-table" id="patientsTable">
                    <thead>
                        <tr>
                            <th class="sortable" data-sort="patient_no">
                                Patient No
                                @if(request('sort') == 'patient_no')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="first_name">
                                Patient Name
                                @if(request('sort') == 'first_name')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th>Age</th>
                            <th class="sortable" data-sort="room_no">
                                Room
                                @if(request('sort') == 'room_no')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="status">
                                Status
                                @if(request('sort') == 'status')
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th class="sortable" data-sort="created_at">
                                Admission Date
                                @if(request('sort') == 'created_at' || !request('sort'))
                                    <span class="sort-indicator {{ request('direction') == 'asc' ? 'asc' : 'desc' }}">
                                        {{ request('direction') == 'asc' ? '↑' : '↓' }}
                                    </span>
                                @else
                                    <span class="sort-indicator">↕</span>
                                @endif
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($patients as $patient)
                        <tr data-status="{{ strtolower($patient->status ?? 'active') }}" data-name="{{ strtolower(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '') . ' ' . ($patient->patient_no ?? '')) }}">
                            <td>{{ $patient->patient_no ?? 'N/A' }}</td>
                            <td>{{ ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '') }}</td>
                            <td>
                                @php
                                    $age = '';
                                    if($patient->age_years) {
                                        $age = $patient->age_years . 'y';
                                        if($patient->age_months) {
                                            $age .= ' ' . $patient->age_months . 'm';
                                        }
                                        if($patient->age_days && !$patient->age_years) {
                                            $age .= ' ' . $patient->age_days . 'd';
                                        }
                                    } elseif($patient->age_months) {
                                        $age = $patient->age_months . 'm';
                                        if($patient->age_days) {
                                            $age .= ' ' . $patient->age_days . 'd';
                                        }
                                    } elseif($patient->age_days) {
                                        $age = $patient->age_days . 'd';
                                    } else {
                                        $age = 'N/A';
                                    }
                                @endphp
                                {{ $age }}
                            </td>
                            <td>{{ $patient->room_no ?? 'N/A' }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($patient->status ?? 'active') }}">
                                    {{ ucfirst($patient->status ?? 'active') }}
                                </span>
                            </td>
                            <td>{{ $patient->created_at ? \Carbon\Carbon::parse($patient->created_at)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="action-btn" onclick="toggleDropdown({{ $patient->id ?? $loop->index }})">
                                        <span>⋯</span>
                                    </button>
                                    <div class="dropdown-content" id="dropdown-{{ $patient->id ?? $loop->index }}">
                                        <a href="#" onclick="viewPatient({{ $patient->id ?? $loop->index }})">View Details</a>
                                        <a href="#" onclick="updatePatientStatus({{ $patient->id ?? $loop->index }}, 'active')" class="activate-action">Mark Active</a>
                                        <a href="#" onclick="updatePatientStatus({{ $patient->id ?? $loop->index }}, 'discharged')" class="discharge-action">Mark Discharged</a>
                                        <a href="#" onclick="updatePatientStatus({{ $patient->id ?? $loop->index }}, 'deceased')" class="deceased-action">Mark Deceased</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                        @if($patients->count() == 0)
                        <tr class="no-results">
                            <td colspan="7" style="text-align: center; color: #666; padding: 20px;">
                                @if(request('q') || request('status'))
                                    No patients found matching your search criteria.
                                @else
                                    No patients found.
                                @endif
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination Links -->
            <div class="pagination-wrapper">
                @if(isset($patients) && method_exists($patients, 'hasPages') && $patients->hasPages())
                    @include('components.custom-pagination', ['paginator' => $patients])
                @endif
            </div>
        </div>
    </div>

    <!-- Patient Details Modal -->
    <div id="patientDetailsModal" class="addUserModal" style="display: none;">
        <div class="addUserModalContent">
            <button class="addUserModalClose" onclick="closePatientDetailsModal()">&times;</button>
            <div class="sign">Patient Details</div>
            
            <div id="patientDetailsContent">
                <!-- Patient details will be loaded here -->
            </div>
        </div>
    </div>

    <script>
    function openPatientDetailsModal() {
        document.getElementById('patientDetailsModal').style.display = 'flex';
    }
    
    function closePatientDetailsModal() {
        document.getElementById('patientDetailsModal').style.display = 'none';
    }

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
        
        try {
            const response = await fetch(`/admin/patients/${patientId}/details`);
            const result = await response.json();
            
            if (result.success) {
                document.getElementById('patientDetailsContent').innerHTML = result.html;
                openPatientDetailsModal();
            } else {
                alert('Error loading patient details: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while loading patient details.');
        }
    }

    async function updatePatientStatus(patientId, newStatus) {
        document.getElementById(`dropdown-${patientId}`).classList.remove('show');
        
        const statusText = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
        if (!confirm(`Are you sure you want to mark this patient as ${statusText}?`)) {
            return;
        }
        
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
                alert(`Patient status updated to ${statusText} successfully!`);
                location.reload();
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while updating the patient status.');
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
</body>
</html>