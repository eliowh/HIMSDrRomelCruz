<?php $__env->startSection('title','Test Doctor Page'); ?>

<?php $__env->startSection('content'); ?>

<div style="padding: 20px;">
    <h1>Doctor Test Page</h1>
    <p>This is a test page to verify doctor permissions work.</p>
    <p>Current user: <?php echo e(auth()->user()->name ?? 'Not logged in'); ?></p>
    <p>Current role: <?php echo e(auth()->user()->role ?? 'No role'); ?></p>
    <p>Time: <?php echo e(now()); ?></p>
</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.doctor', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\doctor\test.blade.php ENDPATH**/ ?>