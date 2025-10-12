<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/forgotPass.css')); ?>">
    <title>Forgot Password | Romel Cruz Hospital</title>
</head>
<body>
    <div class="container">
        <div class="right">
            <img src="<?php echo e(asset('img/hospital_logo.png')); ?>" alt="Hospital Logo" class="hospital-logo">
        </div>
        <div class="left">
            <div class="formbox">
                <h3 class="forgot-title">Forgot Password</h3>
                <p class="forgot-desc">
                    Enter your email address to receive a password reset link.
                </p>
                <form action="/forgot-password" method="POST">
                    <?php echo csrf_field(); ?>
                    <h2 class="mail">Email</h2>
                    <div class="mb-3">
                        <input type="email" placeholder="Enter your email address" name="email" class="eField" required>
                    </div>
                    <button type="submit" class="submitBtn">Send Reset Link</button>
                    <a href="/login" class="back-link">
                        ← Back to Login
                    </a>
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
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\forgotPassword.blade.php ENDPATH**/ ?>