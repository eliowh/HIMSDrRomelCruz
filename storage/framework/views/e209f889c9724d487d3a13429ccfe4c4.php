<!-- Out of Stock Report -->
<div class="report-content">
    <?php if(isset($data) && $data->count() > 0): ?>
        <div class="report-card">
            <div class="report-card-header">
                <h3><i class="fas fa-times-circle"></i> Out of Stock Items</h3>
                <p>Items that are completely out of stock and need immediate restocking.</p>
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
                        <th>Reorder Level</th>
                        <th>Unit Price</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="out-of-stock-row">
                        <td><strong><?php echo e($item->item_code); ?></strong></td>
                        <td><?php echo e($item->generic_name ?: '-'); ?></td>
                        <td><?php echo e($item->brand_name ?: '-'); ?></td>
                        <td>
                            <span class="quantity-badge danger">
                                <?php echo e(number_format($item->quantity)); ?>

                            </span>
                        </td>
                        <td><?php echo e(number_format($item->reorder_level)); ?></td>
                        <td>₱<?php echo e(number_format($item->price, 2)); ?></td>
                        <td><?php echo e($item->updated_at->format('M d, Y')); ?></td>
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
                    <h3>No Out of Stock Items</h3>
                    <p>All items are currently in stock!</p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.report-header.danger {
    background: #f8d7da;
    border-bottom: 1px solid #f1aeb5;
}
.report-header.danger h3,
.report-header.danger p {
    color: #721c24;
}
.quantity-badge.danger {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f1aeb5;
}
.out-of-stock-row:hover {
    background: #f8f9fa;
}
</style><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\reports\out_of_stock.blade.php ENDPATH**/ ?>