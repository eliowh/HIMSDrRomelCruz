<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Appointments</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/nursecss/nurse.css')); ?>">
</head>
<body>
    <?php
        $nurseName = auth()->user()->name ?? 'Nurse';
    ?>
    <?php echo $__env->make('nurse.nurse_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="nurse-layout">
        <?php echo $__env->make('nurse.nurse_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <div class="main-content">
            <div class="nurse-card">
                <h2>Your Appointments</h2>
                <p>View and manage your scheduled appointments and patient care tasks.</p>
                <!-- Add appointments content here -->
            </div>
        </div>
    </div>
    <?php echo $__env->make('nurse.modals.notification_system', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\nurse\nurse_appointments.blade.php ENDPATH**/ ?>