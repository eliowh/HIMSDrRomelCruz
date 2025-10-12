<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo e($template['title'] ?? 'Lab Result'); ?></title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
        .header { text-align: center; font-weight: bold; }
        .section { background: #eee; font-weight: bold; }
        .meta td { border: 1px solid #000; font-size: 11px; }
        .meta th { background: #f5f5f5; }
        .sig-block { margin-top: 40px; text-align: center; font-size: 11px; }
        .small { font-size: 10px; }
    </style>
</head>
<body>
    <div class="header" style="margin-bottom:10px;">
        <div style="font-size:16px;">ROMEL CRUZ HOSPITAL</div>
        <div style="font-size:11px;">702 Matimbo, Malolos, Bulacan<br/>DOH-BRL LICENSE NO. 2480</div>
        <div style="margin-top:6px;font-size:15px;"><?php echo e(strtoupper($template['title'] ?? 'LABORATORY RESULT FORM')); ?></div>
    </div>

    <table class="meta" style="margin-bottom:12px;">
        <tr>
            <th style="width:35%">NAME OF PATIENT</th>
            <th style="width:15%">AGE/SEX</th>
            <th style="width:15%">WARD</th>
            <th style="width:35%">DATE</th>
        </tr>
        <tr>
            <td><?php echo e($patient->display_name ?? $patient->first_name.' '.$patient->last_name); ?></td>
            <td><?php echo e($patient->age ?? 'N/A'); ?>/<?php echo e($patient->sex ?? 'N/A'); ?></td>
            <td><?php echo e($patient->ward ?? ''); ?></td>
            <td><?php echo e(now()->format('l, F d, Y')); ?></td>
        </tr>
    </table>

    <?php if(isset($template['sections'])): ?>
        <?php $__currentLoopData = $template['sections']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sectionTitle => $fields): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <table style="margin-bottom:10px;">
                <tr><th colspan="3" class="section"><?php echo e($sectionTitle); ?></th></tr>
                <?php $__currentLoopData = $fields; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td style="width:40%"><?php echo e($f['label']); ?></td>
                        <td style="width:30%"><?php echo e($values[$f['key']] ?? ''); ?></td>
                        <td style="width:30%"><?php echo e($f['ref'] ?? ''); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </table>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <table>
            <tr>
                <th style="width:40%"><?php echo e(isset($template['fields'][0]['ref']) ? 'EXAMINATION / TEST' : 'TEST'); ?></th>
                <?php if(isset($template['fields'][0]['ref'])): ?><th style="width:20%">VALUE / RESULT</th><th style="width:15%">UNIT</th><th style="width:25%">REFERENCE / NORMAL VALUE</th><?php else: ?> <th style="width:60%">RESULT</th> <?php endif; ?>
            </tr>
            <?php $__currentLoopData = $template['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($f['label']); ?></td>
                    <?php if(isset($f['ref'])): ?>
                        <td><?php echo e($values[$f['key']] ?? ''); ?></td>
                        <td><?php echo e($f['unit'] ?? ''); ?></td>
                        <td><?php echo e($f['ref'] ?? ''); ?></td>
                    <?php else: ?>
                        <td><?php echo e($values[$f['key']] ?? ''); ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </table>
    <?php endif; ?>

    <div class="sig-block" style="margin-top:60px;">
        <div>Jhen-Jhen DG. Guevarra, RMT</div>
        <div class="small">License No.: ____________</div>
        <div style="font-weight:bold; margin-top:4px;">MEDICAL TECHNOLOGIST</div>
    </div>
</body>
</html>
<?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\labtech\templates\lab_result_generic.blade.php ENDPATH**/ ?>