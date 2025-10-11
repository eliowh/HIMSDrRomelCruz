<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - HIMS</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/error_403.css')); ?>">
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔒</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            <?php echo e($exception->getMessage() ?: 'You do not have permission to access this resource.'); ?>

        </p>
        
        <?php if(auth()->check()): ?>
            <div class="error-details">
                <strong>Current Role:</strong> <?php echo e(ucfirst(auth()->user()->role)); ?><br>
                <strong>User:</strong> <?php echo e(auth()->user()->name); ?><br>
                <strong>Time:</strong> <?php echo e(now()->format('M d, Y h:i A')); ?>

            </div>
            
            <a href="javascript:history.back()" class="back-button">Go Back</a>
            
            <?php
                $userRole = auth()->user()->role;
                $dashboardRoutes = [
                    'admin' => '/admin/home',
                    'inventory' => '/inventory/home', 
                    'pharmacy' => '/pharmacy/home',
                    'doctor' => '/doctor/home',
                    'nurse' => '/nurse/home',
                    'lab_technician' => '/labtech/home',
                    'cashier' => '/cashier/home',
                ];
                $dashboardUrl = $dashboardRoutes[$userRole] ?? '/login';
            ?>
            
            <a href="<?php echo e($dashboardUrl); ?>" class="login-button">Go to My Dashboard</a>
        <?php else: ?>
            <a href="<?php echo e(route('login')); ?>" class="login-button">Login</a>
        <?php endif; ?>
    </div>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/errors/403.blade.php ENDPATH**/ ?>