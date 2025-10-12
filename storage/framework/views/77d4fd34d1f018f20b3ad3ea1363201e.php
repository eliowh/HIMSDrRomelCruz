<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title>Pharmacy Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/pharmacycss/pharmacy.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
        $pharmacyName = auth()->check() ? auth()->user()->name : 'Pharmacy Staff';
    ?>
    <?php echo $__env->make('pharmacy.pharmacy_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="pharmacy-layout">
        <?php echo $__env->make('pharmacy.pharmacy_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <div class="dashboard-header">
                <div>
                    <h1>Pharmacy Dashboard</h1>
                    <p>Welcome back, <?php echo e(Auth::user()->name); ?>! Here's your pharmacy overview.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-outline" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <a href="<?php echo e(route('pharmacy.orders')); ?>" class="action-btn primary">
                        <i class="fas fa-plus"></i> New Order
                    </a>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="dashboard-grid">
                <div class="stat-card <?php echo e($pendingOrders > 10 ? 'alert-warning' : ($pendingOrders > 20 ? 'alert-danger' : '')); ?>">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($pendingOrders); ?></span>
                        <span class="stat-label">Pending Orders</span>
                        <small class="stat-sublabel">Awaiting approval</small>
                    </div>
                    <div class="stat-trend">
                        <?php if($pendingOrders > 10): ?>
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon approved">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($approvedOrders); ?></span>
                        <span class="stat-label">Approved Orders</span>
                        <small class="stat-sublabel">Ready for processing</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($completedOrders); ?></span>
                        <span class="stat-label">Completed Orders</span>
                        <small class="stat-sublabel">Successfully dispensed</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($totalOrders); ?></span>
                        <span class="stat-label">Total Orders</span>
                        <small class="stat-sublabel">All time</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon value">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱<?php echo e(number_format($pendingOrdersValue, 2)); ?></span>
                        <span class="stat-label">Pending Value</span>
                        <small class="stat-sublabel">Total pending worth</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon monthly">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱<?php echo e(number_format($completedOrdersValue, 2)); ?></span>
                        <span class="stat-label">Monthly Sales</span>
                        <small class="stat-sublabel"><?php echo e(now()->format('F Y')); ?></small>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-history"></i> Recent Orders</h3>
                    <a href="<?php echo e(route('pharmacy.orders')); ?>" class="view-all-link">
                        View All Orders <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if($recentOrders->count() > 0): ?>
                    <div class="orders-grid">
                        <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="order-card">
                            <div class="order-header">
                                <span class="order-id">#<?php echo e(str_pad($order->id, 4, '0', STR_PAD_LEFT)); ?></span>
                                <span class="status-badge status-<?php echo e($order->status); ?>">
                                    <?php echo e(ucfirst($order->status)); ?>

                                </span>
                            </div>
                            <div class="order-content">
                                <div class="medicine-info">
                                    <strong><?php echo e($order->generic_name ?: $order->brand_name); ?></strong>
                                    <?php if($order->generic_name && $order->brand_name): ?>
                                        <br><small>Brand: <?php echo e($order->brand_name); ?></small>
                                    <?php endif; ?>
                                    <br><small>Code: <?php echo e($order->item_code); ?></small>
                                </div>
                                <div class="order-details">
                                    <div class="quantity">
                                        <i class="fas fa-pills"></i>
                                        <?php echo e($order->quantity); ?> units
                                    </div>
                                    <div class="total-price">
                                        <i class="fas fa-peso-sign"></i>
                                        ₱<?php echo e(number_format($order->total_price, 2)); ?>

                                    </div>
                                </div>
                            </div>
                            <div class="order-footer">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    <?php echo e($order->requested_at->diffForHumans()); ?>

                                </small>
                                <?php if($order->notes): ?>
                                <small class="order-notes">
                                    <i class="fas fa-sticky-note"></i>
                                    <?php echo e(\Illuminate\Support\Str::limit($order->notes, 50)); ?>

                                </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-pills"></i>
                        </div>
                        <h4>No Recent Orders</h4>
                        <p>You haven't placed any pharmacy orders yet.</p>
                        <a href="<?php echo e(route('pharmacy.orders')); ?>" class="btn pharmacy-btn-primary">
                            <i class="fas fa-plus"></i> Place Your First Order
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Status Summary and Quick Actions -->
            <div class="dashboard-summary">
                <div class="summary-card">
                    <h3><i class="fas fa-tachometer-alt"></i> Order Status Overview</h3>
                    <div class="status-overview">
                        <div class="status-item">
                            <span class="status-dot pending"></span>
                            <span class="status-text"><?php echo e($pendingOrders); ?> Pending Orders</span>
                            <?php if($pendingOrders > 0): ?>
                                <a href="<?php echo e(route('pharmacy.orders', ['status' => 'pending'])); ?>" class="status-link">View</a>
                            <?php endif; ?>
                        </div>
                        <div class="status-item">
                            <span class="status-dot approved"></span>
                            <span class="status-text"><?php echo e($approvedOrders); ?> Approved Orders</span>
                            <?php if($approvedOrders > 0): ?>
                                <a href="<?php echo e(route('pharmacy.orders', ['status' => 'approved'])); ?>" class="status-link">View</a>
                            <?php endif; ?>
                        </div>
                        <div class="status-item">
                            <span class="status-dot completed"></span>
                            <span class="status-text"><?php echo e($completedOrders); ?> Completed Today</span>
                        </div>
                        <?php if($cancelledOrders > 0): ?>
                        <div class="status-item">
                            <span class="status-dot cancelled"></span>
                            <span class="status-text"><?php echo e($cancelledOrders); ?> Cancelled Orders</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="quick-actions-card">
                    <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="<?php echo e(route('pharmacy.orders')); ?>" class="action-btn primary">
                            <i class="fas fa-plus"></i>
                            <div>
                                <strong>New Order</strong>
                                <small>Request medications</small>
                            </div>
                        </a>
                        <a href="<?php echo e(route('pharmacy.orders', ['status' => 'pending'])); ?>" class="action-btn secondary">
                            <i class="fas fa-clock"></i>
                            <div>
                                <strong>Pending Orders</strong>
                                <small><?php echo e($pendingOrders); ?> awaiting approval</small>
                            </div>
                        </a>
                        <a href="<?php echo e(route('pharmacy.orders', ['status' => 'approved'])); ?>" class="action-btn success">
                            <i class="fas fa-check"></i>
                            <div>
                                <strong>Ready Orders</strong>
                                <small><?php echo e($approvedOrders); ?> ready to process</small>
                            </div>
                        </a>
                        <a href="/pharmacy/account" class="action-btn info">
                            <i class="fas fa-user-cog"></i>
                            <div>
                                <strong>My Account</strong>
                                <small>Profile & settings</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function refreshDashboard() {
            // Show loading state
            const refreshBtn = document.querySelector('[onclick="refreshDashboard()"]');
            const originalContent = refreshBtn.innerHTML;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
            refreshBtn.disabled = true;
            
            // Reload the page after a short delay to show the loading state
            setTimeout(() => {
                window.location.reload();
            }, 500);
        }

        // Auto-refresh every 5 minutes
        setInterval(() => {
            // Update timestamp only, not full refresh
            const now = new Date();
            const timeElements = document.querySelectorAll('.stat-sublabel');
            // You can add more sophisticated auto-refresh logic here
        }, 300000); // 5 minutes

        // Add hover effects and animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add pulse animation to high priority items
            if (<?php echo e($pendingOrders); ?> > 10) {
                document.querySelector('.stat-card.alert-warning').classList.add('pulse');
            }
            
            // Initialize tooltips or other interactive elements here
        });
    </script>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\pharmacy\pharmacy_home.blade.php ENDPATH**/ ?>