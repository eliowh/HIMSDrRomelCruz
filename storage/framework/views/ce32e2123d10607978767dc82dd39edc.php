<?php $__env->startSection('title', 'Edit Billing - ' . $billing->billing_number); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-edit text-warning"></i> Edit Billing</h2>
                <div class="btn-group">
                    <a href="<?php echo e(route('billing.show', $billing)); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Details
                    </a>
                    <a href="<?php echo e(route('billing.dashboard')); ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-list"></i> All Billings
                    </a>
                </div>
            </div>

            <?php if($errors->any()): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?php echo e(route('billing.update', $billing)); ?>" method="POST" id="editBillingForm">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
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
                                        <p><strong>Name:</strong> <?php echo e($billing->patient->display_name ?? 'N/A'); ?></p>
                                        <p><strong>Date of Birth:</strong> <?php echo e($billing->patient->date_of_birth ? $billing->patient->date_of_birth->format('M d, Y') : 'N/A'); ?></p>
                                        <p><strong>Billing Number:</strong> <?php echo e($billing->billing_number); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Billing Date:</strong> <?php echo e($billing->billing_date ? $billing->billing_date->format('M d, Y g:i A') : 'N/A'); ?></p>
                                        <p><strong>Created By:</strong> <?php echo e($billing->createdBy ? $billing->createdBy->name : 'N/A'); ?></p>
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
                                
                                <?php
                                    $professionalFeeOnly = $billing->billingItems->where('item_type', 'professional')->sum('unit_price');
                                ?>
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
                                                   value="<?php echo e(old('professional_fees', $professionalFeeOnly)); ?>" 
                                                   min="0" 
                                                   step="0.01" 
                                                   required>
                                        </div>
                                        <small class="text-muted">Editable professional fee portion only</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Professional Fee Breakdown</label>
                                        <div class="border rounded p-2 bg-light" style="max-height: 200px; overflow-y: auto;">
                                            <?php $__empty_1 = true; $__currentLoopData = $billing->billingItems->where('item_type', 'professional'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <div class="mb-2">
                                                    <div class="fw-bold"><?php echo e($item->description); ?></div>
                                                    <?php if($item->case_rate > 0): ?>
                                                        <div class="d-flex justify-content-between">
                                                            <small class="text-muted">Case Rate:</small>
                                                            <small class="text-success">₱<?php echo e(number_format($item->case_rate, 2)); ?></small>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="d-flex justify-content-between">
                                                        <small class="text-muted">Professional Fee:</small>
                                                        <small class="text-primary">₱<?php echo e(number_format($item->unit_price, 2)); ?></small>
                                                    </div>
                                                    <div class="d-flex justify-content-between border-top pt-1">
                                                        <small class="fw-bold">Total:</small>
                                                        <small class="fw-bold">₱<?php echo e(number_format($item->total_amount, 2)); ?></small>
                                                    </div>
                                                </div>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <span class="text-muted">No professional fee items</span>
                                            <?php endif; ?>
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
                                                       <?php echo e(old('is_senior_citizen', $billing->is_senior_citizen) ? 'checked' : ''); ?>>
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
                                                       <?php echo e(old('is_pwd', $billing->is_pwd) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="is_pwd">
                                                    Person with Disability (20% Discount)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Billing Status</label>
                                        <div class="mt-2">
                                            <?php if($billing->status === 'pending'): ?>
                                                <span class="badge bg-warning">Pending Payment</span>
                                            <?php elseif($billing->status === 'paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif($billing->status === 'cancelled'): ?>
                                                <span class="badge bg-danger">Cancelled</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($billing->status)); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block mt-2">Billing status cannot be changed here. Use payment actions in Payment Management.</small>
                                    </div>
                                </div>

                                <!-- PhilHealth Status (Editable) -->
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <label class="form-label">
                                            <i class="fas fa-shield-alt"></i>
                                            PhilHealth Coverage
                                        </label>
                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="is_philhealth_member" 
                                                   id="is_philhealth_member_edit"
                                                   value="1"
                                                   <?php echo e(old('is_philhealth_member', $billing->is_philhealth_member) ? 'checked' : ''); ?>

                                                   onchange="recalculateEditTotals()">
                                            <label class="form-check-label" for="is_philhealth_member_edit">
                                                PhilHealth Member (Coverage Applied)
                                            </label>
                                        </div>
                                        <small class="text-muted">Check this to apply PhilHealth coverage and deductions</small>
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
                                          placeholder="Add any additional notes or comments..."><?php echo e(old('notes', $billing->notes)); ?></textarea>
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
                                    <div class="col-auto">₱<?php echo e(number_format($billing->room_charges ?? 0, 2)); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Professional Fees:</div>
                                    <div class="col-auto text-warning" id="currentProfessionalFees">₱<?php echo e(number_format($billing->professional_fees ?? 0, 2)); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Medicine Charges:</div>
                                    <div class="col-auto">₱<?php echo e(number_format($billing->medicine_charges ?? 0, 2)); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Laboratory Charges:</div>
                                    <div class="col-auto">₱<?php echo e(number_format($billing->lab_charges ?? 0, 2)); ?></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col">Other Charges:</div>
                                    <div class="col-auto">₱<?php echo e(number_format($billing->other_charges ?? 0, 2)); ?></div>
                                </div>
                                <hr>

                                <!-- Updated Totals -->
                                <div class="row mb-2">
                                    <div class="col"><strong>New Subtotal:</strong></div>
                                    <div class="col-auto"><strong id="newSubtotal">₱<?php echo e(number_format($billing->total_amount ?? 0, 2)); ?></strong></div>
                                </div>
                                
                                <?php if($billing->is_philhealth_member): ?>
                                    <div class="row mb-2 text-success">
                                        <div class="col">PhilHealth Deduction:</div>
                                        <div class="col-auto" id="newPhilhealthDeduction">-₱<?php echo e(number_format($billing->philhealth_deduction ?? 0, 2)); ?></div>
                                    </div>
                                <?php endif; ?>

                                <div class="row mb-2 text-success">
                                    <div class="col">Senior/PWD Discount:</div>
                                    <div class="col-auto" id="newSeniorPwdDiscount">-₱<?php echo e(number_format($billing->senior_pwd_discount ?? 0, 2)); ?></div>
                                </div>
                                
                                <hr class="my-3">
                                
                                <div class="row">
                                    <div class="col"><h5><strong>New Net Amount:</strong></h5></div>
                                    <div class="col-auto"><h5 class="text-primary"><strong id="newNetAmount">₱<?php echo e(number_format($billing->net_amount ?? 0, 2)); ?></strong></h5></div>
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
                                    <?php $__currentLoopData = $billing->billingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="border-bottom pb-2 mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span class="badge 
                                                    <?php if($item->item_type === 'room'): ?> bg-primary
                                                    <?php elseif($item->item_type === 'medicine'): ?> bg-success
                                                    <?php elseif($item->item_type === 'laboratory'): ?> bg-info
                                                    <?php elseif($item->item_type === 'professional'): ?> bg-warning
                                                    <?php else: ?> bg-secondary
                                                    <?php endif; ?>">
                                                    <?php echo e($item->getFormattedItemType()); ?>

                                                </span>
                                                <strong>₱<?php echo e(number_format($item->total_amount, 2)); ?></strong>
                                            </div>
                                            <small class="text-muted"><?php echo e($item->description); ?></small>
                                            <?php if($item->icd_code): ?>
                                                <br><code class="small"><?php echo e($item->icd_code); ?></code>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="<?php echo e(route('billing.show', $billing)); ?>" class="btn btn-secondary">
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const professionalFeesInput = document.getElementById('professional_fees');
    const seniorCheckbox = document.getElementById('is_senior_citizen');
    const pwdCheckbox = document.getElementById('is_pwd');
    
    const originalValues = {
        roomCharges: <?php echo e($billing->room_charges ?? 0); ?>,
        professionalFees: <?php echo e($billing->professional_fees ?? 0); ?>,
        medicineCharges: <?php echo e($billing->medicine_charges ?? 0); ?>,
        labCharges: <?php echo e($billing->lab_charges ?? 0); ?>,
        otherCharges: <?php echo e($billing->other_charges ?? 0); ?>,
        philhealthDeduction: <?php echo e($billing->philhealth_deduction ?? 0); ?>,
        isPhilhealthMember: <?php echo e($billing->is_philhealth_member ? 'true' : 'false'); ?>,
        originalNetAmount: <?php echo e($billing->net_amount ?? 0); ?>

    };
    
    function calculateUpdatedTotals() {
        const newProfessionalFees = parseFloat(professionalFeesInput.value) || 0;
        const isSenior = seniorCheckbox.checked;
        const isPwd = pwdCheckbox.checked;
        
        // Calculate new subtotal
        const newSubtotal = originalValues.roomCharges + newProfessionalFees + 
                           originalValues.medicineCharges + originalValues.labCharges + 
                           originalValues.otherCharges;
        
        // PhilHealth deduction is sum of case_rate values when the checkbox is checked.
        let newPhilhealthDeduction = 0;
        const isPhilhealthMemberChecked = document.getElementById('is_philhealth_member_edit').checked;
        if (isPhilhealthMemberChecked) {
            // Sum case_rates from billing items (server-side values are authoritative)
            <?php
                $caseRateTotal = $billing->billingItems->where('item_type', 'professional')->sum(function($it) { return $it->case_rate * ($it->quantity ?: 1); });
            ?>
            // If original billing had case rates, keep same total case rate amount proportional to quantity (we'll use stored value)
            // For the edit form we cannot recompute case rates client-side reliably, so fall back to the stored philhealth deduction if present
            if (originalValues.isPhilhealthMember && originalValues.philhealthDeduction > 0) {
                newPhilhealthDeduction = originalValues.philhealthDeduction;
            } else {
                newPhilhealthDeduction = <?php echo e($caseRateTotal ?? 0); ?>;
            }
        }
        
        // Calculate senior/PWD discount
        let newSeniorPwdDiscount = 0;
        if (isSenior || isPwd) {
            // Senior/PWD discount applies after PhilHealth deduction
            newSeniorPwdDiscount = (newSubtotal - newPhilhealthDeduction) * 0.20; // 20% discount
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
    // Also check patient's last philhealth status to prevent accidental unchecking
    try {
        const patientId = <?php echo e($billing->patient_id ?? 'null'); ?>;
        if (patientId) {
            fetch('<?php echo e(route("billing.last.philhealth")); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ patient_id: patientId })
            }).then(r => r.json()).then(data => {
                const philCheckbox = document.getElementById('is_philhealth_member_edit');
                if (!philCheckbox) return;
                if (data.last_is_philhealth_member) {
                    philCheckbox.checked = true;
                    philCheckbox.disabled = true;
                    calculateUpdatedTotals();
                } else {
                    philCheckbox.disabled = false;
                }
            }).catch(err => console.warn('Failed to fetch last philhealth status', err));
        }
    } catch (err) {
        console.warn('Error checking last philhealth status', err);
    }
});
</script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
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

<?php echo $__env->make('billing.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
// Show notifications for session messages
document.addEventListener('DOMContentLoaded', function() {
    <?php if($errors->any()): ?>
        let errorMessages = '';
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            errorMessages += '<?php echo e($error); ?>\n';
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        showBillingNotification('error', 'Validation Error', errorMessages);
    <?php endif; ?>
});

// Add form submission handler for better UX
document.querySelector('form').addEventListener('submit', function(e) {
    showBillingLoading('Updating billing record...');
});
</script>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
/* Billing Card & Table Enhancements */
.card.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Card header color consistency with green gradient */
.card-header.bg-info {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-warning {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
    color: #fff !important;
}
.card-header.bg-primary {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-success {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-secondary {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}
.card-header.bg-dark {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}

/* Input styling enhancements */
.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}
</style>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.billing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\billing\edit.blade.php ENDPATH**/ ?>