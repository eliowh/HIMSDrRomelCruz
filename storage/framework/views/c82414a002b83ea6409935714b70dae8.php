<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Billing Details - Cashier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo e(asset('css/cashiercss/cashier.css')); ?>">
</head>
<body>
    <?php echo $__env->make('cashier.cashier_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="cashier-layout">
        <?php echo $__env->make('cashier.cashier_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <div class="container-fluid mt-4">
                <div class="row">
                    <div class="col-12">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2><i class="fas fa-file-invoice-dollar text-primary"></i> Billing Details</h2>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="/cashier/billing" class="text-decoration-none">Payment Management</a></li>
                                        <li class="breadcrumb-item active"><?php echo e($billing->billing_number); ?></li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="/cashier/billing" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Payment Management
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Billing Information -->
                            <div class="col-lg-8">
                                <!-- Patient Information -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0"><i class="fas fa-user"></i> Patient Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Patient Name:</strong> <?php echo e($billing->patient->full_name ?? 'Unknown Patient'); ?></p>
                                                <p><strong>Patient No:</strong> <?php echo e($billing->patient->patient_no ?? 'N/A'); ?></p>
                                                <p><strong>Date of Birth:</strong> <?php echo e($billing->patient->date_of_birth ? $billing->patient->date_of_birth->format('M d, Y') : 'N/A'); ?></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Address:</strong> 
                                                    <?php echo e(implode(', ', array_filter([$billing->patient->barangay ?? '', $billing->patient->city ?? '', $billing->patient->province ?? ''])) ?: 'N/A'); ?>

                                                </p>
                                                
                                                <p><strong>Room No:</strong> <?php echo e($billing->admission->room_no ?? $billing->patient->room_no ?? 'N/A'); ?></p>
                                                <p><strong>Doctor:</strong> <?php echo e($billing->admission->doctor_name ?? $billing->patient->doctor_name ?? 'N/A'); ?></p>
                                                <p><strong>Admission Date:</strong> <?php echo e($billing->admission && $billing->admission->admission_date ? $billing->admission->admission_date->format('M d, Y') : 'N/A'); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Billing Items -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-info text-white">
                                        <h5 class="mb-0"><i class="fas fa-list"></i> Billing Breakdown</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Item Type</th>
                                                        <th>Description</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if($billing->room_charges > 0): ?>
                                                    <tr>
                                                        <td><span class="badge bg-secondary">Room</span></td>
                                                        <td>Room Charges</td>
                                                        <td class="text-end">₱<?php echo e(number_format($billing->room_charges, 2)); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if($billing->professional_fees > 0): ?>
                                                    <tr>
                                                        <td><span class="badge bg-primary">Professional</span></td>
                                                        <td>Professional Fees</td>
                                                        <td class="text-end">₱<?php echo e(number_format($billing->professional_fees, 2)); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if($billing->medicine_charges > 0): ?>
                                                    <tr>
                                                        <td><span class="badge bg-success">Medicine</span></td>
                                                        <td>Medicine Charges</td>
                                                        <td class="text-end">₱<?php echo e(number_format($billing->medicine_charges, 2)); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if($billing->lab_charges > 0): ?>
                                                    <tr>
                                                        <td><span class="badge bg-warning text-dark">Laboratory</span></td>
                                                        <td>Laboratory Charges</td>
                                                        <td class="text-end">₱<?php echo e(number_format($billing->lab_charges, 2)); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                    <?php if($billing->other_charges > 0): ?>
                                                    <tr>
                                                        <td><span class="badge bg-info">Other</span></td>
                                                        <td>Other Charges</td>
                                                        <td class="text-end">₱<?php echo e(number_format($billing->other_charges, 2)); ?></td>
                                                    </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="table-active">
                                                        <th colspan="2">Total Amount</th>
                                                        <th class="text-end">₱<?php echo e(number_format($billing->total_amount, 2)); ?></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Summary & Actions -->
                            <div class="col-lg-4">
                                <!-- Payment Status -->
                                <div class="card shadow mb-4">
                                    <div class="card-header <?php echo e($billing->status === 'paid' ? 'bg-success' : 'bg-warning'); ?> text-<?php echo e($billing->status === 'paid' ? 'white' : 'dark'); ?>">
                                        <h6 class="mb-0">
                                            <i class="fas fa-<?php echo e($billing->status === 'paid' ? 'check-circle' : 'clock'); ?>"></i> 
                                            Payment Status
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <?php if($billing->status === 'paid'): ?>
                                            <h3 class="text-success"><i class="fas fa-check-circle"></i> PAID</h3>
                                            <?php if($billing->payment_date): ?>
                                                <p class="text-muted">
                                                    <strong>Payment Date:</strong><br>
                                                    <?php echo e($billing->payment_date->format('M d, Y h:i A')); ?>

                                                </p>
                                                <p class="text-muted">
                                                    <small><?php echo e($billing->payment_date->diffForHumans()); ?></small>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <?php if($billing->payment_amount && $billing->change_amount !== null): ?>
                                            <div class="mt-3 p-3 bg-light rounded">
                                                <div class="row text-start">
                                                    <div class="col-sm-6">
                                                        <strong class="text-info">
                                                            <i class="fas fa-money-bill-wave"></i> Amount Received:
                                                        </strong><br>
                                                        <span class="fs-5 text-success">₱<?php echo e(number_format($billing->payment_amount, 2)); ?></span>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <strong class="text-warning">
                                                            <i class="fas fa-exchange-alt"></i> Change Given:
                                                        </strong><br>
                                                        <span class="fs-5 text-danger">₱<?php echo e(number_format($billing->change_amount, 2)); ?></span>
                                                    </div>
                                                </div>
                                                <?php if($billing->processedBy): ?>
                                                <div class="mt-2 text-start">
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> Processed by: <strong><?php echo e($billing->processedBy->name); ?></strong>
                                                    </small>
                                                </div>
                                                <?php endif; ?>
                                            </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <h3 class="text-warning"><i class="fas fa-clock"></i> PENDING</h3>
                                            <p class="text-muted">Awaiting payment processing</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Payment Summary -->
                                <div class="card shadow mb-4">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0"><i class="fas fa-calculator"></i> Payment Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="row mb-2">
                                                <div class="col">Subtotal:</div>
                                                <div class="col-auto">₱<?php echo e(number_format($billing->total_amount ?? 0, 2)); ?></div>
                                            </div>
                                            
                                            <?php if($billing->is_philhealth_member && $billing->philhealth_deduction > 0): ?>
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
                                            
                                            <hr>
                                            
                                            <div class="row">
                                                <div class="col"><h5><strong>Net Amount:</strong></h5></div>
                                                <div class="col-auto"><h5 class="text-primary"><strong>₱<?php echo e(number_format($billing->net_amount ?? 0, 2)); ?></strong></h5></div>
                                            </div>
                                            
                                            <?php if($billing->status === 'paid' && $billing->payment_amount): ?>
                                            <hr class="my-3">
                                            <h6 class="text-success mb-2"><i class="fas fa-money-check-alt"></i> Payment Transaction</h6>
                                            <div class="row mb-1">
                                                <div class="col text-success"><strong>Amount Paid:</strong></div>
                                                <div class="col-auto text-success"><strong>₱<?php echo e(number_format($billing->payment_amount, 2)); ?></strong></div>
                                            </div>
                                            <?php if($billing->change_amount > 0): ?>
                                            <div class="row">
                                                <div class="col text-warning"><strong>Change Returned:</strong></div>
                                                <div class="col-auto text-warning"><strong>₱<?php echo e(number_format($billing->change_amount, 2)); ?></strong></div>
                                            </div>
                                            <?php endif; ?>
                                            <?php endif; ?>
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

                                <!-- Payment Actions -->
                                <div class="card shadow">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0"><i class="fas fa-credit-card"></i> Payment Actions</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <?php if($billing->status === 'pending'): ?>
                                                <button type="button" 
                                                        class="btn btn-success mark-as-paid-btn" 
                                                        data-billing-id="<?php echo e($billing->id); ?>"
                                                        data-billing-number="<?php echo e($billing->billing_number); ?>">
                                                    <i class="fas fa-check-circle"></i> Mark as Paid
                                                </button>
                                            <?php elseif($billing->status === 'paid'): ?>
                                                <!-- Receipt Actions for Paid Billings -->
                                                <div class="d-grid gap-2">
                                                    <a href="<?php echo e(route('cashier.billing.receipt', $billing->id)); ?>" 
                                                       target="_blank" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-print"></i> Print Receipt
                                                    </a>
                                                    <a href="<?php echo e(route('cashier.billing.receipt.download', $billing->id)); ?>" 
                                                       class="btn btn-success">
                                                        <i class="fas fa-download"></i> Download Receipt
                                                    </a>
                                                </div>
                                                <hr>
                                                <div class="alert alert-success">
                                                    <i class="fas fa-check-circle"></i> Payment has been finalized and cannot be reverted for security reasons.
                                                </div>
                                            <?php endif; ?>
                                            <a href="/cashier/billing" class="btn btn-outline-primary">
                                                <i class="fas fa-list"></i> Back to Billing List
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php echo $__env->make('cashier.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Payment Processing Functions
    document.addEventListener('DOMContentLoaded', function() {
        // Mark as Paid button
        const markAsPaidBtn = document.querySelector('.mark-as-paid-btn');
        if (markAsPaidBtn) {
            markAsPaidBtn.addEventListener('click', function() {
                const billingId = this.dataset.billingId;
                markBillingAsPaid(billingId, this);
            });
        }
        
        // Mark as Unpaid button
        const markAsUnpaidBtn = document.querySelector('.mark-as-unpaid-btn');
        if (markAsUnpaidBtn) {
            markAsUnpaidBtn.addEventListener('click', function() {
                const billingId = this.dataset.billingId;
                markBillingAsUnpaid(billingId, this);
            });
        }
    });

    async function markBillingAsPaid(billingId, button) {
        const billingNumber = button.dataset.billingNumber;
        
        const confirmed = await confirmPaymentAction(
            `Mark billing ${billingNumber} as PAID?\n\nThis will:\n• Record the payment timestamp\n• Update the billing status to PAID\n• Complete the payment process`, 
            'Confirm Payment Processing'
        );
        
        if (!confirmed) return;
        
        try {
            showBillingLoading('Processing payment confirmation...');
            
            button.disabled = true;
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            const response = await fetch(`/cashier/billing/${billingId}/mark-as-paid`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showBillingNotification('success', 'Payment Processed', 
                    `Billing ${billingNumber} has been successfully marked as PAID. Click OK to refresh and see the updated status.`);
            } else {
                closeBillingNotification();
                showBillingNotification('error', 'Payment Error', data.message);
                button.disabled = false;
                button.innerHTML = originalText;
            }
        } catch (error) {
            closeBillingNotification();
            showBillingNotification('error', 'Network Error', 'Failed to process payment: ' + error.message);
            button.disabled = false;
            button.innerHTML = originalText;
        }
    }

    // Revert functionality removed for security - preventing payment theft
    </script>

    <style>
    /* Global Box Sizing Fix */
    * {
        box-sizing: border-box;
    }

    /* Layout Improvements */
    .main-content {
        padding: 20px;
        min-height: 100vh;
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }

    .container-fluid {
        padding-left: 15px;
        padding-right: 15px;
        width: 100%;
        max-width: none;
    }

    .row {
        margin-left: -15px;
        margin-right: -15px;
    }

    .col-12, .col-lg-8, .col-lg-4, .col-md-6 {
        padding-left: 15px;
        padding-right: 15px;
        width: 100%;
        max-width: 100%;
    }

    @media (min-width: 992px) {
        .col-lg-8 {
            flex: 0 0 66.666667%;
            max-width: 66.666667%;
        }
        .col-lg-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
        }
    }

    @media (min-width: 768px) {
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    /* Card Improvements */
    .card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
        width: 100%;
        max-width: 100%;
        overflow: hidden;
    }

    .card-body {
        padding: 1.5rem;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0,0,0,.125);
    }

    /* Table Responsive Fix */
    .table-responsive {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        width: 100%;
        max-width: 100%;
        margin-bottom: 0;
        table-layout: fixed;
    }

    .table td, .table th {
        padding: 0.75rem;
        vertical-align: top;
        border-top: 1px solid #dee2e6;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Breadcrumb Styling */
    .breadcrumb {
        background: none;
        padding: 0;
        margin: 0;
        font-size: 0.9rem;
        word-wrap: break-word;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: #6c757d;
    }

    /* Header Gradients */
    .card-header.bg-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%) !important;
    }

    .card-header.bg-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #6f42c1 100%) !important;
    }

    .card-header.bg-success {
        background: linear-gradient(135deg, #198754 0%, #20c997 100%) !important;
    }

    .card-header.bg-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%) !important;
    }

    .card-header.bg-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%) !important;
    }

    /* Table Styling */
    .table-striped > tbody > tr:nth-of-type(odd) > td {
        background-color: rgba(0, 0, 0, 0.05);
    }

    /* Button Improvements */
    .btn {
        border-radius: 6px;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border: none;
        transition: all 0.2s ease;
        word-wrap: break-word;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .d-grid .btn {
        white-space: normal;
        word-wrap: break-word;
    }

    /* Alert Styling */
    .alert-success {
        border-radius: 8px;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }

    /* Text Handling */
    p, h1, h2, h3, h4, h5, h6 {
        word-wrap: break-word;
        overflow-wrap: break-word;
        hyphens: auto;
    }

    /* Mobile Responsive */
    @media (max-width: 767px) {
        .main-content {
            padding: 10px;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        .card-header {
            padding: 0.75rem 1rem;
        }
        
        .btn {
            font-size: 0.875rem;
            padding: 0.4rem 0.8rem;
        }
        
        .table td, .table th {
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .d-flex.gap-2 {
            flex-direction: column;
            width: 100%;
        }
    }

    /* Prevent Horizontal Overflow */
    html, body {
        overflow-x: hidden;
        width: 100%;
        max-width: 100%;
    }

    .cashier-layout {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
    }
    </style>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/cashier/cashier_billing_view.blade.php ENDPATH**/ ?>