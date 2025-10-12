<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title><?php echo $__env->yieldContent('title', 'HIMS Billing'); ?></title>
    
    
    <link rel="stylesheet" href="<?php echo e(asset('css/billingcss/billing.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    
    <style>
        .billing-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
        
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #367F2B, #2d6624);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #367F2B, #2d6624) !important;
            border: none !important;
            color: white !important;
            font-weight: 500 !important;
            padding: 8px 16px !important;
            border-radius: 6px !important;
            transition: all 0.3s ease !important;
            text-decoration: none !important;
            display: inline-block !important;
            box-shadow: 0 2px 4px rgba(54, 127, 43, 0.2) !important;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #2d6624, #367F2B) !important;
            color: white !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(54, 127, 43, 0.3) !important;
        }
        
        .btn-primary:focus, .btn-primary:active {
            background: linear-gradient(135deg, #2d6624, #367F2B) !important;
            color: white !important;
            box-shadow: 0 0 0 0.2rem rgba(54, 127, 43, 0.25) !important;
        }
        
        /* Additional button size variations */
        .btn-primary.btn-sm {
            padding: 6px 12px !important;
            font-size: 0.875rem !important;
        }
        
        .btn-primary.btn-lg {
            padding: 12px 24px !important;
            font-size: 1.125rem !important;
        }
        
        /* Ensure icons in buttons are properly spaced */
        .btn-primary i {
            margin-right: 6px;
        }
        
        .btn-primary i:last-child {
            margin-right: 0;
            margin-left: 6px;
        }
        
        .table thead th {
            background: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .form-control {
            border-radius: 6px;
        }
        
        .badge {
            border-radius: 4px;
        }
    </style>
    
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <?php echo $__env->make('billing.billing_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="billing-layout">
        <?php echo $__env->make('billing.billing_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        
        <main class="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </main>
    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\layouts\billing.blade.php ENDPATH**/ ?>