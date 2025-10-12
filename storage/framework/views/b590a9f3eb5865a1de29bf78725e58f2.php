<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Account</title>
    <link rel="stylesheet" href="<?php echo e(asset('css/inventorycss/inventory.css')); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php echo $__env->make('Inventory.inventory_header', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="inventory-layout">
        <?php echo $__env->make('Inventory.inventory_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <main class="main-content">
            <div class="inventory-card">
                <h2>Account</h2>
                <p>Inventory user account settings will appear here.</p>
            </div>
        </main>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\Inventory\inventory_account.blade.php ENDPATH**/ ?>