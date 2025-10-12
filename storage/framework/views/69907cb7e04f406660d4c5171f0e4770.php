<div class="doctor-header">
    <div class="header-container">
        <div class="hospital-info">
            <img src="<?php echo e(asset('img/hospital_logo.png')); ?>" alt="Hospital Logo" class="hospital-logo">
            <h1 class="hospital-name">Romel Cruz Hospital</h1>
        </div>
        <div class="doctor-name"><?php echo e(auth()->user()->name ?? 'Doctor'); ?></div>
    </div>
</div>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\doctor\doctor_header.blade.php ENDPATH**/ ?>