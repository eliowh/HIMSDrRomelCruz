<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Receipt - <?php echo e($billing->billing_number); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c5aa0;
            margin-bottom: 5px;
        }
        
        .hospital-address {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: #333;
        }
        
        .billing-info {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .billing-info .left,
        .billing-info .right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding: 10px;
        }
        
        .info-section {
            margin-bottom: 15px;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        
        .info-value {
            color: #555;
            margin-bottom: 8px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .items-table .text-right {
            text-align: right;
        }
        
        .items-table .text-center {
            text-align: center;
        }
        
        .item-type {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            color: white;
            text-transform: uppercase;
        }
        
        .item-type.room { background-color: #007bff; }
        .item-type.medicine { background-color: #28a745; }
        .item-type.laboratory { background-color: #17a2b8; }
        .item-type.professional { background-color: #ffc107; color: #333; }
        .item-type.other { background-color: #6c757d; }
        
        .summary-section {
            margin-top: 20px;
            border: 2px solid #333;
            padding: 15px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 5px 10px;
            border-bottom: 1px dotted #ccc;
        }
        
        .summary-table .label {
            width: 70%;
            font-weight: bold;
        }
        
        .summary-table .amount {
            width: 30%;
            text-align: right;
            font-family: monospace;
        }
        
        .summary-table .total-row td {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-size: 16px;
            font-weight: bold;
            padding: 10px;
        }
        
        .savings-section {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 10px;
            margin-top: 15px;
        }
        
        .savings-title {
            font-weight: bold;
            color: #155724;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-paid { background-color: #d1ecf1; color: #0c5460; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .philhealth-badge {
            background-color: #cce7ff;
            color: #004085;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .discount-badge {
            background-color: #fff3cd;
            color: #856404;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .breakdown-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        
        .breakdown-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="hospital-name">ROMEL CRUZ HOSPITAL</div>
        <div class="hospital-address">
            Hospital Information Management System<br>
            Complete Healthcare Solutions<br>
            Contact: (Your Hospital Contact Information)
        </div>
        <div class="receipt-title">BILLING RECEIPT</div>
    </div>

    <!-- Billing Information -->
    <div class="billing-info">
        <div class="left">
            <div class="info-section">
                <div class="info-label">Patient Information:</div>
                <div class="info-value">
                    <strong><?php echo e($billing->patient->firstName); ?> <?php echo e($billing->patient->lastName); ?></strong><br>
                    Date of Birth: <?php echo e($billing->patient->dateOfBirth); ?><br>
                    <?php if($billing->patient->address): ?>
                        Address: <?php echo e($billing->patient->address); ?><br>
                    <?php endif; ?>
                    <?php if($billing->patient->contactNumber): ?>
                        Contact: <?php echo e($billing->patient->contactNumber); ?>

                    <?php endif; ?>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-label">Special Status:</div>
                <div class="info-value">
                    <?php if($billing->is_philhealth_member): ?>
                        <span class="philhealth-badge">PhilHealth Member</span><br>
                    <?php endif; ?>
                    <?php if($billing->is_senior_citizen): ?>
                        <span class="discount-badge">Senior Citizen</span><br>
                    <?php endif; ?>
                    <?php if($billing->is_pwd): ?>
                        <span class="discount-badge">Person with Disability</span>
                    <?php endif; ?>
                    <?php if(!$billing->is_philhealth_member && !$billing->is_senior_citizen && !$billing->is_pwd): ?>
                        <span style="color: #666;">Regular Patient</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="right">
            <div class="info-section">
                <div class="info-label">Billing Details:</div>
                <div class="info-value">
                    <strong>Receipt #: <?php echo e($billing->billing_number); ?></strong><br>
                    Date: <?php echo e($billing->billing_date->format('M d, Y g:i A')); ?><br>
                    Status: <span class="status-badge status-<?php echo e($billing->status); ?>"><?php echo e(ucfirst($billing->status)); ?></span><br>
                    Prepared by: <?php echo e($billing->createdBy->name); ?>

                </div>
            </div>
            
            <div class="info-section">
                <div class="info-label">Print Information:</div>
                <div class="info-value">
                    Printed on: <?php echo e(now()->format('M d, Y g:i A')); ?><br>
                    Print ID: <?php echo e(strtoupper(Str::random(8))); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Charges Breakdown -->
    <div class="breakdown-section">
        <div class="breakdown-title">Charges Breakdown by Category</div>
        <table class="summary-table" style="width: 100%; margin-bottom: 0;">
            <tr>
                <td class="label">Room Charges:</td>
                <td class="amount">PHP <?php echo e(number_format($billing->room_charges, 2)); ?></td>
            </tr>
            <tr>
                <td class="label">Professional Fees:</td>
                <td class="amount">PHP <?php echo e(number_format($billing->professional_fees, 2)); ?></td>
            </tr>
            <tr>
                <td class="label">Medicine Charges:</td>
                <td class="amount">PHP <?php echo e(number_format($billing->medicine_charges, 2)); ?></td>
            </tr>
            <tr>
                <td class="label">Laboratory Charges:</td>
                <td class="amount">PHP <?php echo e(number_format($billing->lab_charges, 2)); ?></td>
            </tr>
            <tr>
                <td class="label">Other Charges:</td>
                <td class="amount">PHP <?php echo e(number_format($billing->other_charges, 2)); ?></td>
            </tr>
        </table>
    </div>

    <!-- Detailed Items -->
    <div style="margin-bottom: 20px;">
        <h3 style="margin-bottom: 10px; color: #333;">Itemized Charges</h3>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Type</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 15%;">ICD-10</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 12%;" class="text-right">Unit Price</th>
                    <th style="width: 13%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $billing->billingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td>
                        <span class="item-type <?php echo e($item->item_type); ?>">
                            <?php echo e($item->getFormattedItemType()); ?>

                        </span>
                    </td>
                    <td><?php echo e($item->description); ?></td>
                    <td class="text-center">
                        <?php if($item->icd_code): ?>
                            <code style="font-size: 10px;"><?php echo e($item->icd_code); ?></code>
                        <?php else: ?>
                            <span style="color: #ccc;">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?php echo e($item->quantity); ?></td>
                    <td class="text-right">PHP <?php echo e(number_format($item->unit_price, 2)); ?></td>
                    <td class="text-right"><strong>PHP <?php echo e(number_format($item->total_amount, 2)); ?></strong></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; margin-bottom: 15px; color: #333;">Payment Summary</h3>
        
        <table class="summary-table">
            <tr>
                <td class="label">Subtotal (Gross Amount):</td>
                <td class="amount">PHP <?php echo e(number_format($billing->total_amount, 2)); ?></td>
            </tr>
            
            <?php if($billing->is_philhealth_member && $billing->philhealth_deduction > 0): ?>
            <tr style="color: #28a745;">
                <td class="label">Less: PhilHealth Coverage:</td>
                <td class="amount">-PHP <?php echo e(number_format($billing->philhealth_deduction, 2)); ?></td>
            </tr>
            <?php endif; ?>
            
            <?php if($billing->senior_pwd_discount > 0): ?>
            <tr style="color: #28a745;">
                <td class="label">
                    Less: 
                    <?php if($billing->is_senior_citizen && $billing->is_pwd): ?>
                        Senior Citizen & PWD Discount (20%):
                    <?php elseif($billing->is_senior_citizen): ?>
                        Senior Citizen Discount (20%):
                    <?php else: ?>
                        PWD Discount (20%):
                    <?php endif; ?>
                </td>
                <td class="amount">-PHP <?php echo e(number_format($billing->senior_pwd_discount, 2)); ?></td>
            </tr>
            <?php endif; ?>
            
            <tr class="total-row" style="background-color: #f8f9fa;">
                <td class="label">NET AMOUNT DUE:</td>
                <td class="amount" style="color: #2c5aa0;">PHP <?php echo e(number_format($billing->net_amount, 2)); ?></td>
            </tr>
            
            <?php if($billing->status === 'paid' && $billing->payment_amount): ?>
            <tr style="border-top: 2px solid #2c5aa0;">
                <td class="label" style="color: #28a745; font-weight: bold;">AMOUNT PAID:</td>
                <td class="amount" style="color: #28a745; font-weight: bold;">PHP <?php echo e(number_format($billing->payment_amount, 2)); ?></td>
            </tr>
            
            <?php if($billing->change_amount > 0): ?>
            <tr>
                <td class="label" style="color: #dc3545; font-weight: bold;">CHANGE:</td>
                <td class="amount" style="color: #dc3545; font-weight: bold;">PHP <?php echo e(number_format($billing->change_amount, 2)); ?></td>
            </tr>
            <?php endif; ?>
            <?php endif; ?>
        </table>
        
        <?php if(($billing->philhealth_deduction + $billing->senior_pwd_discount) > 0): ?>
        <div class="savings-section">
            <div class="savings-title">💰 Patient Savings Summary</div>
            <div style="font-size: 11px;">
                Total Savings: <strong>PHP <?php echo e(number_format($billing->philhealth_deduction + $billing->senior_pwd_discount, 2)); ?></strong><br>
                Percentage Saved: <strong><?php echo e(number_format((($billing->philhealth_deduction + $billing->senior_pwd_discount) / $billing->total_amount) * 100, 1)); ?>%</strong> of total charges
            </div>
        </div>
        <?php endif; ?>
        
        <?php if($billing->status === 'paid' && $billing->payment_date): ?>
        <div style="margin-top: 15px; padding: 10px; background-color: #e8f5e8; border-radius: 5px; border-left: 4px solid #28a745;">
            <div style="font-weight: bold; color: #155724; margin-bottom: 5px;">✓ PAYMENT CONFIRMED</div>
            <div style="font-size: 11px; color: #155724;">
                <strong>Payment Date:</strong> <?php echo e($billing->payment_date->format('F d, Y \a\t g:i A')); ?><br>
                <?php if($billing->processed_by): ?>
                <strong>Processed By:</strong> <?php echo e($billing->processedBy?->name ?? 'System'); ?><br>
                <?php endif; ?>
                <?php if($billing->payment_amount && $billing->change_amount): ?>
                <strong>Transaction Details:</strong> Received PHP <?php echo e(number_format($billing->payment_amount, 2)); ?> | Change PHP <?php echo e(number_format($billing->change_amount, 2)); ?>

                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if($billing->notes): ?>
    <div style="margin-top: 20px; padding: 10px; background-color: #fff3cd; border-radius: 5px;">
        <strong>Notes:</strong><br>
        <span style="font-style: italic;"><?php echo e($billing->notes); ?></span>
    </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p><strong>Romel Cruz Hospital Information Management System</strong></p>
        <p>This is a computer-generated receipt. For questions or concerns, please contact our billing department.</p>
        <p style="margin-top: 10px;">
            © <?php echo e(date('Y')); ?> Dr. Romel Cruz Hospital. All rights reserved.<br>
            Receipt generated on <?php echo e(now()->format('F d, Y \a\t g:i A')); ?>

        </p>
    </div>
</body>
</html><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views/billing/receipt.blade.php ENDPATH**/ ?>