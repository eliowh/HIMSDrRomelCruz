<!-- Expired Items Report -->
<div class="report-content">
    <?php if(isset($data) && $data->count() > 0): ?>
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="fas fa-ban"></i> Expired Items</h3>
                <p>Items that have already expired and should be removed from inventory.</p>
            </div>
            <div class="report-card-body">
                <div class="table-responsive">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Item Code</th>
                        <th>Generic Name</th>
                        <th>Brand Name</th>
                        <th>Current Stock</th>
                        <th>Expired Date</th>
                        <th>Days Expired</th>
                        <th>Value Lost</th>
                        <th>Action Required</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="expired-row">
                        <td><strong><?php echo e($item->item_code); ?></strong></td>
                        <td><?php echo e($item->generic_name ?: '-'); ?></td>
                        <td><?php echo e($item->brand_name ?: '-'); ?></td>
                        <td><?php echo e(number_format($item->quantity)); ?></td>
                        <td><?php echo e($item->expiry_date ? $item->expiry_date->format('M d, Y') : '-'); ?></td>
                        <td>
                            <?php if($item->expiry_date): ?>
                                <?php
                                    $daysExpired = $item->expiry_date->diffInDays(now());
                                ?>
                                <span class="expired-badge">
                                    <?php echo e($daysExpired); ?> days ago
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>₱<?php echo e(number_format($item->quantity * $item->price, 2)); ?></td>
                        <td>
                            <span class="action-badge remove">Remove from Stock</span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
                </table>
                </div>
            </div>
            
            <?php if($data->hasPages()): ?>
            <div class="report-card-footer">
                <?php echo e($data->appends(request()->query())->links('components.custom-pagination')); ?>

            </div>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div class="report-card">
            <div class="report-card-body">
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3>No Expired Items</h3>
                    <p>No items have expired. Great inventory management!</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.expired-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.8rem;
    background: #6c757d;
    color: white;
}
.action-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: 500;
    font-size: 0.8rem;
}
.action-badge.remove {
    background: #dc3545;
    color: white;
}
.expired-row {
    background: #fdf2f2;
}
.expired-row:hover {
    background: #fce8e8;
}
</style><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\reports\expired.blade.php ENDPATH**/ ?>