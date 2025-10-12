<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Account Settings</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/doctorcss/doctor.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
        $doctorName = auth()->user()->name ?? 'Doctor';
    ?>
    <?php echo $__env->make('doctor.doctor_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="doctor-layout">
        <?php echo $__env->make('doctor.doctor_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main-content">
            <div class="doctor-card">
                <h2>Account Settings</h2>
                <p>Manage your account information and preferences.</p>
            </div>
            
            <div class="doctor-card">
                <h3>Profile Information</h3>
                <div class="profile-info">
                    <p><strong>Name:</strong> <?php echo e(Auth::user()->name); ?></p>
                    <p><strong>Email:</strong> <?php echo e(Auth::user()->email); ?></p>
                    <p><strong>Role:</strong> Doctor</p>
                </div>
            </div>

            <div class="doctor-card">
                <h3>Change Password</h3>
                <!-- Placeholder for password change form -->
                <div class="placeholder-content">
                    <p>Password change functionality will be implemented soon.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\doctor\doctor_account.blade.php ENDPATH**/ ?>