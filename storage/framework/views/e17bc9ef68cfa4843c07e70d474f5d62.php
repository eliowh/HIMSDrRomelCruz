<?php $__env->startSection('title', 'Billing Details - ' . $billing->billing_number); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-invoice text-primary"></i> Billing Details</h2>
                <div class="btn-group">
                    <a href="<?php echo e(route('billing.dashboard')); ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Billings
                    </a>
                    <?php if($billing->status !== 'paid'): ?>
                    <a href="<?php echo e(route('billing.edit', $billing)); ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <?php else: ?>
                    <span class="btn btn-outline-secondary disabled" title="Cannot edit paid billing">
                        <i class="fas fa-lock"></i> Billing Finalized
                    </span>
                    <?php endif; ?>
                    <a href="<?php echo e(route('billing.export.receipt', $billing)); ?>" class="btn btn-success" target="_blank">
                        <i class="fas fa-file-pdf"></i> Export Receipt
                    </a>
                </div>
            </div>

            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Billing Information -->
                <div class="col-md-8">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-info-circle"></i> Billing Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Billing Number:</strong></td>
                                            <td><?php echo e($billing->billing_number); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Patient:</strong></td>
                                            <td><?php echo e($billing->patient->display_name); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Date of Birth:</strong></td>
                                            <td><?php echo e($billing->patient->date_of_birth ? $billing->patient->date_of_birth->format('M d, Y') : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Billing Date:</strong></td>
                                            <td><?php echo e($billing->billing_date ? $billing->billing_date->format('M d, Y g:i A') : 'N/A'); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Created By:</strong></td>
                                            <td><?php echo e($billing->createdBy->name); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <?php if($billing->status === 'pending'): ?>
                                                    <span class="badge bg-warning fs-6">Pending</span>
                                                <?php elseif($billing->status === 'paid'): ?>
                                                    <span class="badge bg-success fs-6">Paid</span>
                                                <?php elseif($billing->status === 'cancelled'): ?>
                                                    <span class="badge bg-danger fs-6">Cancelled</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>PhilHealth Member:</strong></td>
                                            <td>
                                                <?php if($billing->is_philhealth_member): ?>
                                                    <span class="badge bg-info">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Senior Citizen:</strong></td>
                                            <td>
                                                <?php if($billing->is_senior_citizen): ?>
                                                    <span class="badge bg-warning">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>PWD:</strong></td>
                                            <td>
                                                <?php if($billing->is_pwd): ?>
                                                    <span class="badge bg-warning">Yes</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">No</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <?php if($billing->notes): ?>
                                <div class="mt-3">
                                    <h6><strong>Notes:</strong></h6>
                                    <p class="text-muted"><?php echo e($billing->notes); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Billing Items -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-list-ul"></i> Billing Items</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped">
                                    <thead class="table-success">
                                        <tr>
                                            <th class="text-black">Type</th>
                                            <th class="text-black">Description</th>
                                            <th class="text-black">ICD-10</th>
                                            <th class="text-black">Qty</th>
                                            <th class="text-black">Unit Price</th>
                                            <th class="text-black">Total</th>
                                            <th class="text-black">Date Charged</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__currentLoopData = $billing->billingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td>
                                                <span class="badge 
                                                    <?php if($item->item_type === 'room'): ?> bg-primary
                                                    <?php elseif($item->item_type === 'medicine'): ?> bg-success
                                                    <?php elseif($item->item_type === 'laboratory'): ?> bg-info
                                                    <?php elseif($item->item_type === 'professional'): ?> bg-warning
                                                    <?php else: ?> bg-secondary
                                                    <?php endif; ?>">
                                                    <?php echo e($item->getFormattedItemType()); ?>

                                                </span>
                                            </td>
                                            <td><?php echo e($item->description); ?></td>
                                            <td>
                                                <?php if($item->icd_code): ?>
                                                    <code><?php echo e($item->icd_code); ?></code>
                                                    <?php
                                                        $icdData = $item->icd10NamePriceRate();
                                                    ?>
                                                    <?php if($icdData): ?>
                                                        <br><small class="text-muted"><?php echo e(Str::limit($icdData->description, 30)); ?></small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="text-muted">N/A</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo e($item->quantity); ?></td>
                                            <td>₱<?php echo e(number_format($item->unit_price ?? 0, 2)); ?></td>
                                            <td><strong>₱<?php echo e(number_format($item->total_amount ?? 0, 2)); ?></strong></td>
                                            <td><?php echo e($item->date_charged ? $item->date_charged->format('M d, Y') : 'N/A'); ?></td>
                                        </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Billing Summary -->
                <div class="col-md-4">
                    <div class="card shadow mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-calculator"></i> Billing Summary</h5>
                        </div>
                        <div class="card-body">
                            <!-- Breakdown by Category -->
                            <div class="mb-4">
                                <h6 class="text-muted mb-3">Charges Breakdown</h6>
                                <div class="row mb-2">
                                    <div class="col">Room Charges:</div>
                                    <div class="col-auto">₱<?php echo e(number_format($billing->room_charges ?? 0, 2)); ?></div>
                                </div>
                                <?php
                                    $caseRateTotal = $billing->billingItems->where('item_type', 'professional')->sum('case_rate');
                                    $professionalFeeTotal = $billing->billingItems->where('item_type', 'professional')->sum('unit_price');
                                ?>
                                <?php if($caseRateTotal > 0): ?>
                                <div class="row mb-2">
                                    <div class="col ps-3">Case Rate:</div>
                                    <div class="col-auto text-success">₱<?php echo e(number_format($caseRateTotal, 2)); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if($professionalFeeTotal > 0): ?>
                                <div class="row mb-2">
                                    <div class="col ps-3">Professional Fee:</div>
                                    <div class="col-auto text-primary">₱<?php echo e(number_format($professionalFeeTotal, 2)); ?></div>
                                </div>
                                <?php endif; ?>
                                <div class="row mb-2">
                                    <div class="col">Professional Fees Total:</div>
                                    <div class="col-auto fw-bold">₱<?php echo e(number_format($billing->professional_fees ?? 0, 2)); ?></div>
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
                            </div>

                            <!-- Total Calculation -->
                            <div class="mb-3">
                                <div class="row mb-2">
                                    <div class="col"><strong>Subtotal:</strong></div>
                                    <div class="col-auto"><strong>₱<?php echo e(number_format($billing->total_amount ?? 0, 2)); ?></strong></div>
                                </div>
                                
                                <?php if($billing->is_philhealth_member): ?>
                                    <div class="row mb-2 text-success">
                                        <div class="col">PhilHealth Deduction:</div>
                                        <div class="col-auto">-₱<?php echo e(number_format($billing->philhealth_deduction ?? 0, 2)); ?></div>
                                    </div>
                                <?php endif; ?>

                                <?php if($billing->senior_pwd_discount > 0): ?>
                                    <div class="row mb-2 text-success">
                                        <div class="col">
                                            <?php if($billing->is_senior_citizen && $billing->is_pwd): ?>
                                                Senior & PWD Discount:
                                            <?php elseif($billing->is_senior_citizen): ?>
                                                Senior Citizen Discount:
                                            <?php else: ?>
                                                PWD Discount:
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-auto">-₱<?php echo e(number_format($billing->senior_pwd_discount ?? 0, 2)); ?></div>
                                    </div>
                                <?php endif; ?>
                                
                                <hr class="my-3">
                                
                                <div class="row">
                                    <div class="col"><h5><strong>Net Amount:</strong></h5></div>
                                    <div class="col-auto"><h5 class="text-primary"><strong>₱<?php echo e(number_format($billing->net_amount ?? 0, 2)); ?></strong></h5></div>
                                </div>
                            </div>

                            <!-- Savings Summary -->
                            <?php if($billing->philhealth_deduction > 0 || $billing->senior_pwd_discount > 0): ?>
                                <div class="alert alert-success">
                                    <h6 class="mb-2"><i class="fas fa-piggy-bank"></i> Total Savings</h6>
                                    <h5 class="mb-0 text-success">₱<?php echo e(number_format($billing->philhealth_deduction + $billing->senior_pwd_discount, 2)); ?></h5>
                                    <small class="text-muted">
                                        <?php echo e(number_format((($billing->philhealth_deduction + $billing->senior_pwd_discount) / $billing->total_amount) * 100, 1)); ?>% 
                                        of total charges
                                    </small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <?php if($billing->status !== 'paid'): ?>
                                <a href="<?php echo e(route('billing.edit', $billing)); ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit Billing
                                </a>
                                <?php else: ?>
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-lock"></i> This billing has been finalized and cannot be edited for security reasons.
                                </div>
                                <?php endif; ?>
                                <a href="<?php echo e(route('billing.export.receipt', $billing)); ?>" class="btn btn-success" target="_blank">
                                    <i class="fas fa-file-pdf"></i> Download Receipt
                                </a>

                                <button class="btn btn-info" onclick="window.print()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('styles'); ?>
<style>
/* Billing Card & Table Enhancements */
.table-primary > th {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
}

.table-success > th {
    background-color: #198754 !important;
    border-color: #198754 !important;
}

.table-info > th {
    background-color: #0dcaf0 !important;
    border-color: #0dcaf0 !important;
}

.table-warning > th {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
    color: #000 !important;
}

.table-dark > th {
    background-color: #212529 !important;
    border-color: #212529 !important;
}

/* Ensure tbody text is ALWAYS dark on light backgrounds */
.table tbody td {
    color: #212529 !important;
    background-color: rgba(255, 255, 255, 0.9) !important;
}

.table-striped > tbody > tr:nth-of-type(odd) > td {
    background-color: rgba(0, 0, 0, 0.05) !important;
    color: #212529 !important;
}

/* Card shadow enhancement */
.card.shadow {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

/* Updated card header gradients */
.card-header.bg-primary {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}

.card-header.bg-success {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}

.card-header.bg-info {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}

.card-header.bg-warning {
    background: linear-gradient(135deg, #367F2B, #2d6624) !important;
}

@media print {
    .btn, .card-header, .alert, .navbar, .sidebar {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .container-fluid {
        padding: 0 !important;
    }
}
</style>

<script>
// Show notifications for session messages
document.addEventListener('DOMContentLoaded', function() {
    <?php if(session('success')): ?>
        showBillingNotification('success', 'Success', '<?php echo e(session('success')); ?>');
    <?php endif; ?>
    
    <?php if(session('error')): ?>
        showBillingNotification('error', 'Error', '<?php echo e(session('error')); ?>');
    <?php endif; ?>
});
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.billing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\billing\show.blade.php ENDPATH**/ ?>