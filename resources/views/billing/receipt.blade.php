<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Receipt - {{ $billing->billing_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }
        
        /* Use table layout for header — this is more compatible with PDF renderers
           (DomPDF has limited flexbox support). We keep markup the same but prefer
           table/table-cell rendering so header stays consistent between HTML and PDF. */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .logo-section {
            display: table-cell;
            width: 80px;
            vertical-align: middle;
            padding-right: 20px;
        }

        .logo-section img {
            width: 80px;
            height: 80px;
            /* object-fit isn't always supported by PDF generators; ensure the image
               fits by using explicit width/height and letting browsers scale it. */
        }

        .header-content {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        
        .hospital-name {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        
        .hospital-address {
            font-size: 12px;
            color: #000;
            margin-bottom: 10px;
        }
        
        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
            color: #000;
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
            color: #000;
            margin-bottom: 3px;
        }
        
        .info-value {
            color: #000;
            margin-bottom: 8px;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .items-table th {
            background-color: #fff;
            font-weight: bold;
            color: #000;
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
            border: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
            color: #000;
            background-color: #fff;
            text-transform: uppercase;
        }
        
        .summary-section {
            margin-top: 20px;
            border: 2px solid #000;
            padding: 15px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td {
            padding: 5px 10px;
            border-bottom: 1px dotted #000;
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
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-size: 16px;
            font-weight: bold;
            padding: 10px;
        }
        
        .savings-section {
            background-color: #fff;
            border: 1px solid #000;
            padding: 10px;
            margin-top: 15px;
        }
        
        .savings-title {
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #000;
            border-top: 1px solid #000;
            padding-top: 15px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #fff;
            color: #000;
        }
        
        .philhealth-badge {
            background-color: #fff;
            color: #000;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
        }
        
        .discount-badge {
            background-color: #fff;
            color: #000;
            padding: 2px 6px;
            border: 1px solid #000;
            font-size: 10px;
            font-weight: bold;
        }
        
        .breakdown-section {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #000;
        }
        
        .breakdown-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #000;
        }
        
        @media print {
            body { 
                margin: 0;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .no-print { display: none; }
            .header {
                page-break-inside: avoid;
            }
            .logo-section img {
                max-width: 80px;
                max-height: 80px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            @if(isset($logoData) && $logoData)
                <img src="{{ $logoData }}" alt="Hospital Logo">
            @else
                <div style="width: 80px; height: 80px; border: 2px solid #000; display: flex; align-items: center; justify-content: center; font-size: 10px; text-align: center;">
                    Hospital<br>Logo
                </div>
            @endif
        </div>
        <div class="header-content">
            <div class="hospital-name">ROMEL CRUZ HOSPITAL</div>
            <div class="hospital-address">
                702 Matimbo, City of Malolos, Bulacan<br>
                Tel/Fax No. (044) 791-3025<br>
                Complete Healthcare Solutions
            </div>
            <div class="receipt-title">BILLING RECEIPT</div>
        </div>
    </div>

    <!-- Billing Information -->
    <div class="billing-info">
        <div class="left">
            <div class="info-section">
                <div class="info-label">Patient Information:</div>
                <div class="info-value">
                    <strong>{{ $billing->patient->display_name ?? ($billing->patient->first_name . ' ' . $billing->patient->last_name) }}</strong><br>
                    Date of Birth: {{ $billing->patient->date_of_birth ? \Carbon\Carbon::parse($billing->patient->date_of_birth)->format('M d, Y') : 'N/A' }}<br>
                    @if(isset($billing->admission) && $billing->admission->admission_date)
                        Admission Date: {{ \Carbon\Carbon::parse($billing->admission->admission_date)->format('M d, Y g:i A') }}<br>
                    @endif
                    @if(!empty($billing->patient->address))
                        Address: {{ $billing->patient->address }}<br>
                    @endif
                    @if(!empty($billing->patient->contact_number))
                        Contact: {{ $billing->patient->contact_number }}
                    @endif
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-label">Special Status:</div>
                <div class="info-value">
                    @if($billing->is_philhealth_member)
                        <span class="philhealth-badge">PhilHealth Member</span><br>
                    @endif
                    @if($billing->is_senior_citizen)
                        <span class="discount-badge">Senior Citizen</span><br>
                    @endif
                    @if($billing->is_pwd)
                        <span class="discount-badge">Person with Disability</span>
                    @endif
                    @if(!$billing->is_philhealth_member && !$billing->is_senior_citizen && !$billing->is_pwd)
                        <span>Regular Patient</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="right">
            <div class="info-section">
                <div class="info-label">Billing Details:</div>
                <div class="info-value">
                    <strong>Receipt #: {{ $billing->billing_number }}</strong><br>
                    Date: {{ $billing->billing_date->format('M d, Y g:i A') }}<br>
                    Status: <span class="status-badge status-{{ $billing->status }}">{{ ucfirst($billing->status) }}</span><br>
                    Prepared by: {{ $billing->createdBy->name }}
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-label">Print Information:</div>
                <div class="info-value">
                    Printed on: {{ now()->format('M d, Y g:i A') }}<br>
                    Print ID: {{ strtoupper(Str::random(8)) }}
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
                <td class="amount">PHP {{ number_format($billing->room_charges, 2) }}</td>
            </tr>
            <tr>
                <td class="label">ICD Fees:</td>
                <td class="amount">PHP {{ number_format($billing->professional_fees, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Medicine Charges:</td>
                <td class="amount">PHP {{ number_format($billing->medicine_charges, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Laboratory Charges:</td>
                <td class="amount">PHP {{ number_format($billing->lab_charges, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Other Charges:</td>
                <td class="amount">PHP {{ number_format($billing->other_charges, 2) }}</td>
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
                @foreach($billing->billingItems as $item)
                <tr>
                    <td>
                        <span class="item-type {{ $item->item_type }}">
                            {{ $item->getFormattedItemType() }}
                        </span>
                    </td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">
                        @if($item->icd_code)
                            <code style="font-size: 10px;">{{ $item->icd_code }}</code>
                        @else
                            <span>N/A</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">PHP {{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right"><strong>PHP {{ number_format($item->total_amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Section -->
    <div class="summary-section">
        <h3 style="margin-top: 0; margin-bottom: 15px; color: #333;">Payment Summary</h3>
        
        <table class="summary-table">
            <tr>
                <td class="label">Subtotal (Gross Amount):</td>
                <td class="amount">PHP {{ number_format($billing->total_amount, 2) }}</td>
            </tr>
            
            @if($billing->is_philhealth_member && $billing->philhealth_deduction > 0)
            <tr>
                <td class="label">Less: PhilHealth Coverage:</td>
                <td class="amount">-PHP {{ number_format($billing->philhealth_deduction, 2) }}</td>
            </tr>
            @endif
            
            @if($billing->senior_pwd_discount > 0)
            <tr>
                <td class="label">
                    Less: 
                    @if($billing->is_senior_citizen && $billing->is_pwd)
                        Senior Citizen & PWD Discount (20%):
                    @elseif($billing->is_senior_citizen)
                        Senior Citizen Discount (20%):
                    @else
                        PWD Discount (20%):
                    @endif
                </td>
                <td class="amount">-PHP {{ number_format($billing->senior_pwd_discount, 2) }}</td>
            </tr>
            @endif
            
            <tr class="total-row" style="background-color: #fff;">
                <td class="label">NET AMOUNT DUE:</td>
                <td class="amount" style="font-weight: bold;">PHP {{ number_format($billing->net_amount, 2) }}</td>
            </tr>
            
            @if($billing->status === 'paid' && $billing->payment_amount)
            <tr style="border-top: 2px solid #000;">
                <td class="label" style="font-weight: bold;">AMOUNT PAID:</td>
                <td class="amount" style="font-weight: bold;">PHP {{ number_format($billing->payment_amount, 2) }}</td>
            </tr>
            
            @if($billing->change_amount > 0)
            <tr>
                <td class="label" style="font-weight: bold;">CHANGE:</td>
                <td class="amount" style="font-weight: bold;">PHP {{ number_format($billing->change_amount, 2) }}</td>
            </tr>
            @endif
            @endif
        </table>
        
        @if(($billing->philhealth_deduction + $billing->senior_pwd_discount) > 0)
        <div class="savings-section">
            <div class="savings-title">💰 Patient Savings Summary</div>
            <div style="font-size: 11px;">
                Total Savings: <strong>PHP {{ number_format($billing->philhealth_deduction + $billing->senior_pwd_discount, 2) }}</strong><br>
                Percentage Saved: <strong>{{ number_format((($billing->philhealth_deduction + $billing->senior_pwd_discount) / $billing->total_amount) * 100, 1) }}%</strong> of total charges
            </div>
        </div>
        @endif
        
                @if($billing->status === 'paid' && $billing->payment_date)
        <div style="margin-top: 15px; padding: 10px; background-color: #fff; border: 2px solid #000;">
            <div style="font-weight: bold; color: #000; margin-bottom: 5px;">✓ PAYMENT CONFIRMED</div>
            <div style="font-size: 11px; color: #000;">
                <strong>Payment Date:</strong> {{ $billing->payment_date->format('F d, Y \a\t g:i A') }}<br>
                <strong>Clearance Date:</strong> {{ $billing->payment_date ? \Carbon\Carbon::parse($billing->payment_date)->format('F d, Y \a\t g:i A') : 'N/A' }}<br>
                @if($billing->processed_by)
                <strong>Processed By:</strong> {{ $billing->processedBy?->name ?? 'System' }}<br>
                @endif
                @if($billing->payment_amount && $billing->change_amount)
                <strong>Transaction Details:</strong> Received PHP {{ number_format($billing->payment_amount, 2) }} | Change PHP {{ number_format($billing->change_amount, 2) }}
                @endif
            </div>
        </div>
        @endif
    </div>

    @if($billing->notes)
    <div style="margin-top: 20px; padding: 10px; background-color: #fff; border: 1px solid #000;">
        <strong>Notes:</strong><br>
        <span style="font-style: italic;">{{ $billing->notes }}</span>
    </div>
    @endif

    <!-- Print Button (only show on screen, not in print) -->
    @if(isset($autoPrint) && $autoPrint)
    <div class="no-print" style="text-align: center; margin: 20px 0; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
        <button onclick="window.print()" class="btn" style="background-color: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 16px; cursor: pointer;">
            <i class="fas fa-print"></i> Print Receipt
        </button>
        <button onclick="window.close()" class="btn" style="background-color: #6c757d; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-size: 16px; cursor: pointer; margin-left: 10px;">
            <i class="fas fa-times"></i> Close
        </button>
    </div>
    @endif

    <!-- Signature and footer (centered block so PDF/HTML match) -->
    <div style="margin-top: 30px; text-align: center;">
        <div style="display:inline-block; width:240px; text-align:center;">
            <div style="height:60px; border-bottom:1px solid #000; margin-bottom:6px;"></div>
            @php
                if (isset($isCashier) && $isCashier) {
                    $signatureName = $billing->processedBy?->name ?? '____________________';
                    $signatureLabel = 'Cashier';
                } elseif (isset($isBilling) && $isBilling) {
                    $signatureName = $billing->createdBy?->name ?? '____________________';
                    $signatureLabel = 'Billing Staff';
                } else {
                    $signatureName = $billing->processedBy?->name ?? $billing->createdBy?->name ?? '____________________';
                    $signatureLabel = $billing->processedBy ? 'Cashier' : 'Billing Staff';
                }
            @endphp
            <div style="font-weight:700;">{{ $signatureName }}</div>
            <div style="font-size:12px; color:#000;">{{ $signatureLabel }}</div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Romel Cruz Hospital Information Management System</strong></p>
        <p>This is a computer-generated receipt. For questions or concerns, please contact our billing department.</p>
        <p style="margin-top: 10px;">
            © {{ date('Y') }} Dr. Romel Cruz Hospital. All rights reserved.<br>
            Receipt generated on {{ now()->format('F d, Y \\a\\t g:i A') }}
        </p>
    </div>

    @if(isset($autoPrint) && $autoPrint)
    <script>
        // Auto-print when page loads (for print receipt functionality)
        window.addEventListener('load', function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
    @endif
</body>
</html>