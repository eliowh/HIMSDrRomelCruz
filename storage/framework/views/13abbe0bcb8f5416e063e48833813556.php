<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Account Settings</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/nurse.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
        $nurseName = auth()->user()->name ?? 'Nurse';
    ?>
    <?php echo $__env->make('nurse.nurse_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="nurse-layout">
        <?php echo $__env->make('nurse.nurse_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main-content">
            <div class="nurse-card">
                <h2>Account Settings</h2>
                <p>Manage your account information and preferences.</p>
            </div>
            
            <div class="nurse-card">
                <h3>Profile Information</h3>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?php echo e(Auth::user()->name); ?></p>
                    <p><strong>Email:</strong> <?php echo e(Auth::user()->email); ?></p>
                    <p><strong>Role:</strong> Nurse</p>
                </div>
            </div>

            <div class="nurse-card">
                <h3>Change Password</h3>
                <!-- Trigger sending reset password email to the user's email -->
                <?php if(session('status_error')): ?>
                    <div class="alert alert-danger"><?php echo e(session('status_error')); ?></div>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('account.sendResetEmail')); ?>">
                    <?php echo csrf_field(); ?>
                    <p>Click the button below to send a password reset email to your account email address.</p>
                    <!-- Use existing site action button styles to match other buttons -->
                    <button type="submit" class="action-btn primary">Reset password</button>
                </form>
            </div>
        </main>
    </div>
    <?php echo $__env->make('nurse.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\nurse\nurse_account.blade.php ENDPATH**/ ?>