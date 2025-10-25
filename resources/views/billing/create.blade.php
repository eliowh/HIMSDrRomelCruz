@extends('layouts.billing')

@section('title', 'Create New Billing')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle text-primary"></i> Create New Billing</h2>
                <a href="{{ route('billing.dashboard') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Billings
                </a>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('billing.store') }}" method="POST" id="billingForm">
                @csrf
                
                <!-- Patient Information -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-injured"></i> Patient Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="patient_search" class="form-label">Select Patient <span class="text-danger">*</span></label>
                                <div class="position-relative">
                                    <input type="text" class="form-control" id="patient_search" placeholder="Type to search patients..." autocomplete="off">
                                    <input type="hidden" name="patient_id" id="patient_id" required>
                                    <div id="patient_dropdown" class="dropdown-menu w-100" style="display: none; max-height: 200px; overflow-y: auto;">
                                    </div>
                                </div>
                                <small class="text-muted">Search by name or patient number</small>
                            </div>
                            <div class="col-md-6">
                                <label for="admission_id" class="form-label">Select Admission <span class="text-danger">*</span></label>
                                <select name="admission_id" id="admission_id" class="form-select" required disabled>
                                    <option value="">Select patient first...</option>
                                </select>
                                <small class="text-muted">Choose the specific admission to bill</small>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label">Patient Status & Discounts</label>
                                <div class="d-flex flex-column gap-2 mt-2">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="discount_type" id="discount_none" value="none" {{ old('discount_type', 'none') == 'none' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="discount_none">None</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="discount_type" id="discount_senior" value="senior" {{ old('is_senior_citizen') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="discount_senior">Senior Citizen (20% Discount)</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" name="discount_type" id="discount_pwd" value="pwd" {{ old('is_pwd') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="discount_pwd">Person with Disability (20% Discount)</label>
                                    </div>
                                </div>

                                {{-- Hidden booleans expected by backend --}}
                                <input type="hidden" name="is_senior_citizen" id="is_senior_citizen" value="{{ old('is_senior_citizen') ? '1' : '0' }}">
                                <input type="hidden" name="is_pwd" id="is_pwd" value="{{ old('is_pwd') ? '1' : '0' }}">
                            </div>
                        </div>

                        <!-- Coverage (PhilHealth) -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <label class="form-label">Coverage</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="is_philhealth_member" id="is_philhealth_member" {{ old('is_philhealth_member') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_philhealth_member">PhilHealth Member</label>
                                </div>

                                <!-- PhilHealth status message (used by JS) -->
                                <div id="philhealthStatus" class="alert alert-info mt-2" style="display: none;">
                                    <span id="philhealthMessage">Not a PhilHealth Member</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Patient Services -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-list-ul"></i> Patient Services & Charges</h5>
                        <button type="button" class="btn btn-light btn-sm" onclick="loadPatientServices(document.getElementById('admission_id').value)" id="loadServicesBtn" disabled>
                            <i class="fas fa-sync"></i> Load Patient Services
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="patientServicesContainer">
                            <!-- Patient services will be loaded here automatically -->
                        </div>
                        
                        <div class="alert alert-info mt-3" id="selectPatientAlert">
                            <i class="fas fa-info-circle"></i> 
                            Please select a patient first to load their services and charges.
                        </div>
                        
                        <div class="alert alert-warning mt-3" id="noServicesAlert" style="display: none;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            No billable services found for this patient. Patient may not have completed any procedures yet.
                        </div>
                    </div>
                </div>

                <!-- Total Summary -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-calculator"></i> Billing Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end"><strong id="subtotalAmount">₱0.00</strong></td>
                                    </tr>
                                    <tr class="text-success">
                                        <td>PhilHealth Deduction:</td>
                                        <td class="text-end" id="philhealthDeduction">₱0.00</td>
                                    </tr>
                                    <tr class="text-success">
                                        <td>Senior/PWD Discount (20%):</td>
                                        <td class="text-end" id="seniorPwdDiscount">₱0.00</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h4 class="text-center mb-0">
                                        <strong>Net Amount: <span id="netAmount" class="text-primary">₱0.00</span></strong>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Notes (Optional)</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any additional notes or comments...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('billing.dashboard') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Billing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@push('scripts')
<script>
let itemIndex = 0;
let philhealthMember = null;

// Helper function to parse price values that may contain commas
function parsePrice(value) {
    if (typeof value === 'number') return value;
    if (typeof value !== 'string') return 0;
    // Remove commas, currency symbols, and extra spaces, then parse as float
    const cleaned = value.replace(/[,₱$\s]/g, '');
    const parsed = parseFloat(cleaned);
    return isNaN(parsed) ? 0 : parsed;
}

// Format a number as currency with comma separators and 2 decimals (₱)
function formatCurrency(value) {
    const num = typeof value === 'number' ? value : parseFloat(value) || 0;
    return new Intl.NumberFormat('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(num);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up event listeners');
    
    // Patient search functionality
    setupPatientSearch();
    
    // Initialize totals to zero on page load
    calculateTotals();
    
    // PhilHealth checkbox
    const philEl = document.getElementById('is_philhealth_member');
    if (philEl) {
        philEl.addEventListener('change', function() {
            updatePhilhealthStatus();
            calculateTotals();
        });
    }

    // Discount radio group -> update hidden fields and recalc
    const discountRadios = document.querySelectorAll('input[name="discount_type"]');
    discountRadios.forEach(r => r.addEventListener('change', updateDiscountHiddenFields));

    // Initialize discount hidden fields from current selection
    updateDiscountHiddenFields();
    
    // Admission selection - reload services when admission changes
    document.getElementById('admission_id').addEventListener('change', function() {
        const selectedAdmission = this.value;
        if (selectedAdmission) {
            console.log('Admission selected:', selectedAdmission);
            loadPatientServices(selectedAdmission);
        }
    });

    // If patient prefilled (editing or return), check last philhealth status
    const prefillPatientId = document.getElementById('patient_id').value;
    if (prefillPatientId) {
        checkLastPhilhealthStatus(prefillPatientId);
    }
});

function checkLastPhilhealthStatus(patientId) {
    if (!patientId) return;
    fetch('{{ route("billing.last.philhealth") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ patient_id: patientId })
    }).then(r => r.json()).then(data => {
        const philCheckbox = document.getElementById('is_philhealth_member');
        if (!philCheckbox) return;
        if (data.last_is_philhealth_member) {
            philCheckbox.checked = true;
            philCheckbox.disabled = true;
            updatePhilhealthStatus();
        } else {
            philCheckbox.disabled = false;
        }
    }).catch(err => {
        console.warn('Failed to fetch last philhealth status', err);
    });
}

// Update hidden fields is_senior_citizen and is_pwd based on discount radio selection
function updateDiscountHiddenFields() {
    const selected = document.querySelector('input[name="discount_type"]:checked');
    const isSeniorEl = document.getElementById('is_senior_citizen');
    const isPwdEl = document.getElementById('is_pwd');

    if (!isSeniorEl || !isPwdEl) return;

    if (!selected) {
        isSeniorEl.value = '0';
        isPwdEl.value = '0';
    } else if (selected.value === 'senior') {
        isSeniorEl.value = '1';
        isPwdEl.value = '0';
    } else if (selected.value === 'pwd') {
        isSeniorEl.value = '0';
        isPwdEl.value = '1';
    } else {
        isSeniorEl.value = '0';
        isPwdEl.value = '0';
    }

    // Recalculate totals after changing discount selection
    calculateTotals();
}

async function loadPatientServices(admissionId = null) {
    const patientId = document.getElementById('patient_id').value;
    console.log('Loading patient services for patient ID:', patientId, 'admission ID:', admissionId);
    
    if (!patientId) {
        console.log('No patient ID provided');
        return;
    }
    
    if (!admissionId) {
        console.log('No admission ID provided - services require admission selection');
        document.getElementById('patientServicesContainer').innerHTML = '';
        document.getElementById('selectPatientAlert').style.display = 'none';
        document.getElementById('noServicesAlert').innerHTML = '<div class="alert alert-info"><i class="fas fa-info-circle"></i> Please select an admission first to load services and charges.</div>';
        document.getElementById('noServicesAlert').style.display = 'block';
        return;
    }
    
    try {
        showBillingLoading('Loading patient services...');
        
        // Build URL with admission filter if provided
        const url = admissionId ? 
            `/billing/patient-services/${patientId}?admission_id=${admissionId}` : 
            `/billing/patient-services/${patientId}`;
        
        console.log('Fetching from URL:', url);
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        console.log('Patient services data:', data);
        
        closeBillingNotification();
        
        if (data.services && data.services.length > 0) {
            console.log('Displaying', data.services.length, 'services');
            displayPatientServices(data.services);
            document.getElementById('selectPatientAlert').style.display = 'none';
            document.getElementById('noServicesAlert').style.display = 'none';
        } else {
            console.log('No services found for patient');
            // Patient is selected but has no services - show appropriate message
            document.getElementById('patientServicesContainer').innerHTML = '';
            document.getElementById('selectPatientAlert').style.display = 'none';
            document.getElementById('noServicesAlert').style.display = 'block';
            calculateTotals();
        }
        
    } catch (error) {
        closeBillingNotification();
        showBillingNotification('error', 'Error', 'Failed to load patient services: ' + error.message);
        console.error('Error loading patient services:', error);
    }
}

function displayPatientServices(services) {
    const container = document.getElementById('patientServicesContainer');
    container.innerHTML = '';

    services.forEach((service, index) => {
        const isProfessional = service.type === 'professional';
        const quantity = parseFloat(service.quantity) || 1;
        const caseRate = parsePrice(service.case_rate);
        const unitPrice = parsePrice(service.unit_price);
        const total = isProfessional ? (caseRate + unitPrice) * quantity : unitPrice * quantity;

        // Short description if provided after ' - '
        const descParts = (service.description || '').split(' - ');
        const shortDesc = descParts.length > 1 ? descParts.slice(1).join(' - ') : service.description || '';

        // Build HTML safely using concatenation (avoid nested template literals)
        let html = '';
        html += '<div class="patient-service mb-3 p-3 border rounded">';
        html += '<input type="hidden" name="billing_items[' + index + '][item_type]" value="' + (service.type || '') + '">';
        html += '<input type="hidden" name="billing_items[' + index + '][description]" value="' + (service.description || '') + '">';
        html += '<input type="hidden" name="billing_items[' + index + '][icd_code]" value="' + (service.icd_code || '') + '">';
        html += '<input type="hidden" name="billing_items[' + index + '][quantity]" value="' + (quantity) + '">';
        html += '<input type="hidden" name="billing_items[' + index + '][unit_price]" class="service-unit-price" value="' + (unitPrice.toFixed(2)) + '">';
        html += '<input type="hidden" name="billing_items[' + index + '][case_rate]" value="' + (caseRate.toFixed(2)) + '">';

        html += '<div class="row">';
        html += '  <div class="col-md-4">';
        html += '    <div class="mb-2">' + (isProfessional ? ('₱' + formatCurrency(total)) : ('₱' + formatCurrency(total))) + '</div>';
        html += '    <small class="text-muted">' + (shortDesc || service.description || '') + ' - Source: ' + (service.source || '') + '</small>';
        html += '  </div>';

        if (isProfessional) {
            html += '  <div class="col-md-2">';
            html += '    <label class="form-label">Case Rate</label>';
            html += '    <input type="number" step="0.01" min="0" class="form-control case-rate-input" value="' + caseRate.toFixed(2) + '" onchange="updateServicePrice(this, ' + index + ')" readonly>';
            html += '  </div>';
            html += '  <div class="col-md-2">';
            html += '    <label class="form-label">ICD Fee</label>';
            html += '    <input type="number" step="0.01" min="0" class="form-control unit-price-input" value="' + unitPrice.toFixed(2) + '" onchange="updateServicePrice(this, ' + index + ')">';
            html += '  </div>';
        } else {
            const label = (service.type === 'room') ? 'Room Price' : (service.type === 'laboratory' ? 'Lab Fee' : (service.type === 'medicine' ? 'Medicine Price' : 'Unit Price'));
            html += '  <div class="col-md-4">';
            html += '    <label class="form-label">' + label + '</label>';
            html += '    <input type="number" step="0.01" min="0" class="form-control unit-price-input" value="' + unitPrice.toFixed(2) + '" onchange="updateServicePrice(this, ' + index + ')">';
            html += '  </div>';
        }

        html += '  <div class="col-md-2">';
        html += '    <label class="form-label">Quantity</label>';
        html += '    <input type="number" class="form-control quantity-input" value="' + (service.quantity || 1) + '" readonly>';
        html += '  </div>';

        html += '  <div class="col-md-2">';
        html += '    <label class="form-label">Total</label>';
        html += '    <div class="form-control-plaintext fw-bold" id="service-total-' + index + '">₱' + formatCurrency(total) + '</div>';
        html += '  </div>';

        html += '</div>'; // row
        html += '</div>'; // patient-service

        container.insertAdjacentHTML('beforeend', html);
    });

    // Ensure service totals are correctly displayed and overall totals updated
    refreshServiceTotals();
    calculateTotals();
}

function updateServicePrice(input, index) {
    const newPrice = parsePrice(input.value);
    const service = input.closest('.patient-service');
    const quantity = parseFloat(service.querySelector('.quantity-input').value) || 1;
    
    // Check if this is a professional service (has case rate input)
    const caseRateInput = service.querySelector('.case-rate-input');
    let total;
    
    if (caseRateInput) {
        // Professional service: both PhilHealth and non-PhilHealth members are charged Case Rate + Professional Fee
        // The difference is that PhilHealth members get a deduction covering both
        const caseRate = parsePrice(caseRateInput.value);
        
        // Both member types: Case Rate + Professional Fee included in total
        total = (caseRate + newPrice) * quantity;
    } else {
        // Other services: just use unit price
        total = newPrice * quantity;
    }
    
    // Update hidden input
    service.querySelector('.service-unit-price').value = newPrice.toFixed(2);
    
    // Update total display
    document.getElementById(`service-total-${index}`).textContent = `₱${formatCurrency(total)}`;
    
    calculateTotals();
}

// Patient search functionality
function setupPatientSearch() {
    const searchInput = document.getElementById('patient_search');
    const patientIdInput = document.getElementById('patient_id');
    const dropdown = document.getElementById('patient_dropdown');
    let searchTimeout;

    if (!searchInput) {
        console.error('Patient search input not found');
        return;
    }

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            dropdown.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            searchPatients(query);
        }, 300);
    });

    searchInput.addEventListener('blur', function() {
        // Hide dropdown after a delay to allow clicks
        setTimeout(() => {
            dropdown.style.display = 'none';
        }, 150);
    });

    searchInput.addEventListener('focus', function() {
        // On focus show patient suggestions. If the field is empty we'll request without a filter
        // so the server can return a default list (recent or all patients).
        const query = this.value.trim();
        searchPatients(query);
    });
}

async function searchPatients(query) {
    try {
        // If query is empty or very short, use the recent patients endpoint which returns a sane default list
        const endpoint = (!query || query.length < 2) ? '/billing/recent-patients' : `/billing/search-patients?q=${encodeURIComponent(query)}`;
        const response = await fetch(endpoint);
        const data = await response.json();
        
        displayPatientDropdown(data.patients);
    } catch (error) {
        console.error('Error searching patients:', error);
    }
}

function displayPatientDropdown(patients) {
    const dropdown = document.getElementById('patient_dropdown');
    
    if (!patients || patients.length === 0) {
        dropdown.innerHTML = '<div class="dropdown-item-text text-muted p-2">No patients found</div>';
        dropdown.style.display = 'block';
        return;
    }

    dropdown.innerHTML = '';
    patients.forEach(patient => {
        const item = document.createElement('a');
        item.className = 'dropdown-item';
        item.href = '#';
        item.style.cursor = 'pointer';
        item.textContent = patient.text;
        item.addEventListener('click', function(e) {
            e.preventDefault();
            selectPatient(patient);
        });
        dropdown.appendChild(item);
    });
    
    dropdown.style.display = 'block';
}

function selectPatient(patient) {
    document.getElementById('patient_search').value = patient.text;
    document.getElementById('patient_id').value = patient.id;
    document.getElementById('patient_dropdown').style.display = 'none';
    
    // Update PhilHealth status display
    updatePhilhealthStatus();

    // Auto-check PhilHealth checkbox if the patient's last billing had PhilHealth applied
    try {
        const philCheckbox = document.getElementById('is_philhealth_member');
        // Reset checkbox state for the newly selected patient to avoid carrying over previous state
        if (philCheckbox) {
            philCheckbox.checked = false;
            philCheckbox.disabled = false;
        }

        fetch('{{ route("billing.last.philhealth") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ patient_id: patient.id })
        }).then(r => r.json()).then(data => {
            if (!philCheckbox) return;
            if (data.last_is_philhealth_member) {
                philCheckbox.checked = true;
                philCheckbox.disabled = true; // prevent unchecking if historically a PhilHealth billing
            } else {
                philCheckbox.checked = false;
                philCheckbox.disabled = false;
            }
            // Update PhilHealth status display after adjusting checkbox
            updatePhilhealthStatus();
        }).catch(err => {
            console.warn('Failed to fetch last philhealth status', err);
        });
    } catch (err) {
        console.warn('Error checking last philhealth status', err);
    }
    
    // Load patient admissions
    loadPatientAdmissions(patient.id);
    
    // Enable load services button but don't auto-load
    const loadBtn = document.getElementById('loadServicesBtn');
    if (loadBtn) {
        loadBtn.disabled = false;
    }
    
    // Clear any existing services since no admission is selected yet
    document.getElementById('patientServicesContainer').innerHTML = '';
    document.getElementById('selectPatientAlert').style.display = 'none';
    document.getElementById('noServicesAlert').style.display = 'block';
}

// Load patient admissions
async function loadPatientAdmissions(patientId) {
    const admissionSelect = document.getElementById('admission_id');
    
    // Clear and disable admission select while loading
    admissionSelect.innerHTML = '<option value="">Loading admissions...</option>';
    admissionSelect.disabled = true;
    
    try {
        const response = await fetch(`/billing/patient-admissions/${patientId}`);
        const data = await response.json();
        
        // Clear the loading option and add disabled placeholder
        admissionSelect.innerHTML = '<option value="" disabled selected>Select an admission</option>';
        
        if (data.admissions && data.admissions.length > 0) {
            // Filter only active admissions (not discharged)
            const activeAdmissions = data.admissions.filter(admission => admission.status === 'active');
            
            if (activeAdmissions.length > 0) {
                let firstActiveAdmission = null;
                
                activeAdmissions.forEach((admission, index) => {
                    const option = document.createElement('option');
                    option.value = admission.id;
                    option.textContent = `${admission.admission_number} - ${admission.doctor_name} (${admission.status}) - ${new Date(admission.admission_date).toLocaleDateString()}`;
                    admissionSelect.appendChild(option);
                    
                    // Store the first active admission for auto-selection
                    if (index === 0) {
                        firstActiveAdmission = admission;
                    }
                });
                
                admissionSelect.disabled = false;
                
                // Automatically select the first active admission
                if (firstActiveAdmission) {
                    admissionSelect.value = firstActiveAdmission.id;
                    
                    // Trigger the admission change event to load services
                    const event = new Event('change');
                    admissionSelect.dispatchEvent(event);
                }
            } else {
                // No active admissions - only discharged ones exist
                admissionSelect.innerHTML = '<option value="" disabled>No active admissions available for billing</option>';
                admissionSelect.disabled = true;
            }
        } else {
            // No admissions at all
            admissionSelect.innerHTML = '<option value="" disabled>No admissions found for this patient</option>';
            admissionSelect.disabled = true;
        }
    } catch (error) {
        console.error('Error loading patient admissions:', error);
        admissionSelect.innerHTML = '<option value="">Error loading admissions</option>';
        admissionSelect.disabled = true;
    }
}

// PhilHealth status management
function updatePhilhealthStatus() {
    const checkbox = document.getElementById('is_philhealth_member');
    const statusDiv = document.getElementById('philhealthStatus');
    const messageSpan = document.getElementById('philhealthMessage');
    const patientId = document.getElementById('patient_id').value;
    
    // Only show PhilHealth status if both patient is selected and checkbox is checked
    if (patientId && checkbox.checked) {
        statusDiv.style.display = 'block';
        messageSpan.textContent = 'PhilHealth Member - Applicable discounts will be applied';
        messageSpan.parentElement.className = 'alert alert-success';
    } else if (patientId && !checkbox.checked) {
        statusDiv.style.display = 'block';
        messageSpan.textContent = 'Not a PhilHealth Member';
        messageSpan.parentElement.className = 'alert alert-info';
    } else {
        // Hide PhilHealth status if no patient is selected
        statusDiv.style.display = 'none';
    }
    
    // Refresh individual service totals to reflect current calculation logic
    refreshServiceTotals();
    
    // Recalculate totals when PhilHealth status changes
    calculateTotals();
}

// Function to refresh individual service total displays
function refreshServiceTotals() {
    const services = document.querySelectorAll('.patient-service');
    services.forEach((service, index) => {
        const quantityInput = service.querySelector('.quantity-input');
        const quantity = quantityInput ? parseFloat(quantityInput.value) || 1 : 1;
        
        const caseRateInput = service.querySelector('.case-rate-input');
        const unitPriceInput = service.querySelector('.unit-price-input');
        
        let total = 0;
        
        if (caseRateInput && unitPriceInput) {
            // Professional service: Case Rate + Professional Fee for all patients
            const caseRate = parsePrice(caseRateInput.value);
            const professionalFee = parsePrice(unitPriceInput.value);
            total = (caseRate + professionalFee) * quantity;
        } else if (unitPriceInput) {
            // Other services: just unit price
            const unitPrice = parsePrice(unitPriceInput.value);
            total = unitPrice * quantity;
        }
        
        // Update the total display
        const totalElement = document.getElementById(`service-total-${index}`);
        if (totalElement) {
            totalElement.textContent = `₱${formatCurrency(total)}`;
        }
    });
}

function getServiceIcon(type) {
    switch(type) {
        case 'professional': return 'fa-stethoscope';
        case 'diagnosis': return 'fa-stethoscope';
        case 'laboratory': return 'fa-vial';
        case 'medicine': return 'fa-pills';
        case 'room': return 'fa-bed';
        default: return 'fa-medical';
    }
}

function clearPatientServices() {
    console.log('Clearing patient services');
    document.getElementById('patientServicesContainer').innerHTML = '';
    
    // Only show select patient alert if no patient is actually selected
    const patientId = document.getElementById('patient_id').value;
    if (!patientId) {
        document.getElementById('selectPatientAlert').style.display = 'block';
        document.getElementById('noServicesAlert').style.display = 'none';
    } else {
        // If patient is selected but no services, keep the selectPatientAlert hidden
        // and let the loadPatientServices function handle showing noServicesAlert
        document.getElementById('selectPatientAlert').style.display = 'none';
    }
    
    calculateTotals();
}



function calculateTotals() {
    const services = document.querySelectorAll('.patient-service');
    let subtotal = 0;
    
    // Only calculate if we have services
    if (services.length === 0) {
        // Reset to zero if no services
        subtotal = 0;
    } else {
        services.forEach((service, index) => {
            const quantityInput = service.querySelector('.quantity-input');
            const quantity = quantityInput ? parseFloat(quantityInput.value) || 0 : 0;
            
            // Check if this is a professional service (has both case rate and professional fee)
            const caseRateInput = service.querySelector('.case-rate-input');
            const professionalFeeInput = service.querySelector('.unit-price-input');
            
            let serviceTotal = 0;
            
            if (caseRateInput && professionalFeeInput) {
                // Professional service: both PhilHealth and non-PhilHealth members are charged Case Rate + Professional Fee
                // The difference is that PhilHealth members get a deduction covering both amounts
                const caseRate = parsePrice(caseRateInput.value);
                const professionalFee = parsePrice(professionalFeeInput.value);
                
                // Both member types: Case Rate + Professional Fee included in subtotal
                serviceTotal = quantity * (caseRate + professionalFee);
            } else {
                // Other services: just use unit price
                const unitPrice = professionalFeeInput ? parsePrice(professionalFeeInput.value) : 0;
                serviceTotal = quantity * unitPrice;
            }
            
            subtotal += serviceTotal;
        });
    }
    
    // PhilHealth deduction: sum of Case Rate + Professional Fee for professional items when PhilHealth member is checked
    let philhealthDeduction = 0;
    if (document.getElementById('is_philhealth_member').checked) {
        services.forEach((service) => {
            const caseRateInput = service.querySelector('.case-rate-input');
            const unitPriceInput = service.querySelector('.unit-price-input');
            const qtyInput = service.querySelector('.quantity-input');
            const qty = qtyInput ? parseFloat(qtyInput.value) || 1 : 1;
            
            if (caseRateInput) {
                // PhilHealth covers Case Rate + Professional Fee
                const caseRate = parsePrice(caseRateInput.value);
                const professionalFee = unitPriceInput ? parsePrice(unitPriceInput.value) : 0;
                philhealthDeduction += (caseRate + professionalFee) * qty;
            }
        });
    }
    
    // Senior/PWD discount (single selection via radio -> hidden fields)
    let seniorPwdDiscount = 0;
    const isSenior = document.getElementById('is_senior_citizen') && document.getElementById('is_senior_citizen').value === '1';
    const isPwd = document.getElementById('is_pwd') && document.getElementById('is_pwd').value === '1';
    if (isSenior || isPwd) {
        // Senior/PWD discount applies on the billed subtotal after PhilHealth deduction is applied
        seniorPwdDiscount = (parseFloat(subtotal) - parseFloat(philhealthDeduction)) * 0.20;
    }
    
    const netAmount = Math.max(0, parseFloat(subtotal) - parseFloat(philhealthDeduction) - parseFloat(seniorPwdDiscount));
    
    // Update display with fallback elements
    const subtotalEl = document.getElementById('subtotalAmount');
    const philhealthEl = document.getElementById('philhealthDeduction');
    const seniorPwdEl = document.getElementById('seniorPwdDiscount');
    const netEl = document.getElementById('netAmount');
    
    if (subtotalEl) subtotalEl.textContent = '₱' + formatCurrency(subtotal);
    if (philhealthEl) philhealthEl.textContent = '₱' + formatCurrency(philhealthDeduction);
    if (seniorPwdEl) seniorPwdEl.textContent = '₱' + formatCurrency(seniorPwdDiscount);
    if (netEl) netEl.textContent = '₱' + formatCurrency(netAmount);
}

async function checkPhilhealthStatus() {
    const patientId = document.getElementById('patient_id').value;
    const statusDiv = document.getElementById('philhealthStatus');
    const messageSpan = document.getElementById('philhealthMessage');
    
    if (!patientId) {
        statusDiv.style.display = 'none';
        philhealthMember = null;
        calculateTotals();
        return;
    }
    
    statusDiv.style.display = 'block';
    messageSpan.textContent = 'Checking PhilHealth status...';
    
    try {
        const response = await fetch('{{ route("billing.check.philhealth") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ patient_id: patientId })
        });
        
        const data = await response.json();
        
        if (data.is_member) {
            philhealthMember = data.member_info;
            messageSpan.innerHTML = `
                <strong>PhilHealth Member Found!</strong><br>
                ID: ${data.member_info.philhealth_number}<br>
                Type: ${data.member_info.member_type}<br>
                Status: ${data.member_info.status}<br>
                Coverage until: ${data.member_info.expiry_date}
            `;
        } else {
            philhealthMember = null;
            messageSpan.innerHTML = '<strong>Not a PhilHealth Member</strong> - No coverage available.';
        }
        
        calculateTotals();
    } catch (error) {
        console.error('Error checking PhilHealth status:', error);
        messageSpan.textContent = 'Error checking PhilHealth status.';
    }
}

async function searchIcdCodes(event) {
    const input = event.target;
    const query = input.value.trim();
    const suggestionsDiv = input.nextElementSibling;
    
    if (query.length < 2) {
        suggestionsDiv.innerHTML = '';
        return;
    }
    
    try {
    const response = await fetch('{{ route('billing.icd.rates') }}?query=' + encodeURIComponent(query));
        const data = await response.json();
        
        let html = '';
        data.forEach(item => {
            html += `
                <div class="icd-suggestion p-2 border-bottom cursor-pointer" onclick="selectIcdCode('${item.icd_code}', '${item.description}', this)">
                    <strong>${item.icd_code}</strong> - ${item.description}
                    <br><small class="text-muted">ICD Fee: ₱${formatCurrency(item.professional_fee)}</small>
                </div>
            `;
        });
        
        if (html) {
            suggestionsDiv.innerHTML = `<div class="position-absolute bg-white border rounded shadow-sm w-100" style="z-index: 1000; max-height: 200px; overflow-y: auto;">${html}</div>`;
        } else {
            suggestionsDiv.innerHTML = '';
        }
    } catch (error) {
        console.error('Error searching ICD codes:', error);
    }
}

function selectIcdCode(code, description, element) {
    const input = element.closest('.row').querySelector('.icd-code');
    const descriptionInput = element.closest('.billing-item').querySelector('input[name*="[description]"]');
    
    input.value = code;
    if (!descriptionInput.value) {
        descriptionInput.value = description;
    }
    
    // Clear suggestions
    element.closest('.icd-suggestions').innerHTML = '';
}

// Close suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.icd-suggestions')) {
        document.querySelectorAll('.icd-suggestions').forEach(div => {
            div.innerHTML = '';
        });
    }
});
</script>

<style>
.cursor-pointer {
    cursor: pointer;
}

.icd-suggestion:hover {
    background-color: #f8f9fa;
}

.billing-item {
    transition: all 0.3s ease;
}

.billing-item:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>

@include('billing.modals.notification_system')

<script>
// Show notifications for session messages
document.addEventListener('DOMContentLoaded', function() {
    @if($errors->any())
        let errorMessages = '';
        @foreach($errors->all() as $error)
            errorMessages += '{{ $error }}\n';
        @endforeach
        showBillingNotification('error', 'Validation Error', errorMessages);
    @endif
});

// Add form submission handler for better UX
document.querySelector('form').addEventListener('submit', function(e) {
    const items = document.querySelectorAll('.billing-item');
    if (items.length === 0) {
        e.preventDefault();
        showBillingNotification('warning', 'No Items', 'Please add at least one billing item before submitting.');
        return false;
    }
    
    showBillingLoading('Creating billing record...');
});

// Notification functions are provided by the notification_system modal include
// No need to define fallback functions here as the modal provides the real implementation
</script>

@endpush

@section('styles')
<style>
/* Billing Card & Table Enhancements */
.card.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Card header color consistency with green gradient */
.card-header.bg-primary {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-success {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-info {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-secondary {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
</style>
@endsection