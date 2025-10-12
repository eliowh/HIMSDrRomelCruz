<!-- Overview Report -->
<div class="report-content">
    <!-- Quick Alerts Card -->
    <?php if((isset($lowStockAlerts) && $lowStockAlerts->count() > 0) || (isset($upcomingExpiries) && $upcomingExpiries->count() > 0)): ?>
    <div class="report-card">
        <div class="report-card-header">
            <h3><i class="fas fa-bell"></i> Urgent Alerts</h3>
            <p>Items requiring immediate attention</p>
        </div>
        <div class="report-card-body">
            <div class="alert-grid">
                <?php if(isset($lowStockAlerts) && $lowStockAlerts->count() > 0): ?>
                <div class="alert-box warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h4>
                    <div class="alert-items">
                        <?php $__currentLoopData = $lowStockAlerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="alert-item">
                            <span class="item-name"><?php echo e($item->generic_name ?: $item->item_code); ?></span>
                            <span class="quantity"><?php echo e($item->quantity); ?> left</span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'low-stock'])); ?>" class="view-all">View All Low Stock</a>
                </div>
                <?php endif; ?>

                <?php if(isset($upcomingExpiries) && $upcomingExpiries->count() > 0): ?>
                <div class="alert-box danger">
                    <h4><i class="fas fa-clock"></i> Upcoming Expiries</h4>
                    <div class="alert-items">
                        <?php $__currentLoopData = $upcomingExpiries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="alert-item">
                            <span class="item-name"><?php echo e($item->generic_name ?: $item->item_code); ?></span>
                            <span class="expiry-date">
                                <?php if($item->expiry_date): ?>
                                    <?php echo e($item->expiry_date->format('M d, Y')); ?>

                                    (<?php echo e($item->days_until_expiry); ?> days)
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'expiring'])); ?>" class="view-all">View All Expiring Items</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Charts Section -->
    <div class="report-card">
        <div class="report-card-header">
            <h3><i class="fas fa-chart-bar"></i> Visual Analytics</h3>
            <p>Graphical representation of your inventory distribution and trends</p>
        </div>
        <div class="report-card-body">
            <div class="chart-grid">
                <!-- Stock Status Chart -->
                <div class="chart-card">
                    <h4>Stock Status Distribution</h4>
                    <canvas id="stockStatusChart" width="400" height="200"></canvas>
                </div>

                <!-- Top Value Items Chart -->
                <div class="chart-card">
                    <h4>Top 5 Most Valuable Items</h4>
                    <canvas id="topValueChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Tables -->
    <div class="report-card">
        <div class="report-card-header">
            <h3><i class="fas fa-table"></i> Summary Statistics</h3>
            <p>Quick overview of recent activity and inventory breakdown</p>
        </div>
        <div class="report-card-body">
            <div class="table-grid">
                <!-- Recent Activity Summary -->
                <div class="table-card">
                    <h4><i class="fas fa-activity"></i> Recent Activity Summary</h4>
                    <div class="summary-stats">
                        <div class="summary-item">
                            <span class="label">Orders This Week:</span>
                            <span class="value"><?php echo e($stats['recent_orders'] ?? 0); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Items In Stock:</span>
                            <span class="value"><?php echo e($stats['in_stock'] ?? 0); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Out of Stock:</span>
                            <span class="value"><?php echo e($stats['out_of_stock'] ?? 0); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="label">Expired Items:</span>
                            <span class="value"><?php echo e($stats['expired'] ?? 0); ?></span>
                        </div>
                    </div>
                </div>

            <!-- Top Value Items Table -->
            <?php if(isset($topValueItems) && $topValueItems->count() > 0): ?>
            <div class="table-card">
                <h4><i class="fas fa-chart-line"></i> Most Valuable Stock Items</h4>
                <div class="table-responsive">
                    <table class="reports-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $topValueItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <div class="item-info">
                                        <strong><?php echo e($item->generic_name ?: $item->item_code); ?></strong>
                                        <?php if($item->brand_name): ?>
                                            <small><?php echo e($item->brand_name); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td><?php echo e(number_format($item->quantity)); ?></td>
                                <td>₱<?php echo e(number_format($item->price, 2)); ?></td>
                                <td><strong>₱<?php echo e(number_format($item->total_value, 2)); ?></strong></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Stock Status Chart
    const stockStatusCtx = document.getElementById('stockStatusChart').getContext('2d');
    new Chart(stockStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['In Stock', 'Low Stock', 'Out of Stock', 'Expired'],
            datasets: [{
                data: [
                    <?php echo e($stats['in_stock'] ?? 0); ?>,
                    <?php echo e($stats['low_stock'] ?? 0); ?>,
                    <?php echo e($stats['out_of_stock'] ?? 0); ?>,
                    <?php echo e($stats['expired'] ?? 0); ?>

                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Top Value Items Chart
    <?php if(isset($topValueItems) && $topValueItems->count() > 0): ?>
    const topValueCtx = document.getElementById('topValueChart').getContext('2d');
    new Chart(topValueCtx, {
        type: 'bar',
        data: {
            labels: [
                <?php $__currentLoopData = $topValueItems->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    '<?php echo e(Str::limit($item->generic_name ?: $item->item_code, 15)); ?>',
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            ],
            datasets: [{
                label: 'Total Value (₱)',
                data: [
                    <?php $__currentLoopData = $topValueItems->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php echo e($item->total_value); ?>,
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                ],
                backgroundColor: '#1a4931'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    <?php endif; ?>
});
</script>

<style>
.report-content {
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.alert-section {
    padding: 1.5rem;
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}

.alert-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.alert-box {
    background: white;
    border-radius: 6px;
    padding: 1rem;
    border-left: 4px solid;
}

.alert-box.warning {
    border-left-color: #ffc107;
}

.alert-box.danger {
    border-left-color: #dc3545;
}

.alert-box h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.alert-items {
    margin-bottom: 0.5rem;
}

.alert-item {
    display: flex;
    justify-content: space-between;
    padding: 0.25rem 0;
    font-size: 0.9rem;
}

.alert-item .item-name {
    font-weight: 500;
}

.alert-item .quantity,
.alert-item .expiry-date {
    color: #666;
    font-size: 0.8rem;
}

.view-all {
    color: #1a4931;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
}

.view-all:hover {
    text-decoration: underline;
}

.charts-section {
    padding: 1.5rem;
}

.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.chart-card {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 1rem;
    height: 300px;
}

.chart-card h4 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1rem;
}

.tables-section {
    padding: 1.5rem;
    background: #f8f9fa;
}

.table-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
}

.table-card {
    background: white;
    border-radius: 6px;
    padding: 1rem;
}

.table-card h4 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.summary-stats {
    display: grid;
    gap: 0.5rem;
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-item:last-child {
    border-bottom: none;
}

.summary-item .label {
    color: #666;
    font-size: 0.9rem;
}

.summary-item .value {
    font-weight: 600;
    color: #333;
}

.reports-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

.reports-table th,
.reports-table td {
    padding: 0.5rem;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

.reports-table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.item-info strong {
    display: block;
    color: #333;
}

.item-info small {
    display: block;
    color: #666;
    font-size: 0.8rem;
}

@media (max-width: 768px) {
    .alert-grid,
    .chart-grid,
    .table-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-card {
        min-width: auto;
    }
}
</style><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\reports\overview.blade.php ENDPATH**/ ?>