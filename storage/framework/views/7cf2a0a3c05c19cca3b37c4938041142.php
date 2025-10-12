<?php $__env->startSection('title', 'Billing Management'); ?>

<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-file-invoice-dollar text-primary"></i> Billing Management</h2>
                <a href="<?php echo e(route('billing.create')); ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Billing
                </a>
            </div>

            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if(session('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo e(session('error')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Patient Billings</h5>
                </div>
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="<?php echo e(route('billing.dashboard')); ?>" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search by billing number, patient name, or patient number..." value="<?php echo e(request('search')); ?>">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="all" <?php echo e(request('status') == 'all' ? 'selected' : ''); ?>>All Status</option>
                                    <option value="pending" <?php echo e(request('status') == 'pending' ? 'selected' : ''); ?>>Pending</option>
                                    <option value="paid" <?php echo e(request('status') == 'paid' ? 'selected' : ''); ?>>Paid</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>
                                    <a href="<?php echo e(route('billing.dashboard')); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i> Clear
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Search Results Summary -->
                    <?php if(request('search') || (request('status') && request('status') !== 'all')): ?>
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Search Results:</strong> Found <?php echo e($billings->total()); ?> billing record(s)
                            <?php if(request('search')): ?>
                                matching "<?php echo e(request('search')); ?>"
                            <?php endif; ?>
                            <?php if(request('status') && request('status') !== 'all'): ?>
                                with status "<?php echo e(ucfirst(request('status'))); ?>"
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th class="text-black">Billing #</th>
                                    <th class="text-black">Patient</th>
                                    <th class="text-black">Total Amount</th>
                                    <th class="text-black">PhilHealth</th>
                                    <th class="text-black">Discount</th>
                                    <th class="text-black">Net Amount</th>
                                    <th class="text-black">Status</th>
                                    <th class="text-black">Date</th>
                                    <th class="text-black">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $billings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary"><?php echo e($billing->billing_number); ?></strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo e($billing->patient->display_name); ?></strong>
                                            <?php if($billing->is_philhealth_member): ?>
                                                <span class="badge bg-info ms-1">PhilHealth</span>
                                            <?php endif; ?>
                                            <?php if($billing->is_senior_citizen): ?>
                                                <span class="badge bg-warning ms-1">Senior</span>
                                            <?php endif; ?>
                                            <?php if($billing->is_pwd): ?>
                                                <span class="badge bg-warning ms-1">PWD</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>₱<?php echo e(number_format($billing->total_amount ?? 0, 2)); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($billing->is_philhealth_member): ?>
                                            <span class="text-success">-₱<?php echo e(number_format($billing->philhealth_deduction ?? 0, 2)); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($billing->senior_pwd_discount > 0): ?>
                                            <span class="text-success">-₱<?php echo e(number_format($billing->senior_pwd_discount ?? 0, 2)); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong class="text-dark">₱<?php echo e(number_format($billing->net_amount ?? 0, 2)); ?></strong>
                                    </td>
                                    <td>
                                        <?php if($billing->status === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif($billing->status === 'paid'): ?>
                                            <span class="badge bg-success">Paid</span>
                                        <?php elseif($billing->status === 'cancelled'): ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo e($billing->billing_date->format('M d, Y')); ?>

                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('billing.show', $billing)); ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if($billing->status !== 'paid'): ?>
                                            <a href="<?php echo e(route('billing.edit', $billing)); ?>" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php else: ?>
                                            <span class="btn btn-sm btn-outline-secondary disabled" 
                                                  title="Cannot edit paid billing">
                                                <i class="fas fa-lock"></i>
                                            </span>
                                            <?php endif; ?>
                                            

                                            
                                            <a href="<?php echo e(route('billing.export.receipt', $billing)); ?>" 
                                               class="btn btn-sm btn-outline-success" 
                                               title="Export Receipt" 
                                               target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-3x mb-3"></i>
                                            <p>No billing records found.</p>
                                            <a href="<?php echo e(route('billing.create')); ?>" class="btn btn-primary">
                                                Create First Billing
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if($billings->hasPages()): ?>
                        <div class="pagination-wrapper">
                            <?php echo $__env->make('components.custom-pagination', ['paginator' => $billings], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Total Billings</h5>
                                    <h3><?php echo e($totalBillings ?? $billings->total()); ?></h3>
                                </div>
                                <i class="fas fa-file-invoice-dollar fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Paid Bills</h5>
                                    <h3><?php echo e($paidBillsCount ?? $billings->where('status', 'paid')->count()); ?></h3>
                                </div>
                                <i class="fas fa-check-circle fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>Pending Bills</h5>
                                    <h3><?php echo e($pendingBillsCount ?? $billings->where('status', 'pending')->count()); ?></h3>
                                </div>
                                <i class="fas fa-clock fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5>PhilHealth Members</h5>
                                    <h3><?php echo e($philhealthMembersCount ?? $billings->where('is_philhealth_member', true)->count()); ?></h3>
                                </div>
                                <i class="fas fa-shield-alt fa-2x opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('billing.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<script>
// Handle billing deletion with notification system
async function handleBillingDelete(event, form) {
    event.preventDefault();
    
    const confirmed = await confirmDeleteAction(
        'This billing record will be permanently deleted along with:\n• All billing items and charges\n• Payment history\n• Associated reports\n\nThis action cannot be undone!', 
        'Delete Billing Record'
    );
    
    if (confirmed) {
        showBillingLoading('Deleting billing record...');
        form.submit();
    }
    
    return false;
}



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

<?php $__env->startSection('styles'); ?>
<style>
/* Billing Card & Table Enhancements */
.table-primary > th {
    background-color: #0d6efd !important;
    border-color: #0d6efd !important;
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
</style>

<!-- Enhanced Search JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when status filter changes
    const statusSelect = document.querySelector('select[name="status"]');
    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            this.closest('form').submit();
        });
    }

    // Add Enter key submit for search input
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                this.closest('form').submit();
            }
        });

        // Add search icon animation
        searchInput.addEventListener('focus', function() {
            const searchBtn = this.parentElement.querySelector('button');
            if (searchBtn) {
                searchBtn.classList.add('btn-primary');
                searchBtn.classList.remove('btn-outline-primary');
            }
        });

        searchInput.addEventListener('blur', function() {
            const searchBtn = this.parentElement.querySelector('button');
            if (searchBtn && !this.value) {
                searchBtn.classList.remove('btn-primary');
                searchBtn.classList.add('btn-outline-primary');
            }
        });
    }

    // Highlight search terms in results
    const searchTerm = '<?php echo e(request("search")); ?>';
    if (searchTerm) {
        highlightSearchTerm(searchTerm);
    }
});

function highlightSearchTerm(term) {
    if (!term) return;
    
    const table = document.querySelector('table tbody');
    if (table) {
        const cells = table.querySelectorAll('td');
        cells.forEach(cell => {
            if (cell.innerHTML && typeof cell.innerHTML === 'string') {
                const regex = new RegExp(`(${term})`, 'gi');
                cell.innerHTML = cell.innerHTML.replace(regex, '<mark class="bg-warning">$1</mark>');
            }
        });
    }
}
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.billing', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/billing/index.blade.php ENDPATH**/ ?>