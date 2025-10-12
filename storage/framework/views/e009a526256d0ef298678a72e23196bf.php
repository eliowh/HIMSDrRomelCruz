<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/inventorycss/inventory.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php echo $__env->make('Inventory.inventory_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="inventory-layout">
        <?php echo $__env->make('Inventory.inventory_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main-content">
            <!-- Dashboard Header -->
            <div class="dashboard-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1><i class="fas fa-tachometer-alt"></i> Inventory Dashboard</h1>
                        <p>Monitor your medicine stock levels, orders, and inventory operations</p>
                    </div>
                    <div>
                        <button onclick="refreshDashboard()" class="action-btn primary" style="margin: 0;">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <span id="lastUpdated" class="widget-subtitle" style="margin-left: 10px;">
                            Last updated: <?php echo e(now()->format('M d, Y H:i:s')); ?>

                        </span>
                    </div>
                </div>
            </div>

            <!-- Statistics Widgets -->
            <div class="widgets-grid">
                <!-- Total Stocks Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon primary">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Total Stock Items</h3>
                            <p class="widget-value"><?php echo e($totalStocks); ?></p>
                            <p class="widget-subtitle">Different medicines in inventory</p>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Widget -->
                <div class="widget <?php echo e($lowStockCount > 5 ? 'alert-warning' : ''); ?>">
                    <div class="widget-header">
                        <div class="widget-icon warning">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Low Stock Alert</h3>
                            <p class="widget-value"><?php echo e($lowStockCount); ?></p>
                            <p class="widget-subtitle">Items with ≤10 units remaining</p>
                            <?php if($lowStockCount > 10): ?>
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-down">
                                        <i class="fas fa-arrow-down"></i>
                                    </span>
                                    <span>Many items need restocking</span>
                                </div>
                            <?php elseif($lowStockCount > 0): ?>
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-neutral">
                                        <i class="fas fa-minus"></i>
                                    </span>
                                    <span>Monitor stock levels</span>
                                </div>
                            <?php else: ?>
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-up">
                                        <i class="fas fa-check"></i>
                                    </span>
                                    <span>Stock levels healthy</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Out of Stock Widget -->
                <div class="widget <?php echo e($outOfStockCount > 0 ? 'alert-critical' : ''); ?>">
                    <div class="widget-header">
                        <div class="widget-icon danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Out of Stock</h3>
                            <p class="widget-value"><?php echo e($outOfStockCount); ?></p>
                            <p class="widget-subtitle">Items requiring immediate restock</p>
                            <?php if($outOfStockCount > 0): ?>
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-down">
                                        <i class="fas fa-exclamation-triangle"></i>
                                    </span>
                                    <span>Immediate attention needed</span>
                                </div>
                            <?php else: ?>
                                <div class="widget-trend">
                                    <span class="trend-indicator trend-up">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <span>All items in stock</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Total Orders Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon info">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Total Orders</h3>
                            <p class="widget-value"><?php echo e($totalOrders); ?></p>
                            <p class="widget-subtitle">All time stock orders</p>
                        </div>
                    </div>
                </div>

                <!-- Pending Orders Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon warning">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Pending Orders</h3>
                            <p class="widget-value"><?php echo e($pendingOrders); ?></p>
                            <p class="widget-subtitle">Orders awaiting approval</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Value Widget -->
                <div class="widget">
                    <div class="widget-header">
                        <div class="widget-icon success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div>
                            <h3 class="widget-title">Inventory Value</h3>
                            <p class="widget-value">₱<?php echo e($totalStockValue); ?></p>
                            <p class="widget-subtitle">Total stock value</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="widget activity-widget">
                <div class="activity-grid">
                    <!-- Recent Stocks -->
                    <div class="activity-section">
                        <h4><i class="fas fa-plus-circle"></i> Recent Stock Items</h4>
                        <?php if($recentStocks->count() > 0): ?>
                            <ul class="activity-list">
                                <?php $__currentLoopData = $recentStocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="activity-item">
                                    <div class="activity-info">
                                        <p class="activity-name"><?php echo e($stock->generic_name ?: $stock->item_code); ?></p>
                                        <p class="activity-meta"><?php echo e($stock->brand_name ?: 'No brand'); ?> | Code: <?php echo e($stock->item_code); ?></p>
                                    </div>
                                    <div class="activity-value">
                                        <?php echo e($stock->quantity ?? 0); ?> units
                                    </div>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php else: ?>
                            <p class="activity-meta">No stock items found</p>
                        <?php endif; ?>
                    </div>

                    <!-- Recent Orders -->
                    <div class="activity-section">
                        <h4><i class="fas fa-list-alt"></i> Recent Orders</h4>
                        <?php if($recentOrders->count() > 0): ?>
                            <ul class="activity-list">
                                <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li class="activity-item">
                                    <div class="activity-info">
                                        <p class="activity-name"><?php echo e($order->generic_name ?: $order->item_code); ?></p>
                                        <p class="activity-meta">
                                            Requested by: <?php echo e($order->user->name ?? 'Unknown'); ?> |
                                            <?php echo e($order->requested_at ? $order->requested_at->format('M d, Y') : 'No date'); ?>

                                        </p>
                                    </div>
                                    <div>
                                        <span class="status-badge status-<?php echo e($order->status); ?>">
                                            <?php echo e(ucfirst($order->status)); ?>

                                        </span>
                                    </div>
                                </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                        <?php else: ?>
                            <p class="activity-meta">No recent orders found</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="inventory-card">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                <div class="quick-actions">
                    <a href="<?php echo e(asset('/inventory/stocks')); ?>" class="action-btn primary">
                        <i class="fas fa-boxes"></i> Manage Stocks
                    </a>
                    <a href="<?php echo e(asset('/inventory/orders')); ?>" class="action-btn secondary">
                        <i class="fas fa-shopping-cart"></i> View Orders
                    </a>
                    <a href="<?php echo e(asset('/inventory/reports')); ?>" class="action-btn secondary">
                        <i class="fas fa-chart-bar"></i> View Reports
                    </a>
                    <a href="<?php echo e(asset('/inventory/account')); ?>" class="action-btn secondary">
                        <i class="fas fa-user-cog"></i> Account Settings
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Dashboard functionality
        function refreshDashboard() {
            // Show loading state
            document.querySelectorAll('.widget').forEach(widget => {
                widget.classList.add('loading');
            });
            
            // Simulate refresh (in real app, you'd make an AJAX call)
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Add alert classes based on values
        document.addEventListener('DOMContentLoaded', function() {
            // Add alert class to out of stock widget if value > 0
            const outOfStockValue = parseInt(document.querySelector('.widget:nth-child(3) .widget-value').textContent);
            if (outOfStockValue > 0) {
                document.querySelector('.widget:nth-child(3)').classList.add('alert-critical');
            }
            
            // Add alert class to low stock widget if value > 5
            const lowStockValue = parseInt(document.querySelector('.widget:nth-child(2) .widget-value').textContent);
            if (lowStockValue > 5) {
                document.querySelector('.widget:nth-child(2)').classList.add('alert-warning');
            }
            
            // Add hover effects to activity items
            document.querySelectorAll('.activity-item').forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.backgroundColor = '#f8f9fa';
                    this.style.transform = 'translateX(4px)';
                    this.style.transition = 'all 0.2s ease';
                });
                
                item.addEventListener('mouseleave', function() {
                    this.style.backgroundColor = '';
                    this.style.transform = '';
                });
            });
        });

        // Auto-refresh every 5 minutes
        setInterval(function() {
            const lastUpdatedElement = document.getElementById('lastUpdated');
            if (lastUpdatedElement) {
                const now = new Date();
                const timeString = now.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                lastUpdatedElement.textContent = `Last updated: ${timeString}`;
            }
        }, 300000); // 5 minutes
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\inventory_home.blade.php ENDPATH**/ ?>