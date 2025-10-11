<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title><?php echo $__env->yieldContent('title','HIMS Doctor'); ?></title>

    
    <link rel="stylesheet" href="<?php echo e(asset('css/doctorcss/doctor.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/doctorcss/doctor_patients.css')); ?>">
</head>
<body>
    
    <?php if ($__env->exists('doctor.doctor_header')) echo $__env->make('doctor.doctor_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="doctor-layout">
        
        <?php if ($__env->exists('doctor.doctor_sidebar')) echo $__env->make('doctor.doctor_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-content">
            <?php echo $__env->yieldContent('content'); ?>
        </div>
    </div>
    
    
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/layouts/doctor.blade.php ENDPATH**/ ?>