<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Reports</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/inventorycss/inventory.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/inventorycss/inventory_reports.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/pagination.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <?php echo $__env->make('Inventory.inventory_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="inventory-layout">
        <?php echo $__env->make('Inventory.inventory_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main-content">
            <?php if(isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo e($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo e(session('success')); ?></span>
                </div>
            <?php endif; ?>

            <?php if(session('warning')): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><?php echo e(session('warning')); ?></span>
                </div>
            <?php endif; ?>

            <!-- Report Navigation -->
            <div class="reports-nav">
                <h2><i class="fas fa-chart-bar"></i> Inventory Reports</h2>
                <div class="report-tabs">
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'overview'])); ?>" 
                       class="report-tab <?php echo e(($reportType ?? 'overview') === 'overview' ? 'active' : ''); ?>">
                        <i class="fas fa-dashboard"></i> Overview
                    </a>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'low-stock'])); ?>" 
                       class="report-tab <?php echo e(($reportType ?? '') === 'low-stock' ? 'active' : ''); ?>">
                        <i class="fas fa-exclamation-triangle"></i> Low Stock
                    </a>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'out-of-stock'])); ?>" 
                       class="report-tab <?php echo e(($reportType ?? '') === 'out-of-stock' ? 'active' : ''); ?>">
                        <i class="fas fa-times-circle"></i> Out of Stock
                    </a>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'expiring'])); ?>" 
                       class="report-tab <?php echo e(($reportType ?? '') === 'expiring' ? 'active' : ''); ?>">
                        <i class="fas fa-clock"></i> Expiring Soon
                    </a>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'expired'])); ?>" 
                       class="report-tab <?php echo e(($reportType ?? '') === 'expired' ? 'active' : ''); ?>">
                        <i class="fas fa-ban"></i> Expired
                    </a>
                    <a href="<?php echo e(route('inventory.reports', ['type' => 'stock-movement'])); ?>" 
                       class="report-tab <?php echo e(($reportType ?? '') === 'stock-movement' ? 'active' : ''); ?>">
                        <i class="fas fa-exchange-alt"></i> Stock Movement
                    </a>
                </div>
            </div>

            <!-- Summary Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo e(number_format($stats['total_items'] ?? 0)); ?></h3>
                        <p>Total Items</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-peso-sign"></i>
                    </div>
                    <div class="stat-content">
                        <h3>₱<?php echo e(number_format($stats['total_value'] ?? 0, 2)); ?></h3>
                        <p>Total Value</p>
                    </div>
                </div>
                <div class="stat-card low-stock">
                    <div class="stat-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo e($stats['low_stock'] ?? 0); ?></h3>
                        <p>Low Stock Items</p>
                    </div>
                </div>
                <div class="stat-card expiring">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo e($stats['expiring_soon'] ?? 0); ?></h3>
                        <p>Expiring Soon</p>
                    </div>
                </div>
            </div>

            <!-- Report Content -->
            <?php if(($reportType ?? 'overview') === 'overview'): ?>
                <?php echo $__env->make('Inventory.reports.overview', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($reportType === 'low-stock'): ?>
                <?php echo $__env->make('Inventory.reports.low_stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($reportType === 'out-of-stock'): ?>
                <?php echo $__env->make('Inventory.reports.out_of_stock', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($reportType === 'expiring'): ?>
                <?php echo $__env->make('Inventory.reports.expiring', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($reportType === 'expired'): ?>
                <?php echo $__env->make('Inventory.reports.expired', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php elseif($reportType === 'stock-movement'): ?>
                <?php echo $__env->make('Inventory.reports.stock_movement', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>

        </main>
    </div>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\inventory_reports.blade.php ENDPATH**/ ?>