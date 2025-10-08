@extends('layouts.billing')

@section('title', 'Edit Billing - ' . $billing->billing_number)

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit text-warning"></i> Edit Billing</h2>
                <div class="btn-group">
                    <a href="{{ route('billing.show', $billing) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Details
                    </a>
                    <a href="{{ route('billing.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> All Billings
                    </a>
                </div>
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

            <form action="{{ route('billing.update', $billing) }}" method="POST" id="editBillingForm">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Left Column - Billing Information -->
                    <div class="col-md-8">
                        <!-- Patient Information (Read-only) -->
                        <div class="card shadow mb-4">
                            <div class="card-header bg-info text-white">
                                <h5 class="mb-0"><i class="fas fa-user-injured"></i> Patient Information (Read-only)</h5>
                            </div>
                            <div class="card-body bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> {{ $billing->patient->firstName }} {{ $billing->patient->lastName }}</p>
                                        <p><strong>Date of Birth:</strong> {{ $billing->patient->dateOfBirth }}</p>
                                        <p><strong>Billing Number:</strong> {{ $billing->billing_number }}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Billing Date:</strong> {{ $billing->billing_date->format('M d, Y g:i A') }}</p>
                                        <p><strong>Created By:</strong> {{ $billing->createdBy->name }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Fee Editing -->
                        <div class="card shadow mb-4">
                            <div class="card-header bg-warning text-dark">
                                <h5 class="mb-0"><i class="fas fa-user-md"></i> Professional Fee Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> 
                                    <strong>Note:</strong> You can only edit the professional fees. Other charges are fixed based on the original billing items.
                                </div>
                                
                                @php
                                    $professionalFeeOnly = $billing->billingItems->where('item_type', 'professional')->sum('unit_price');
                                @endphp
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="professional_fees" class="form-label">
                                            Professional Fee Amount <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" 
                                                   name="professional_fees" 
                                                   id="professional_fees" 
                                                   class="form-control" 
                                                   value="{{ old('professional_fees', $professionalFeeOnly) }}" 
                                                   min="0" 
                                                   step="0.01" 
                                                   required>
                                        </div>
                                        <small class="text-muted">Editable professional fee portion only</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Professional Fee Breakdown</label>
                                        <div class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">
                                            @forelse($billing->billingItems->where('item_type', 'professional') as $item)
                                                <div class="mb-2">
                                                    <div class="fw-bold">{{ $item->description }}</div>
                                                    @if($item->case_rate > 0)
                                                        <div class="d-flex justify-content-between">
                                                            <small class="text-muted">Case Rate:</small>
                                                            <small class="text-success">₱{{ number_format($item->case_rate, 2) }}</small>
                                                        </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">Professional Fee:</small>
                                                        <small class="text-primary">₱{{ number_format($item->unit_price, 2) }}</small>
                                                    </div>
                                                    <div class="d-flex justify-content-between border-top pt-1">
                                                        <small class="fw-bold">Total:</small>
                                                        <small class="fw-bold">₱{{ number_format($item->total_amount, 2) }}</small>
                                                    </div>
                                                </div>
                                            @empty
                                                <span class="text-muted">No professional fee items</span>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Patient Status & Billing Status -->
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-cog"></i> Status Management</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Patient Discount Status</label>
                                        <div class="d-flex flex-column gap-2">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="is_senior_citizen" 
                                                       id="is_senior_citizen" 
                                                       value="1"
                                                       {{ old('is_senior_citizen', $billing->is_senior_citizen) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_senior_citizen">
                                                    Senior Citizen (20% Discount)
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       name="is_pwd" 
                                                       id="is_pwd" 
                                                       value="1"
                                                       {{ old('is_pwd', $billing->is_pwd) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_pwd">
                                                    Person with Disability (20% Discount)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="status" class="form-label">Billing Status</label>
                                        <select name="status" id="status" class="form-select" required>
                                            <option value="pending" {{ old('status', $billing->status) == 'pending' ? 'selected' : '' }}>
                                                Pending Payment
                                            </option>
                                            <option value="paid" {{ old('status', $billing->status) == 'paid' ? 'selected' : '' }}>
                                                Paid
                                            </option>
                                            <option value="cancelled" {{ old('status', $billing->status) == 'cancelled' ? 'selected' : '' }}>
                                                Cancelled
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <!-- PhilHealth Status (Read-only) -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <div class="alert {{ $billing->is_philhealth_member ? 'alert-info' : 'alert-secondary' }}">
                                            <i class="fas fa-shield-alt"></i>
                                            <strong>PhilHealth Status:</strong> 
                                            @if($billing->is_philhealth_member)
                                                Active Member - Coverage Applied
                                            @else
                                                Not a PhilHealth Member
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="card shadow mb-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Notes</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="notes" 
                                          class="form-control" 
                                          rows="4" 
                                          placeholder="Add any additional notes or comments...">{{ old('notes', $billing->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Current Billing Summary -->
                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-calculator"></i> Updated Summary</h5>
                            </div>
                            <div class="card-body">
                                <!-- Current Breakdown -->
                                <h6 class="text-muted mb-3">Current Charges</h6>
                                <div class="row mb-2">
                                    <div class="col">Room Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->room_charges, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Professional Fees:</div>
                                    <div class="col-auto text-warning" id="currentProfessionalFees">₱{{ number_format($billing->professional_fees, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Medicine Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->medicine_charges, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Laboratory Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->lab_charges, 2) }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Other Charges:</div>
                                    <div class="col-auto">₱{{ number_format($billing->other_charges, 2) }}</div>
                                </div>
                                <hr>

                                <!-- Updated Totals -->
                                <div class="row mb-2">
                                    <div class="col"><strong>New Subtotal:</strong></div>
                                    <div class="col-auto"><strong id="newSubtotal">₱{{ number_format($billing->total_amount, 2) }}</strong></div>
                                </div>
                                
                                @if($billing->is_philhealth_member)
                                    <div class="row mb-2 text-success">
                                        <div class="col">PhilHealth Deduction:</div>
                                        <div class="col-auto" id="newPhilhealthDeduction">-₱{{ number_format($billing->philhealth_deduction, 2) }}</div>
                                    </div>
                                @endif

                                <div class="row mb-2 text-success">
                                    <div class="col">Senior/PWD Discount:</div>
                                    <div class="col-auto" id="newSeniorPwdDiscount">-₱{{ number_format($billing->senior_pwd_discount, 2) }}</div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <div class="row">
                                    <div class="col"><h5><strong>New Net Amount:</strong></h5></div>
                                    <div class="col-auto"><h5 class="text-primary"><strong id="newNetAmount">₱{{ number_format($billing->net_amount, 2) }}</strong></h5></div>
                                </div>

                                <!-- Comparison -->
                                <div class="mt-3 p-2 bg-light rounded">
                                    <small class="text-muted">
                                        <strong>Difference:</strong> 
                                        <span id="amountDifference">₱0.00</span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- All Billing Items (Read-only) -->
                        <div class="card shadow">
                            <div class="card-header bg-dark text-white">
                                <h6 class="mb-0"><i class="fas fa-list"></i> All Billing Items</h6>
                            </div>
                            <div class="card-body">
                                <div style="max-height: 300px; overflow-y: auto;">
                                    @foreach($billing->billingItems as $item)
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="badge 
                                                    @if($item->item_type === 'room') bg-primary
                                                    @elseif($item->item_type === 'medicine') bg-success
                                                    @elseif($item->item_type === 'laboratory') bg-info
                                                    @elseif($item->item_type === 'professional') bg-warning
                                                    @else bg-secondary
                                                    @endif">
                                                    {{ $item->getFormattedItemType() }}
                                                </span>
                                                <strong>₱{{ number_format($item->total_amount, 2) }}</strong>
                                            </div>
                                            <small class="text-muted">{{ $item->description }}</small>
                                            @if($item->icd_code)
                                                <br><code class="small">{{ $item->icd_code }}</code>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('billing.show', $billing) }}" class="btn btn-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save"></i> Update Billing
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const professionalFeesInput = document.getElementById('professional_fees');
    const seniorCheckbox = document.getElementById('is_senior_citizen');
    const pwdCheckbox = document.getElementById('is_pwd');
    
    const originalValues = {
        roomCharges: {{ $billing->room_charges }},
        professionalFees: {{ $billing->professional_fees }},
        medicineCharges: {{ $billing->medicine_charges }},
        labCharges: {{ $billing->lab_charges }},
        otherCharges: {{ $billing->other_charges }},
        philhealthDeduction: {{ $billing->philhealth_deduction }},
        isPhilhealthMember: {{ $billing->is_philhealth_member ? 'true' : 'false' }},
        originalNetAmount: {{ $billing->net_amount }}
    };
    
    function calculateUpdatedTotals() {
        const newProfessionalFees = parseFloat(professionalFeesInput.value) || 0;
        const isSenior = seniorCheckbox.checked;
        const isPwd = pwdCheckbox.checked;
        
        // Calculate new subtotal
        const newSubtotal = originalValues.roomCharges + newProfessionalFees + 
                           originalValues.medicineCharges + originalValues.labCharges + 
                           originalValues.otherCharges;
        
        // Calculate PhilHealth deduction (simplified - same percentage as original)
        let newPhilhealthDeduction = 0;
        if (originalValues.isPhilhealthMember && originalValues.philhealthDeduction > 0) {
            const originalPhilhealthPercentage = originalValues.philhealthDeduction / (originalValues.roomCharges + originalValues.professionalFees + originalValues.medicineCharges + originalValues.labCharges + originalValues.otherCharges);
            newPhilhealthDeduction = newSubtotal * originalPhilhealthPercentage;
        }
        
        // Calculate senior/PWD discount
        let newSeniorPwdDiscount = 0;
        if (isSenior || isPwd) {
            newSeniorPwdDiscount = newSubtotal * 0.20; // 20% discount
        }
        
        // Calculate new net amount
        const newNetAmount = newSubtotal - newPhilhealthDeduction - newSeniorPwdDiscount;
        
        // Update display
        document.getElementById('currentProfessionalFees').textContent = '₱' + newProfessionalFees.toFixed(2);
        document.getElementById('newSubtotal').textContent = '₱' + newSubtotal.toFixed(2);
        document.getElementById('newPhilhealthDeduction').textContent = '-₱' + newPhilhealthDeduction.toFixed(2);
        document.getElementById('newSeniorPwdDiscount').textContent = '-₱' + newSeniorPwdDiscount.toFixed(2);
        document.getElementById('newNetAmount').textContent = '₱' + newNetAmount.toFixed(2);
        
        // Calculate and show difference
        const difference = newNetAmount - originalValues.originalNetAmount;
        const differenceElement = document.getElementById('amountDifference');
        differenceElement.textContent = (difference >= 0 ? '+' : '') + '₱' + difference.toFixed(2);
        differenceElement.className = difference >= 0 ? 'text-success' : 'text-danger';
    }
    
    // Add event listeners
    professionalFeesInput.addEventListener('input', calculateUpdatedTotals);
    seniorCheckbox.addEventListener('change', calculateUpdatedTotals);
    pwdCheckbox.addEventListener('change', calculateUpdatedTotals);
    
    // Initial calculation
    calculateUpdatedTotals();
});
</script>
@endsection

@section('styles')
<style>
.position-sticky {
    position: sticky;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.border-bottom:last-child {
    border-bottom: none !important;
}

.card .card-body {
    position: relative;
}

@media (max-width: 768px) {
    .position-sticky {
        position: relative !important;
        top: auto !important;
    }
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
    showBillingLoading('Updating billing record...');
});
</script>

@endsection

@section('styles')
<style>
/* Billing Card & Table Enhancements */
.card.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Card header color consistency */
.card-header.bg-info {
    background-color: #0dcaf0 !important;
}
.card-header.bg-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}
.card-header.bg-primary {
    background-color: #0d6efd !important;
}
.card-header.bg-success {
    background-color: #198754 !important;
}
.card-header.bg-secondary {
    background-color: #6c757d !important;
}
.card-header.bg-dark {
    background-color: #212529 !important;
}

/* Input styling enhancements */
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
@endsection