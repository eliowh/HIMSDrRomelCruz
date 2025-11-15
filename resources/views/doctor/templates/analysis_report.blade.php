<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        @page { margin: 18px 18px 28px 18px; }
        body { font-family: 'Arial', sans-serif; font-size: 12px; line-height: 1.4; }
        .header-container { width: 100%; margin-bottom: 20px; }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { padding: 8px; vertical-align: top; }
        .logo-cell { width: 120px; text-align: right; padding-right: 10px; }
        .hospital-info { text-align: center; font-weight: bold; width: 100%; padding-left: 30px;}
        .hospital-name { font-size: 16px; font-weight: 700; margin-bottom: 2px; text-align: center; }
        .hospital-address { font-size: 10px; margin-bottom: 8px; text-align: center; }
        .department-title { font-size: 14px; font-weight: 700; text-decoration: underline; margin-bottom: 2px; text-align: center; }
        .section-title { font-size: 12px; font-weight: 600; text-align: center; }
        
        .patient-info { margin: 20px 0; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { 
            padding: 6px;
            vertical-align: top;
            border: none;
        }
        .info-label { 
            font-weight: bold; 
            font-size: 12px;
            width: 80px;
            display: inline-block;
        }
        .info-value { 
            font-size: 12px;
        }
        .col-name { width: 40%; }
        .col-age { width: 15%; }
        .col-sex { width: 15%; }
        .col-address { width: 50%; }
        .col-date { width: 25%; }
        .col-doctor { width: 40%; }
        .col-test { width: 35%; }
        
        .findings-section { margin: 30px 0; }
        .findings-title { font-weight: bold; text-decoration: underline; margin-bottom: 10px; font-size: 14px; }
        .findings-content { min-height: 300px; border: 1px solid #000; padding: 15px; background: #fff; font-size: 13px; line-height: 1.6; }
        
        .recommendations-section { margin: 20px 0; }
        .recommendations-title { font-weight: bold; text-decoration: underline; margin-bottom: 10px; font-size: 14px; }
        .recommendations-content { min-height: 100px; border: 1px solid #000; padding: 15px; background: #fff; font-size: 13px; line-height: 1.6; }
        
        .signature-section { margin-top: 40px; text-align: right; }
        .signature-box { display: inline-block; text-align: center; min-width: 200px; }
        .signature-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; }
        .doctor-name { font-weight: bold; font-size: 12px; }
        .doctor-title { font-size: 10px; margin-top: 2px; }
        
        .report-footer { margin-top: 30px; font-size: 9px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <!-- Hospital Header -->
    <div class="header-container">
        <table class="header-table">
            <tr>
                <td class="logo-cell" rowspan="2">
                    @if(isset($logoData) && $logoData)
                        <img src="{{ $logoData }}" style="width:65px;height:55px;object-fit:contain;object-position:center;" alt="Hospital Logo" />
                    @else
                        <div style="width:70px;height:60px;border:1px solid #000;display:flex;align-items:center;justify-content:center;font-size:6px;background:#e8e8e8;text-align:center;padding:2px;">
                            <div>
                                <div style="font-weight:bold;font-size:7px;">ROMEL</div>
                                <div style="font-weight:bold;font-size:7px;">CRUZ</div>
                                <div style="font-weight:bold;font-size:7px;">HOSPITAL</div>
                            </div>
                        </div>
                    @endif
                </td>
                <td class="hospital-info">
                    <div class="hospital-name">ROMEL CRUZ HOSPITAL</div>
                    <div class="hospital-address">702 Matimbo, City of Malolos, Bulacan<br>Tel/Fax No. (044) 791-3025</div>
                    <div class="department-title">CLINICAL ANALYSIS DEPARTMENT</div>
                    <div class="section-title">DIAGNOSTIC CLINICAL ANALYSIS SECTION</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Patient Information -->
    <div class="patient-info">
        <table class="info-table">
            <tr>
                <td class="col-name">
                    <span class="info-label">NAME:</span>
                    <span class="info-value">{{ $patient->first_name }} {{ $patient->last_name }}</span>
                </td>
                <td class="col-age">
                    <span class="info-label">AGE:</span>
                    <span class="info-value">
                        @if($patient->date_of_birth)
                            {{ \Carbon\Carbon::parse($patient->date_of_birth)->age }}
                        @endif
                    </span>
                </td>
                <td class="col-sex">
                    <span class="info-label">SEX:</span>
                    <span class="info-value">{{ strtoupper($patient->sex ?? $patient->gender ?? '') }}</span>
                </td>
            </tr>
            <tr>
                <td class="col-address">
                    <span class="info-label">ADDRESS:</span>
                    <span class="info-value">
                        @php
                            $addressParts = array_filter([
                                $patient->barangay,
                                $patient->city,
                                $patient->province
                            ]);
                            $address = implode(', ', $addressParts);
                        @endphp
                        {{ $address }}
                    </span>
                </td>
                <td class="col-date" colspan="2">
                    <span class="info-label">DATE:</span>
                    <span class="info-value">{{ $currentDate }}</span>
                </td>
            </tr>
            <tr>
                <td class="col-doctor">
                    <span class="info-label">DOCTOR:</span>
                    <span class="info-value">{{ $doctor->name }}</span>
                </td>
                <td class="col-test" colspan="2">
                    <span class="info-label">TEST TYPE:</span>
                    <span class="info-value">{{ strtoupper($labOrder->test_requested) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Clinical Findings -->
    <div class="findings-section">
        <div class="findings-title">CLINICAL FINDINGS:</div>
        <div class="findings-content">
            {!! nl2br(e($analysis->clinical_notes ?? '')) !!}
        </div>
    </div>

    <!-- Recommendations -->
    <div class="recommendations-section">
        <div class="recommendations-title">RECOMMENDATIONS:</div>
        <div class="recommendations-content">
            {!! nl2br(e($analysis->recommendations ?? '')) !!}
        </div>
    </div>

    <!-- Doctor Signature -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line">
                <div class="doctor-name">{{ strtoupper($doctor->name) }}</div>
                <div class="doctor-title">ATTENDING PHYSICIAN</div>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div class="report-footer">
        Analysis Report Generated on {{ now()->format('F j, Y \a\t g:i A') }}<br>
        Lab Order #{{ $labOrder->id }} | Patient ID: {{ $patient->patient_no }}
    </div>
</body>
</html>