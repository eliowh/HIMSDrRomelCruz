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
                                <label class="form-label">Patient Status & Discounts</label>
                                <div class="d-flex gap-3 align-items-center mt-2 flex-wrap">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_senior_citizen" id="is_senior_citizen" {{ old('is_senior_citizen') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_senior_citizen">
                                            Senior Citizen (20% Discount)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_pwd" id="is_pwd" {{ old('is_pwd') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_pwd">
                                            PWD (20% Discount)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_philhealth_member" id="is_philhealth_member" {{ old('is_philhealth_member') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_philhealth_member">
                                            PhilHealth Member
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PhilHealth Status -->
                        <div class="row mt-3" id="philhealthStatus" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-shield-alt"></i> 
                                    <strong>PhilHealth Status:</strong> 
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
                        <button type="button" class="btn btn-light btn-sm" onclick="loadPatientServices()" id="loadServicesBtn" disabled>
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

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, setting up event listeners');
    
    // Patient search functionality
    setupPatientSearch();
    
    // Initialize totals to zero on page load
    calculateTotals();
    
    // PhilHealth checkbox
    document.getElementById('is_philhealth_member').addEventListener('change', function() {
        updatePhilhealthStatus();
        calculateTotals();
    });
    
    // Discount checkboxes
    document.getElementById('is_senior_citizen').addEventListener('change', calculateTotals);
    document.getElementById('is_pwd').addEventListener('change', calculateTotals);
});

async function loadPatientServices() {
    const patientId = document.getElementById('patient_id').value;
    console.log('Loading patient services for patient ID:', patientId);
    
    if (!patientId) {
        console.log('No patient ID provided');
        return;
    }
    
    try {
        showBillingLoading('Loading patient services...');
        
        console.log('Fetching from URL:', `/billing/patient-services/${patientId}`);
        const response = await fetch(`/billing/patient-services/${patientId}`);
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
            document.getElementById('selectPatientAlert').style.display = 'none';
            document.getElementById('noServicesAlert').style.display = 'block';
            clearPatientServices();
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
        const serviceHtml = `
            <div class="patient-service mb-3 p-3 border rounded">
                <input type="hidden" name="billing_items[${index}][item_type]" value="${service.type}">
                <input type="hidden" name="billing_items[${index}][description]" value="${service.description}">
                <input type="hidden" name="billing_items[${index}][icd_code]" value="${service.icd_code || ''}">
                <input type="hidden" name="billing_items[${index}][quantity]" value="${parseFloat(service.quantity) || 1}">
                <input type="hidden" name="billing_items[${index}][unit_price]" class="service-unit-price" value="${parsePrice(service.unit_price)}">
                <input type="hidden" name="billing_items[${index}][case_rate]" value="${parsePrice(service.case_rate)}">
                
                <div class="row">
                    <div class="col-md-4">
                        ${service.type === 'professional' ? `
                        <h6 class="text-primary mb-1">
                            <i class="fas ${getServiceIcon(service.type)}"></i>
                            ICD-10 - ${service.icd_code}
                        </h6>
                        <small class="text-muted">
                            ${service.description.split(' - ')[1] || service.description} - Source: ${service.source}
                        </small>
                        ` : `
                        <h6 class="text-primary mb-1">
                            <i class="fas ${getServiceIcon(service.type)}"></i>
                            ${service.description}
                        </h6>
                        <small class="text-muted">
                            ${service.type.charAt(0).toUpperCase() + service.type.slice(1)} 
                            - Source: ${service.source}
                        </small>
                        `}
                    </div>
                    ${service.type === 'professional' ? `
                    <div class="col-md-2">
                        <label class="form-label">Case Rate</label>
                        <input type="number" step="0.01" min="0" 
                               class="form-control case-rate-input" 
                               value="${parsePrice(service.case_rate)}"
                               readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Professional Fee</label>
                        <input type="number" step="0.01" min="0" 
                               class="form-control unit-price-input" 
                               value="${parsePrice(service.unit_price)}"
                               onchange="updateServicePrice(this, ${index})">
                    </div>
                    ` : `
                    <div class="col-md-4">
                        <label class="form-label">${service.type === 'room' ? 'Room Price' : service.type === 'laboratory' ? 'Lab Fee' : service.type === 'medicine' ? 'Medicine Price' : 'Unit Price'}</label>
                        <input type="number" step="0.01" min="0" 
                               class="form-control unit-price-input" 
                               value="${parsePrice(service.unit_price)}"
                               onchange="updateServicePrice(this, ${index})">
                    </div>
                    `}
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control quantity-input" value="${service.quantity}" readonly>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total</label>
                        <div class="form-control-plaintext fw-bold" id="service-total-${index}">
                            ${service.type === 'professional' ? 
                                '₱' + ((parsePrice(service.case_rate) + parsePrice(service.unit_price)) * parseFloat(service.quantity || 1)).toFixed(2) :
                                '₱' + (parsePrice(service.unit_price) * parseFloat(service.quantity || 1)).toFixed(2)
                            }
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += serviceHtml;
    });
    
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
        // Professional service: add case rate + professional fee
        const caseRate = parsePrice(caseRateInput.value);
        const totalPrice = caseRate + newPrice;
        total = totalPrice * quantity;
    } else {
        // Other services: just use unit price
        total = newPrice * quantity;
    }
    
    // Update hidden input
    service.querySelector('.service-unit-price').value = newPrice.toFixed(2);
    
    // Update total display
    document.getElementById(`service-total-${index}`).textContent = `₱${total.toFixed(2)}`;
    
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
        if (this.value.length >= 2) {
            searchPatients(this.value.trim());
        }
    });
}

async function searchPatients(query) {
    try {
        const response = await fetch(`/billing/search-patients?q=${encodeURIComponent(query)}`);
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
    
    // Load patient services
    const loadBtn = document.getElementById('loadServicesBtn');
    if (loadBtn) {
        loadBtn.disabled = false;
    }
    loadPatientServices();
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
    }
    document.getElementById('noServicesAlert').style.display = 'none';
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
                // Professional service: add case rate + professional fee
                const caseRate = parsePrice(caseRateInput.value);
                const professionalFee = parsePrice(professionalFeeInput.value);
                const totalPrice = caseRate + professionalFee;
                serviceTotal = quantity * totalPrice;
            } else {
                // Other services: just use unit price
                const unitPrice = professionalFeeInput ? parsePrice(professionalFeeInput.value) : 0;
                serviceTotal = quantity * unitPrice;
            }
            
            subtotal += serviceTotal;
        });
    }
    
    // PhilHealth deduction
    let philhealthDeduction = 0;
    if (document.getElementById('is_philhealth_member').checked) {
        philhealthDeduction = parseFloat(subtotal) * 0.30; // Assume 30% coverage
    }
    
    // Senior/PWD discount
    let seniorPwdDiscount = 0;
    if (document.getElementById('is_senior_citizen').checked || document.getElementById('is_pwd').checked) {
        seniorPwdDiscount = parseFloat(parseFloat(subtotal) - parseFloat(philhealthDeduction)) * 0.20;
    }
    
    const netAmount = Math.max(0, parseFloat(subtotal) - parseFloat(philhealthDeduction) - parseFloat(seniorPwdDiscount));
    
    // Update display with fallback elements
    const subtotalEl = document.getElementById('subtotalAmount');
    const philhealthEl = document.getElementById('philhealthDeduction');
    const seniorPwdEl = document.getElementById('seniorPwdDiscount');
    const netEl = document.getElementById('netAmount');
    
    if (subtotalEl) subtotalEl.textContent = '₱' + subtotal.toFixed(2);
    if (philhealthEl) philhealthEl.textContent = '₱' + philhealthDeduction.toFixed(2);
    if (seniorPwdEl) seniorPwdEl.textContent = '₱' + seniorPwdDiscount.toFixed(2);
    if (netEl) netEl.textContent = '₱' + netAmount.toFixed(2);
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
        const response = await fetch(`{{ route('billing.icd.rates') }}?query=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        let html = '';
        data.forEach(item => {
            html += `
                <div class="icd-suggestion p-2 border-bottom cursor-pointer" onclick="selectIcdCode('${item.icd_code}', '${item.description}', this)">
                    <strong>${item.icd_code}</strong> - ${item.description}
                    <br><small class="text-muted">Professional Fee: ₱${item.professional_fee}</small>
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

// Define missing notification functions
function showBillingLoading(message) {
    console.log('Loading:', message);
    // You can add a loading spinner here if needed
}

function closeBillingNotification() {
    console.log('Closing notification');
    // Close any loading spinners here
}

function showBillingNotification(type, title, message) {
    console.log(`${type.toUpperCase()}: ${title} - ${message}`);
    alert(`${title}: ${message}`);
}
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