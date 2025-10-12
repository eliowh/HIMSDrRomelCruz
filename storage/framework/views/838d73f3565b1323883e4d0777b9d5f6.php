<div class="patient-details-simple">
    <h4>Patient Information</h4>
    <p><strong>Patient Number:</strong> <?php echo e($patient->patient_no ?? 'N/A'); ?></p>
    <p><strong>Name:</strong> <?php echo e(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '')); ?></p>
    <p><strong>Status:</strong> <?php echo e(ucfirst($patient->status ?? 'active')); ?></p>
    <p><strong>Room:</strong> <?php echo e($patient->room_no ?? 'Not assigned'); ?></p>
    <?php
        $ageYears = $patient->date_of_birth ? intval(\Carbon\Carbon::parse($patient->date_of_birth)->diffInYears(now())) : null;
    ?>
    <p><strong>Age:</strong> <?php echo e($ageYears !== null ? $ageYears.' years' : 'N/A'); ?></p>
    <p><strong>Created:</strong> <?php echo e($patient->created_at ?? 'N/A'); ?></p>
</div>

<style>
.patient-details-simple {
    padding: 20px;
    font-family: Arial, sans-serif;
}
.patient-details-simple h4 {
    color: #333;
    margin-bottom: 15px;
}
.patient-details-simple p {
    margin: 8px 0;
    line-height: 1.5;
}
</style><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\admin\partials\patient_details_simple.blade.php ENDPATH**/ ?>