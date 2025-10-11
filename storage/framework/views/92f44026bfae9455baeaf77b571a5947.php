<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/cashiercss/cashier.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php echo $__env->make('cashier.cashier_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="cashier-layout">
        <?php echo $__env->make('cashier.cashier_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main class="main-content">
            <?php
                // Use controller statistics if available, otherwise fallback
                if (!isset($stats)) {
                    $stats = [
                        'total_payments' => App\Models\Billing::where('status', 'paid')->count(),
                        'total_amount' => App\Models\Billing::where('status', 'paid')->sum('net_amount') ?? 0,
                        'pending_billings' => App\Models\Billing::where('status', 'pending')->count(),
                        'filter' => 'week',
                        'recent_payments' => App\Models\Billing::with('patient')->where('status', 'paid')->orderBy('payment_date', 'desc')->take(5)->get()
                    ];
                }
                $todayCollections = App\Models\Billing::where('status', 'paid')
                    ->whereDate('payment_date', today())
                    ->sum('net_amount') ?? 0;
            ?>

            <div class="cashier-card">
                <h2>Welcome, <?php echo e(Auth::user()->name); ?></h2>
                <p>This is your dashboard where you can manage billing and payment processing.</p>
                
                <!-- Filter Controls -->
                <div class="filter-controls">
                    <label>Payment Statistics Period:</label>
                    <select id="statisticsFilter" onchange="filterStatistics(this.value)">
                        <option value="week" <?php echo e(($stats['filter'] ?? 'week') === 'week' ? 'selected' : ''); ?>>Past Week</option>
                        <option value="month" <?php echo e(($stats['filter'] ?? 'week') === 'month' ? 'selected' : ''); ?>>Past Month</option>
                        <option value="year" <?php echo e(($stats['filter'] ?? 'week') === 'year' ? 'selected' : ''); ?>>Past Year</option>
                    </select>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($stats['pending_billings']); ?></span>
                        <span class="stat-label">Pending Bills</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon paid">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number"><?php echo e($stats['total_payments']); ?></span>
                        <span class="stat-label">Paid Bills (<?php echo e(ucfirst($stats['filter'] ?? 'week')); ?>)</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon collections">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱<?php echo e(number_format($todayCollections, 2)); ?></span>
                        <span class="stat-label">Today's Collections</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon total">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-number">₱<?php echo e(number_format($stats['total_amount'], 2)); ?></span>
                        <span class="stat-label">Total Revenue (<?php echo e(ucfirst($stats['filter'] ?? 'week')); ?>)</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Payments -->
            <div class="cashier-card">
                <div class="card-header">
                    <h3>Recent Payments (<?php echo e(ucfirst($stats['filter'] ?? 'week')); ?>)</h3>
                    <a href="/cashier/billing" class="view-all-link">View All <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <?php if($stats['recent_payments']->count()): ?>
                    <div class="table-wrap">
                        <table class="billings-table">
                            <thead>
                                <tr>
                                    <th>Billing #</th>
                                    <th>Patient</th>
                                    <th>Amount Paid</th>
                                    <th>Change Given</th>
                                    <th>Payment Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__currentLoopData = $stats['recent_payments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $billing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($billing->billing_number); ?></td>
                                    <td><?php echo e($billing->patient->display_name ?? 'Unknown Patient'); ?></td>
                                    <td>₱<?php echo e(number_format($billing->payment_amount ?? $billing->net_amount, 2)); ?></td>
                                    <td>
                                        <?php if($billing->change_amount): ?>
                                            ₱<?php echo e(number_format($billing->change_amount, 2)); ?>

                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($billing->payment_date ? $billing->payment_date->format('M d, Y H:i') : 'N/A'); ?></td>
                                    <td>
                                        <a href="/cashier/billing/<?php echo e($billing->id); ?>/view" class="action-link">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?php echo e(route('cashier.billing.receipt', $billing->id)); ?>" class="action-link" target="_blank">
                                            <i class="fas fa-receipt"></i> Receipt
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                                <?php else: ?>
                    <div class="no-data">
                        <i class="fas fa-receipt"></i>
                        <p>No recent payments found for the selected period</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Filter JavaScript -->
    <script>
        function filterStatistics(filter) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('filter', filter);
            window.location.href = currentUrl.toString();
        }
        
        // Add styling for filter controls
        const style = document.createElement('style');
        style.textContent = `
            .filter-controls {
                margin: 15px 0;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                border: 1px solid #e9ecef;
            }
            .filter-controls label {
                font-weight: bold;
                margin-right: 10px;
                color: #495057;
            }
            .filter-controls select {
                padding: 8px 12px;
                border: 1px solid #ced4da;
                border-radius: 4px;
                background: white;
                font-size: 14px;
                min-width: 150px;
            }
            .filter-controls select:focus {
                border-color: #80bdff;
                box-shadow: 0 0 0 2px rgba(0,123,255,.25);
                outline: none;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
            </div>
                        
        </main>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/cashier/cashier_home.blade.php ENDPATH**/ ?>