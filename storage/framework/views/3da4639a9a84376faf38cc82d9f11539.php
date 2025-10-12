<header class="labtech-header">
    <div class="header-container">
        <div class="hospital-info">
            <img src="<?php echo e(asset('img/hospital_logo.png')); ?>" alt="Hospital Logo" class="hospital-logo">
            <h1 class="hospital-name">Romel Cruz Hospital</h1>
        </div>
        <span class="labtech-name"><?php echo e(Auth::check() ? Auth::user()->name : 'Lab Technician'); ?></span>
    </div>
</header>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\labtech\labtech_header.blade.php ENDPATH**/ ?>