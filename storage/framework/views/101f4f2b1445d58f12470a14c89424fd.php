<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/forgotPass.css')); ?>">
</head>
<body>
<div class="container">
    <div class="left">
        <img src="<?php echo e(asset('img/hospital_logo.png')); ?>" alt="">
    </div>
    <div class="right">
        <div class="formbox">
            <form action="<?php echo e(secure_url('/reset-password/'.$token)); ?>" method="post">
                <?php echo csrf_field(); ?>
                <h3 class="header">Reset Password</h3>
                <p style="margin-bottom: 30px; margin-top: 5px; color: #666; text-align: center; font-size: 0.9em;">
                Enter your new password and confirm it.
                </p>
                <h2 class="pass">New Password</h2>
                <div class="mb-3">
                    <input type="password" placeholder="New password" name="password" class="eField">
                </div>
                <h2 class="pass">Confirm Password</h2>
                <div class="mb-3">
                    <input type="password" placeholder="Confirm new password" name="password_confirmation" class="eField">
                </div>
                <button type="submit" class="submitBtn">Reset Password</button>
            </form>
        </div>
    </div>  
</div>
<?php if($errors->any()): ?>
    <div class="alert alert-danger" style="position: fixed; bottom: 0; width: 100%; text-align: center;">
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>
</body><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\reset_password.blade.php ENDPATH**/ ?>