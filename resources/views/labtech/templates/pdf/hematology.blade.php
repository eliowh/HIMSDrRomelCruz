@php
    $facility = config('lab_settings.facility');
    $signature = config('lab_settings.signature');
    $dateStr = now()->format('l, F j, Y');
    
    // Calculate age from date of birth if available
    $age = '';
    if ($patient->date_of_birth) {
        $age = \Carbon\Carbon::parse($patient->date_of_birth)->age;
    }
    
    // Get sex from the form values or patient record
    $sex = $values['sex'] ?? ($patient->sex ?? $patient->gender ?? '');
    
    // Logo handling - try to load without base64 first for DomPDF
    $logoPath = public_path('img/hospital_logo.png');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <style>
        @page { margin: 18px 18px 28px 18px; }
        body { font-family: 'Arial', sans-serif; font-size: 11px; }
        .sheet { width:100%; border:1px solid #000; border-collapse:collapse; }
        .header-table { width:100%; border-collapse:collapse; }
        .header-table td { border:1px solid #000; padding:2px 4px; }
        .header-logo { width:70px; text-align:center; vertical-align:middle; }
        .logo-img { max-width:60px; }
        .facility-name { font-size:15px; font-weight:700; letter-spacing:0.5px; }
        .facility-address { font-size:10px; }
        .facility-license { font-size:10px; font-weight:600; }
        .form-title { font-size:15px; font-weight:700; text-align:center; border:1px solid #000; padding:4px 0; letter-spacing:1px; }
        .meta-header td { font-size:10px; font-weight:600; text-align:center; }
        .meta-header td span.value { font-weight:normal; display:block; margin-top:6px; font-size:11px; }
        table.results { width:100%; border-collapse:collapse; margin-top:4px; }
        table.results th, table.results td { border:1px solid #000; padding:3px 4px; }
        table.results th { font-size:11px; background:#f8f8f8; letter-spacing:0.5px; }
        table.results td.label { width:160px; }
        .footer { margin-top:28px; width:100%; }
        .sign-row { height:70px; }
        .signature-block { text-align:center; font-size:10px; }
        .signature-block .line { margin-top:40px; border-top:1px solid #000; width:200px; margin-left:auto; margin-right:auto; }
        .ref-col { width:120px; }
        .unit-col { width:70px; }
        .value-col { width:100px; }
        .grid-placeholder { height:140px; border:1px solid #000; border-top:none; border-left:none; border-right:none; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo" rowspan="3">
                @if(file_exists($logoPath))
                    <img src="{{ asset('img/hospital_logo.png') }}" class="logo-img" />
                @else
                    <div style="width:60px;height:60px;border:1px solid #000;display:flex;align-items:center;justify-content:center;font-size:8px;">LOGO</div>
                @endif
            </td>
            <td style="text-align:center; border:1px solid #000;">
                <div class="facility-name">{{ strtoupper($facility['name']) }}</div>
                <div class="facility-address">{{ $facility['address_line'] }}</div>
                <div class="facility-license">{{ $facility['license_line'] }}</div>
            </td>
        </tr>
        <tr>
            <td class="form-title">HEMATOLOGY LABORATORY RESULT FORM</td>
        </tr>
        <tr>
            <td style="padding:0;">
                <table class="sheet meta-header" style="border:none;border-collapse:collapse;">
                    <tr>
                        <td style="width:35%;">NAME OF PATIENT<br><span class="value">{{ $patient->first_name }} {{ $patient->last_name }}</span></td>
                        <td style="width:15%;">AGE/SEX<br><span class="value">{{ $age }}/{{ strtoupper($sex) }}</span></td>
                        <td style="width:15%;">WARD<br><span class="value"></span></td>
                        <td style="width:35%;">DATE<br><span class="value">{{ $dateStr }}</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="results">
        <thead>
            <tr>
                <th style="width:180px;">EXAMINATION</th>
                <th class="value-col">VALUE</th>
                <th class="unit-col">UNIT</th>
                <th class="ref-col">REFERENCE VALUE</th>
            </tr>
        </thead>
        <tbody>
            @foreach(($template['fields'] ?? []) as $row)
                <tr>
                    <td class="label">{{ strtoupper($row['label']) }}</td>
                    <td>{{ $values[$row['key']] ?? '' }}</td>
                    <td>{{ $row['unit'] ?? '' }}</td>
                    <td>{{ $row['ref'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table style="width:100%; border-collapse:collapse;">
            <tr class="sign-row">
                <td style="border:1px solid #000; border-right:none; width:20%;"></td>
                <td style="border:1px solid #000; border-right:none; width:20%;"></td>
                <td style="border:1px solid #000; border-right:none; width:20%;"></td>
                <td style="border:1px solid #000; border-right:none; width:20%;"></td>
                <td style="border:1px solid #000; width:20%; vertical-align:bottom; text-align:center; font-size:10px; padding-bottom:4px;">
                    <div style="border-top:1px solid #000; width:100%; padding-top:4px; font-weight:600;"></div>
                    <div style="font-size:9px;">License No.: </div>
                    <div style="font-size:10px; font-weight:700; margin-top:4px;">{{ strtoupper($signature['designation']) }}</div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>