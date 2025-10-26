<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Patient Records Management</title>
    <link rel="stylesheet" href="{{asset('css/admincss/admin.css')}}">
    <link rel="stylesheet" href="{{asset('css/pagination.css')}}">
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
                            <th>DOB / Age</th>
                            <th class="sortable" data-sort="contact_number">
                                Contact Number
                                @if(request('sort') == 'contact_number')
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
                                    $dobStr = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') : 'N/A';
                                    $age = 'N/A';
                                    if ($patient->date_of_birth) {
                                        $dob = \Carbon\Carbon::parse($patient->date_of_birth);
                                        $ageYears = intval($dob->diffInYears(now()));
                                        $age = $ageYears . ' years';
                                    }
                                @endphp
                                {{ $dobStr }}<br>
                                <small class="text-muted">{{ $age }}</small>
                            </td>
                            <td>{{ $patient->contact_number ?? '' }}</td>
                            <td>
                                <span class="status-badge status-{{ strtolower($patient->status ?? 'active') }}">
                                    {{ ucfirst($patient->status ?? 'active') }}
                                </span>
                            </td>
                            <td>{{ $patient->created_at ? \Carbon\Carbon::parse($patient->created_at)->format('M d, Y') : 'N/A' }}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="view-btn" onclick="viewPatient({{ $patient->id ?? $loop->index }})" title="View Details">
                                        View
                                    </button>
                                    <button class="edit-btn" onclick="updatePatientStatus({{ $patient->id ?? $loop->index }}, 'active')" title="Mark Active">
                                        Active
                                    </button>
                                    <button class="warning-btn" onclick="updatePatientStatus({{ $patient->id ?? $loop->index }}, 'discharged')" title="Mark Discharged">
                                        Discharge
                                    </button>
                                    <button class="delete-btn" onclick="updatePatientStatus({{ $patient->id ?? $loop->index }}, 'deceased')" title="Mark Deceased">
                                        Deceased
                                    </button>
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

    // Patient Actions
    async function viewPatient(patientId) {
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
                // initialize province/city selects if present in the returned HTML
                try { if (typeof initAdminProvinceCitySelects === 'function') initAdminProvinceCitySelects(); } catch(e) { console.warn('initAdminProvinceCitySelects error', e); }
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

    @include('admin.modals.notification_system')
    
    <script>
    // Initialize province/city selects inside admin patient details HTML (invoked after content is injected)
    function initAdminProvinceCitySelects() {
        try {
            const API_BASE = '/api/locations';

            // helper: find element within patientDetailsContent and replace an <input> with a <select> if needed
            function ensureSelect(id) {
                const container = document.getElementById('patientDetailsContent');
                if (!container) return null;
                let el = container.querySelector('#' + id);
                if (!el) return null;
                if (el.tagName && el.tagName.toLowerCase() === 'select') return el;
                // create select preserving id/name/class and current value
                const sel = document.createElement('select');
                sel.id = el.id || id;
                if (el.name) sel.name = el.name;
                // preserve any classes for styling
                sel.className = el.className || '';
                // carry over current value into data-selected attribute so population can preselect
                const currentVal = el.value || el.getAttribute('data-selected') || '';
                if (currentVal) sel.setAttribute('data-selected', currentVal);
                // replace input with select in DOM
                el.parentNode.replaceChild(sel, el);
                console.debug('Replaced input #' + id + ' with select, preserved value:', currentVal);
                return sel;
            }

            // more robust finder: search for any input/textarea whose id or name contains the keyword
            function ensureSelectForKeyword(keyword) {
                const container = document.getElementById('patientDetailsContent');
                if (!container) return null;
                const inputs = Array.from(container.querySelectorAll('input,textarea,select'));
                for (const el of inputs) {
                    const id = (el.id || '').toLowerCase();
                    const name = (el.name || '').toLowerCase();
                    if (id.includes(keyword) || name.includes(keyword)) {
                        if (el.tagName && el.tagName.toLowerCase() === 'select') return el;
                        const sel = document.createElement('select');
                        sel.id = el.id || (keyword === 'province' ? 'province' : 'city');
                        if (el.name) sel.name = el.name;
                        sel.className = el.className || '';
                        const currentVal = el.value || el.getAttribute('data-selected') || '';
                        if (currentVal) sel.setAttribute('data-selected', currentVal);
                        el.parentNode.replaceChild(sel, el);
                        console.debug('Replaced input (keyword=' + keyword + ') element with select, id:', sel.id, 'preserved value:', currentVal);
                        return sel;
                    }
                }
                return null;
            }

            const provinceSel = ensureSelect('province') || ensureSelect('edit_province') || ensureSelectForKeyword('province');
            const citySel = ensureSelect('city') || ensureSelect('edit_city') || ensureSelectForKeyword('city');
            const barangaySel = ensureSelect('barangay') || ensureSelect('edit_barangay') || ensureSelectForKeyword('barangay');
            if (!provinceSel || !citySel) return; // nothing to do

            function clearSelect(sel) { while (sel.firstChild) sel.removeChild(sel.firstChild); }
            function addOption(sel, value, text, isSelected, dataCode) {
                const opt = document.createElement('option'); opt.value = value; opt.textContent = text; if (isSelected) opt.selected = true; if (dataCode !== undefined && dataCode !== null) opt.dataset.code = dataCode; sel.appendChild(opt);
            }
            function normalize(s) { if (!s) return ''; return s.toString().normalize('NFD').replace(/\p{Diacritic}/gu, '').replace(/[^\w\s]/g, '').toLowerCase().trim(); }

            const preSelectedProvinceValue = provinceSel.value || provinceSel.getAttribute('data-selected') || '';
            let preSelectedCityValue = citySel.value || citySel.getAttribute('data-selected') || '';
            let preSelectedBarangayValue = barangaySel ? (barangaySel.value || barangaySel.getAttribute('data-selected') || '') : '';

            let provincesList = [];
            fetch(API_BASE + '/provinces')
                .then(r => r.ok ? r.json() : Promise.reject('No provinces'))
                .then(list => {
                    provincesList = Array.isArray(list) ? list : [];
                    clearSelect(provinceSel);
                    addOption(provinceSel, '', '-- Select Province --', false, '');
                    provincesList.forEach(p => {
                        const name = p.name || p.province_name || p.provDesc || p.prov_name || p.province || '';
                        const code = p.code || p.province_code || p.provCode || p.prov_code || p.id || '';
                        if (!name) return;
                        addOption(provinceSel, name, name, false, code);
                    });

                    const preFromField = preSelectedProvinceValue || '';
                    if (preFromField) {
                        let opt = Array.from(provinceSel.options).find(o => o.value === preFromField);
                        let code = opt ? opt.dataset.code : '';
                        if (!opt && provincesList.length) {
                            const normTarget = normalize(preFromField);
                            const found = provincesList.find(pp => normalize(pp.name || pp.province_name || pp.provDesc || pp.prov_name || pp.province || '') === normTarget);
                            if (found) {
                                opt = Array.from(provinceSel.options).find(o => normalize(o.value) === normalize(found.name || found.province_name || found.provDesc || found.prov_name || found.province || '')) || null;
                                code = found.code || found.provCode || found.province_code || found.prov_code || found.id || '';
                            }
                        }
                        if (opt) opt.selected = true;
                        if (preFromField) loadCitiesForProvince(preFromField, code);
                    }
                })
                .catch(err => { console.warn('Failed to load provinces for admin modal', err); clearSelect(provinceSel); addOption(provinceSel, '', '-- Unable to load provinces --', false, ''); });

            function loadCitiesForProvince(provinceName, provinceCode) {
                clearSelect(citySel); addOption(citySel, '', '-- Loading cities... --', false, '');
                const citiesUrl = API_BASE + '/cities' + (provinceCode ? ('?province_code=' + encodeURIComponent(provinceCode)) : ('?province=' + encodeURIComponent(provinceName)));
                fetch(citiesUrl)
                    .then(r => r.ok ? r.json() : Promise.reject('No cities'))
                    .then(list => {
                        clearSelect(citySel); addOption(citySel, '', '-- Select City --', false, '');
                        let matched = [];
                        if (provinceCode) {
                            matched = list.filter(c => {
                                const ccode = c.provinceCode || c.provCode || c.province_code || c.provinceId || c.province_id || c.prov_code || c.province || c.psgc10DigitCode || c.psgc10digitcode || c.code || c.id || '';
                                return ccode && (ccode.toString() === provinceCode.toString());
                            });
                        }
                        if (!matched.length && provinceName) {
                            const normTarget = normalize(provinceName);
                            matched = list.filter(c => {
                                const prov = (c.province_name || c.provDesc || c.prov_name || c.province || c.region || '') + '';
                                const cname = (c.name || c.city_name || c.citymunDesc || c.municipality || c.city || '') + '';
                                return normalize(prov) === normTarget || normalize(prov).includes(normTarget) || normalize(cname).includes(normTarget);
                            });
                        }
                        if (!matched.length && provinceName) {
                            const normTarget = normalize(provinceName);
                            matched = list.filter(c => normalize(c.name || c.city_name || c.citymunDesc || c.municipality || c.city || '').includes(normTarget));
                        }
                        if (!matched.length) { clearSelect(citySel); addOption(citySel, '', '-- No cities found for selected province --', false, ''); return; }
                        matched.forEach(c => {
                            const cname = c.name || c.city_name || c.citymunDesc || c.municipality || c.city || '';
                            if (!cname) return;
                            const isSelected = preSelectedCityValue && (preSelectedCityValue === cname);
                            addOption(citySel, cname, cname, isSelected, c.code || c.city_code || c.id || '');
                            if (isSelected) preSelectedCityValue = '';
                            // if city is preselected, attempt to load barangays for it after options are inserted
                            if (isSelected && barangaySel) {
                                const code = c.code || c.city_code || c.id || '';
                                // small delay to ensure option selection has settled
                                setTimeout(() => loadBarangaysForCity(cname, code), 40);
                            }
                        });
                    })
                    .catch(err => { console.warn('Failed to load cities for admin modal', err); clearSelect(citySel); addOption(citySel, '', '-- Unable to load cities --', false, ''); });
            }

            provinceSel?.addEventListener('change', function () {
                const selOpt = this.options[this.selectedIndex];
                const provName = selOpt ? selOpt.value : '';
                const provCode = selOpt && selOpt.dataset ? selOpt.dataset.code : '';
                if (provName) loadCitiesForProvince(provName, provCode);
                else { clearSelect(citySel); addOption(citySel, '', '-- Select province first --', false, ''); }
            });

            // Load barangays for a given city (by name or code)
            function loadBarangaysForCity(cityName, cityCode) {
                if (!barangaySel) return;
                clearSelect(barangaySel); addOption(barangaySel, '', '-- Loading barangays... --', false, '');
                // Always prefer cityCode if available, fallback to cityName
                let url = API_BASE + '/barangays';
                if (cityCode && cityCode !== '') {
                    url += '?city_code=' + encodeURIComponent(cityCode);
                } else if (cityName && cityName !== '') {
                    url += '?city=' + encodeURIComponent(cityName);
                }
                fetch(url)
                    .then(r => r.ok ? r.json() : Promise.reject('No barangays'))
                    .then(list => {
                        clearSelect(barangaySel); addOption(barangaySel, '', '-- Select Barangay --', false, '');
                        if (!Array.isArray(list) || list.length === 0) {
                            // Try fallback: if we used code and got nothing, try with city name
                            if (cityCode && cityName) {
                                fetch(API_BASE + '/barangays?city=' + encodeURIComponent(cityName))
                                    .then(r2 => r2.ok ? r2.json() : [])
                                    .then(list2 => {
                                        if (Array.isArray(list2) && list2.length > 0) {
                                            clearSelect(barangaySel); addOption(barangaySel, '', '-- Select Barangay --', false, '');
                                            list2.forEach(b => {
                                                const bname = b.name || b.barangayDesc || b.barangay || '';
                                                if (!bname) return;
                                                const isSelected = preSelectedBarangayValue && (preSelectedBarangayValue === bname);
                                                addOption(barangaySel, bname, bname, isSelected, b.code || b.id || b.barangay_code || '');
                                                if (isSelected) preSelectedBarangayValue = '';
                                            });
                                        } else {
                                            clearSelect(barangaySel); addOption(barangaySel, '', '-- No barangays found --', false, '');
                                        }
                                    });
                                return;
                            }
                            clearSelect(barangaySel); addOption(barangaySel, '', '-- No barangays found --', false, '');
                            return;
                        }
                        list.forEach(b => {
                            const bname = b.name || b.barangayDesc || b.barangay || '';
                            if (!bname) return;
                            const isSelected = preSelectedBarangayValue && (preSelectedBarangayValue === bname);
                            addOption(barangaySel, bname, bname, isSelected, b.code || b.id || b.barangay_code || '');
                            if (isSelected) preSelectedBarangayValue = '';
                        });
                    })
                    .catch(err => { console.warn('Failed to load barangays for admin modal', err); clearSelect(barangaySel); addOption(barangaySel, '', '-- Unable to load barangays --', false, ''); });
            }

            // When city changes, load barangays for that city if barangay select exists
            citySel?.addEventListener('change', function() {
                const sel = this.options[this.selectedIndex];
                const cname = sel ? sel.value : '';
                const code = sel && sel.dataset ? sel.dataset.code : '';
                if (cname && barangaySel) loadBarangaysForCity(cname, code);
                else if (barangaySel) { clearSelect(barangaySel); addOption(barangaySel, '', '-- Select city first --', false, ''); }
            });

            // Also attempt to trigger city load if modal is already open and province has value
            const modal = document.getElementById('patientDetailsModal');
            if (modal && (modal.style.display === 'flex' || modal.style.display === 'block')) {
                const currentVal = provinceSel.value || '';
                if (currentVal && (citySel.options.length <= 1)) {
                    const opt = Array.from(provinceSel.options).find(o => normalize(o.value) === normalize(currentVal) || (o.dataset && o.dataset.code && o.dataset.code === currentVal));
                    const code = opt ? opt.dataset.code : '';
                    loadCitiesForProvince(currentVal, code);
                }
            }
        } catch (e) {
            console.warn('initAdminProvinceCitySelects caught', e);
        }
    }
    </script>
</body>
</html>