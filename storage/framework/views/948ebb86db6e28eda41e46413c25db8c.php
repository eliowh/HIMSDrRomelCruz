<header class="cashier-header">
    <div class="header-container">
        <div class="hospital-info">
            <img src="<?php echo e(asset('img/hospital_logo.png')); ?>" alt="Hospital Logo" class="hospital-logo">
            <h1 class="hospital-name">Romel Cruz Hospital</h1>
        </div>
        <span class="cashier-name"><?php echo e(auth()->user()->name ?? 'Cashier'); ?></span>
    </div>
</header>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/cashier/cashier_header.blade.php ENDPATH**/ ?>