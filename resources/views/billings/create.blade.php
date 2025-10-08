@extends('layouts.billing')

@section('title', 'Create New Billing')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-plus-circle text-primary"></i> Create New Billing</h2>
                <a href="{{ route('billings.index') }}" class="btn btn-secondary">
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

            <form action="{{ route('billings.store') }}" method="POST" id="billingForm">
                @csrf
                
                <!-- Patient Information -->
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user-injured"></i> Patient Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="patient_id" class="form-label">Select Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select" required>
                                    <option value="">Choose a patient...</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                            {{ $patient->display_name }} - P{{ $patient->patient_no }} ({{ $patient->date_of_birth->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Patient Status</label>
                                <div class="d-flex gap-3 align-items-center mt-2">
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
                                </div>
                            </div>
                        </div>

                        <!-- PhilHealth Status -->
                        <div class="row mt-3" id="philhealthStatus" style="display: none;">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-shield-alt"></i> 
                                    <strong>PhilHealth Status:</strong> 
                                    <span id="philhealthMessage">Checking...</span>
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
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Notes (Optional)</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="3" placeholder="Add any additional notes or comments...">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('billings.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Billing
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<script>
let itemIndex = 0;
let philhealthMember = null;

document.addEventListener('DOMContentLoaded', function() {
    // Patient selection change
    document.getElementById('patient_id').addEventListener('change', function() {
        const patientId = this.value;
        if (patientId) {
            document.getElementById('loadServicesBtn').disabled = false;
            loadPatientServices();
            checkPhilhealthStatus();
        } else {
            document.getElementById('loadServicesBtn').disabled = true;
            clearPatientServices();
        }
    });
    
    // Discount checkboxes
    document.getElementById('is_senior_citizen').addEventListener('change', calculateTotals);
    document.getElementById('is_pwd').addEventListener('change', calculateTotals);
});

async function loadPatientServices() {
    const patientId = document.getElementById('patient_id').value;
    if (!patientId) return;
    
    try {
        showBillingLoading('Loading patient services...');
        
        const response = await fetch(`/billing/patient-services/${patientId}`);
        const data = await response.json();
        
        closeBillingNotification();
        
        if (data.services && data.services.length > 0) {
            displayPatientServices(data.services);
            document.getElementById('selectPatientAlert').style.display = 'none';
            document.getElementById('noServicesAlert').style.display = 'none';
        } else {
            document.getElementById('selectPatientAlert').style.display = 'none';
            document.getElementById('noServicesAlert').style.display = 'block';
            clearPatientServices();
        }
        
    } catch (error) {
        closeBillingNotification();
        showBillingNotification('error', 'Error', 'Failed to load patient services');
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
                <input type="hidden" name="billing_items[${index}][quantity]" value="${service.quantity}">
                <input type="hidden" name="billing_items[${index}][unit_price]" class="service-unit-price" value="${service.professional_fee}">
                
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-1">
                            <i class="fas ${getServiceIcon(service.type)}"></i>
                            ${service.description}
                        </h6>
                        <small class="text-muted">
                            ${service.type.charAt(0).toUpperCase() + service.type.slice(1)} 
                            ${service.icd_code ? '(ICD: ' + service.icd_code + ')' : ''}
                            - Source: ${service.source}
                        </small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Professional Fee</label>
                        <input type="number" step="0.01" min="0" 
                               class="form-control professional-fee-input" 
                               value="${service.professional_fee}"
                               onchange="updateServiceFee(this, ${index})">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" value="${service.quantity}" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label">Total</label>
                        <div class="form-control-plaintext fw-bold" id="service-total-${index}">
                            ₱${(service.professional_fee * service.quantity).toFixed(2)}
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += serviceHtml;
    });
    
    calculateTotals();
}

function updateServiceFee(input, index) {
    const newFee = parseFloat(input.value) || 0;
    const quantity = parseFloat(input.closest('.patient-service').querySelector('input[type="number"][readonly]').value) || 1;
    const total = newFee * quantity;
    
    // Update hidden input
    input.closest('.patient-service').querySelector('.service-unit-price').value = newFee;
    
    // Update total display
    document.getElementById(`service-total-${index}`).textContent = `₱${total.toFixed(2)}`;
    
    calculateTotals();
}

function getServiceIcon(type) {
    switch(type) {
        case 'diagnosis': return 'fa-stethoscope';
        case 'laboratory': return 'fa-vial';
        case 'medicine': return 'fa-pills';
        case 'room': return 'fa-bed';
        default: return 'fa-medical';
    }
}

function clearPatientServices() {
    document.getElementById('patientServicesContainer').innerHTML = '';
    document.getElementById('selectPatientAlert').style.display = 'block';
    document.getElementById('noServicesAlert').style.display = 'none';
    calculateTotals();
}



function calculateTotals() {
    const services = document.querySelectorAll('.patient-service');
    let subtotal = 0;
    
    services.forEach(service => {
        const quantity = parseFloat(service.querySelector('input[type="number"][readonly]').value) || 0;
        const unitPrice = parseFloat(service.querySelector('.service-unit-price').value) || 0;
        subtotal += quantity * unitPrice;
    });
    
    // PhilHealth deduction (simplified - would need actual rates)
    let philhealthDeduction = 0;
    if (philhealthMember) {
        philhealthDeduction = subtotal * 0.30; // Assume 30% coverage
    }
    
    // Senior/PWD discount
    let seniorPwdDiscount = 0;
    if (document.getElementById('is_senior_citizen').checked || document.getElementById('is_pwd').checked) {
        seniorPwdDiscount = subtotal * 0.20;
    }
    
    const netAmount = subtotal - philhealthDeduction - seniorPwdDiscount;
    
    // Update display
    document.getElementById('subtotalAmount').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('philhealthDeduction').textContent = '₱' + philhealthDeduction.toFixed(2);
    document.getElementById('seniorPwdDiscount').textContent = '₱' + seniorPwdDiscount.toFixed(2);
    document.getElementById('netAmount').textContent = '₱' + netAmount.toFixed(2);
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

@include('billings.modals.notification_system')

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
</script>

@endsection