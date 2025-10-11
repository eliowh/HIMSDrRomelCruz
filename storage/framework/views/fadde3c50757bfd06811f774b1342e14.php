<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Technician Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/labtechcss/labtech.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
        $labtechName = auth()->check() ? auth()->user()->name : 'Lab Technician';
        
        // Get lab order statistics
        $pendingOrders = App\Models\LabOrder::where('status', 'pending')->count();
        $inProgressOrders = App\Models\LabOrder::where('status', 'in_progress')->count();
        $completedOrders = App\Models\LabOrder::where('status', 'completed')->count();
        $cancelledOrders = App\Models\LabOrder::where('status', 'cancelled')->count();
        $totalOrders = $pendingOrders + $inProgressOrders + $completedOrders + $cancelledOrders;
        
        // Get recent lab orders
        $recentOrders = App\Models\LabOrder::with(['patient', 'requestedBy'])
            ->orderBy('requested_at', 'desc')
            ->take(5)
            ->get();
    ?>
    <?php echo $__env->make('labtech.labtech_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="labtech-layout">
        <?php echo $__env->make('labtech.labtech_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <div class="labtech-card">
                <h2>Welcome, <?php echo e(Auth::check() ? Auth::user()->name : 'Lab Technician'); ?></h2>
                <p>This is your dashboard where you can manage laboratory orders and patient records.</p>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($pendingOrders); ?></span>
                        <span class="stat-label">Pending Orders</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon in-progress">
                        <i class="fas fa-vial"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($inProgressOrders); ?></span>
                        <span class="stat-label">In Progress</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon completed">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($completedOrders); ?></span>
                        <span class="stat-label">Completed</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($totalOrders); ?></span>
                        <span class="stat-label">Total Orders</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="labtech-card">
                <div class="card-header">
                    <h3>Recent Orders</h3>
                    <a href="<?php echo e(route('labtech.orders')); ?>" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <?php if($recentOrders->count()): ?>
                    <div class="table-wrap">
                        <table class="orders-table">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Patient</th>
                                    <th>Test</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td>#<?php echo e(str_pad($order->id, 4, '0', STR_PAD_LEFT)); ?></td>
                                    <td><?php echo e($order->patient_name); ?></td>
                                    <td><?php echo e(\Illuminate\Support\Str::limit($order->test_requested, 30)); ?></td>
                                    <td>
                                        <span class="priority-badge priority-<?php echo e($order->priority); ?>">
                                            <?php echo e(ucfirst($order->priority)); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo e($order->status); ?>">
                                            <?php echo e(ucfirst(str_replace('_', ' ', $order->status))); ?>

                                        </span>
                                    </td>
                                    <td><?php echo e($order->requested_at->format('M d, Y H:i')); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-flask"></i>
                        <p>No lab orders found</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Quick Actions -->
            <div class="labtech-card">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="<?php echo e(route('labtech.orders')); ?>" class="quick-action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Manage Orders</span>
                    </a>
                    <a href="<?php echo e(route('labtech.patients')); ?>" class="quick-action-btn">
                        <i class="fas fa-users"></i>
                        <span>View Patients</span>
                    </a>
                    <a href="/labtech/account" class="quick-action-btn">
                        <i class="fas fa-user-cog"></i>
                        <span>My Account</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/labtech/labtech_home.blade.php ENDPATH**/ ?>