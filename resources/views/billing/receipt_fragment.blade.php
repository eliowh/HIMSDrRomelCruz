{{-- Receipt fragment (print-only) used by cashier JS to print in-place --}}
<div id="receipt-fragment-wrapper">
    <style>
        /* Minimal necessary receipt styles (copied from full receipt). Use table-based
           header for better PDF compatibility (DomPDF handles table/table-cell well).
        */
        #receipt-fragment-wrapper { font-family: Arial, sans-serif; color: #000; }
        #receipt-fragment-wrapper .header { display: table; width:100%; border-bottom:2px solid #000; padding-bottom:20px; margin-bottom:20px; }
        #receipt-fragment-wrapper .logo-section { display: table-cell; width:80px; vertical-align: middle; padding-right:20px; }
        #receipt-fragment-wrapper .logo-section img { width:80px; height:80px; }
        #receipt-fragment-wrapper .header-content { display: table-cell; vertical-align: middle; text-align:center; }
        #receipt-fragment-wrapper .hospital-name { font-size:24px; font-weight:bold; margin-bottom:5px; }
        #receipt-fragment-wrapper .receipt-title { font-size:18px; font-weight:bold; margin-top:10px; }
        @media print {
            #receipt-fragment-wrapper .header { display: table !important; width: 100% !important; }
            #receipt-fragment-wrapper .header-content { display: table-cell !important; vertical-align: middle !important; text-align: center !important; }
            #receipt-fragment-wrapper .hospital-name { 
                font-size:24px !important; 
                font-weight:bold !important; 
                display:block !important; 
                margin-bottom:5px !important;
                visibility: visible !important;
                color: #000 !important;
            }
            #receipt-fragment-wrapper .hospital-address { 
                display:block !important; 
                visibility: visible !important;
                color: #000 !important;
            }
            #receipt-fragment-wrapper .receipt-title { 
                font-size:18px !important; 
                font-weight:bold !important; 
                display:block !important;
                margin-top:10px !important;
                visibility: visible !important;
                color: #000 !important;
            }
        }
        #receipt-fragment-wrapper .billing-info { display:table; width:100%; margin-bottom:20px; }
        #receipt-fragment-wrapper .billing-info .left, #receipt-fragment-wrapper .billing-info .right { display:table-cell; width:50%; vertical-align:top; padding:10px; }
        #receipt-fragment-wrapper .items-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
        #receipt-fragment-wrapper .items-table th, #receipt-fragment-wrapper .items-table td { border:1px solid #000; padding:8px; text-align:left; }
        #receipt-fragment-wrapper .summary-section { margin-top:20px; border:2px solid #000; padding:15px; }
        @media print {
            /* ensure fragment prints correctly when injected */
            #receipt-fragment-wrapper { 
                -webkit-print-color-adjust: exact; 
                color-adjust: exact;
                display: block !important;
                visibility: visible !important;
            }
            /* Force all elements to be visible and properly styled for print */
            #receipt-fragment-wrapper * {
                visibility: visible !important;
                color: #000 !important;
            }
        }
    </style>

    {{-- Render the same content used in billing.receipt but only the body portion --}}
    <div class="header">
        <div class="logo-section">
            @if(isset($logoData) && $logoData)
                <img src="{{ $logoData }}" alt="Hospital Logo">
            @else
                <div style="width:80px;height:80px;border:2px solid #000;display:flex;align-items:center;justify-content:center;font-size:10px;text-align:center;">
                    Hospital<br>Logo
                </div>
            @endif
        </div>
        <div class="header-content">
            <div class="hospital-name">ROMEL CRUZ HOSPITAL</div>
            <div class="hospital-address">702 Matimbo, City of Malolos, Bulacan<br>Tel/Fax No. (044) 791-3025</div>
            <div class="receipt-title">
                @if(isset($isCashier) && $isCashier && $billing->status === 'paid')
                    STATEMENT OF ACCOUNT
                @else
                    BILLING RECEIPT
                @endif
            </div>
        </div>
    </div>

    <div class="billing-info">
        <div class="left">
            <div class="info-section">
                <div class="info-label">Patient Information:</div>
                <div class="info-value">
                    <strong>{{ $billing->patient->display_name ?? ($billing->patient->first_name . ' ' . $billing->patient->last_name) }}</strong><br>
                    Date of Birth: {{ $billing->patient->date_of_birth ? \Carbon\Carbon::parse($billing->patient->date_of_birth)->format('M d, Y') : 'N/A' }}<br>
                    @if(isset($billing->admission) && $billing->admission->admission_date)
                        Admission Date: {{ \Carbon\Carbon::parse($billing->admission->admission_date)->setTimezone('Asia/Manila')->format('M d, Y g:i A') }}<br>
                    @endif
                    @if(!empty($billing->patient->address))
                        Address: {{ $billing->patient->address }}<br>
                    @endif
                    @if(!empty($billing->patient->contact_number))
                        Contact: {{ $billing->patient->contact_number }}
                    @endif
                </div>
            </div>
        </div>

        <div class="right">
            <div class="info-section">
                <div class="info-label">Billing Details:</div>
                <div class="info-value">
                    <strong>Receipt #: {{ $billing->billing_number }}</strong><br>
                    Date: {{ $billing->billing_date->setTimezone('Asia/Manila')->format('M d, Y g:i A') }}<br>
                    Clearance Date: {{ $billing->payment_date ? \Carbon\Carbon::parse($billing->payment_date)->setTimezone('Asia/Manila')->format('M d, Y g:i A') : 'N/A' }}<br>
                    @if(isset($billing->admission) && $billing->admission->admission_date)
                        Admission: {{ \Carbon\Carbon::parse($billing->admission->admission_date)->setTimezone('Asia/Manila')->format('M d, Y') }}<br>
                    @endif
                    Status: {{ ucfirst($billing->status) }}<br>
                    Prepared by: {{ $billing->createdBy->name ?? '' }}
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom:20px;">
        <table class="items-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Description</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billing->billingItems as $item)
                <tr>
                    <td>{{ $item->getFormattedItemType() }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">PHP {{ number_format($item->unit_price,2) }}</td>
                    <td class="text-right">PHP {{ number_format($item->total_amount,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-section">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="width:70%; font-weight:bold;">NET AMOUNT DUE:</td>
                <td style="width:30%; text-align:right; font-weight:bold;">PHP {{ number_format($billing->net_amount,2) }}</td>
            </tr>
            @if($billing->status === 'paid' && $billing->payment_amount)
            <tr>
                <td style="font-weight:bold;">AMOUNT PAID:</td>
                <td style="text-align:right; font-weight:bold;">PHP {{ number_format($billing->payment_amount,2) }}</td>
            </tr>
            @if($billing->change_amount > 0)
            <tr>
                <td style="font-weight:bold;">CHANGE:</td>
                <td style="text-align:right; font-weight:bold;">PHP {{ number_format($billing->change_amount,2) }}</td>
            </tr>
            @endif
            @endif
        </table>
    </div>

    <div style="margin-top:20px; text-align:center; font-size:10px;">
        <div style="display:inline-block; width:240px; text-align:center;">
            <div style="height:60px; border-bottom:1px solid #000; width:240px; margin:0 auto 6px;"></div>
            @php
                // Prefer processedBy name for cashier prints, createdBy for billing role
                if (isset($isCashier) && $isCashier) {
                    $signatureName = $billing->processedBy?->name ?? '____________________';
                    $signatureLabel = 'Cashier Staff';
                } elseif (isset($isBilling) && $isBilling) {
                    $signatureName = $billing->createdBy?->name ?? '____________________';
                    $signatureLabel = 'Billing Staff';
                } else {
                    $signatureName = $billing->processedBy?->name ?? $billing->createdBy?->name ?? '____________________';
                    $signatureLabel = $billing->processedBy ? 'Cashier Staff' : 'Billing Staff';
                }
            @endphp
            <div style="font-weight:700;">{{ $signatureName }}</div>
            <div style="font-size:12px;">{{ $signatureLabel }}</div>
        </div>
    </div>
    
</div>
