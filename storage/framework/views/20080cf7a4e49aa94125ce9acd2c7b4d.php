<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title><?php echo $__env->yieldContent('title','HIMS Nurse'); ?></title>

    
    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/nurse.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/nurse_addPatients.css')); ?>">
</head>
<body>
    
    <?php if ($__env->exists('nurse.nurse_header')) echo $__env->make('nurse.nurse_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="nurse-layout">
        
        <?php if ($__env->exists('nurse.nurse_sidebar')) echo $__env->make('nurse.nurse_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
    
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\layouts\app.blade.php ENDPATH**/ ?>