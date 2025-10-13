@php
    $facility = config('lab_settings.facility');
    $signature = config('lab_settings.signature');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>{{ $template['title'] ?? 'Lab Result' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
        .header { text-align: center; font-weight: bold; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .header-table td { border: 1px solid #000; padding: 4px; }
        .logo-cell { width: 80px; text-align: center; vertical-align: middle; }
        .logo-img { max-width: 70px; max-height: 70px; }
        .section { background: #eee; font-weight: bold; }
        .meta td { border: 1px solid #000; font-size: 11px; }
        .meta th { background: #f5f5f5; }
        .sig-block { margin-top: 40px; text-align: center; font-size: 11px; }
        .small { font-size: 10px; }
    </style>
</head>
<body>
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
            <td class="header" style="border: 1px solid #000;">
                <div style="font-size:16px;">{{ $facility['name'] ?? 'ROMEL CRUZ HOSPITAL' }}</div>
                <div style="font-size:11px;">{{ $facility['address_line'] ?? '702 Matimbo, Malolos, Bulacan' }}<br/>{{ $facility['license_line'] ?? 'DOH-BRL LICENSE NO. 2480' }}</div>
            </td>
        </tr>
        <tr>
            <td class="header" style="border: 1px solid #000;">
                <div style="margin-top:6px;font-size:15px;">{{ strtoupper($template['title'] ?? 'LABORATORY RESULT FORM') }}</div>
            </td>
        </tr>
    </table>

    <table class="meta" style="margin-bottom:12px;">
        <tr>
            <th style="width:35%">NAME OF PATIENT</th>
            <th style="width:15%">AGE/SEX</th>
            <th style="width:15%">WARD</th>
            <th style="width:35%">DATE</th>
        </tr>
        <tr>
            <td>{{ $patient->display_name ?? $patient->first_name.' '.$patient->last_name }}</td>
            <td>{{ $patient->age ?? 'N/A' }}/{{ $patient->sex ?? 'N/A' }}</td>
            <td>{{ $patient->ward ?? '' }}</td>
            <td>{{ now()->format('l, F d, Y') }}</td>
        </tr>
    </table>

    @if(isset($template['sections']))
        @foreach($template['sections'] as $sectionTitle => $fields)
            <table style="margin-bottom:10px;">
                <tr><th colspan="3" class="section">{{ $sectionTitle }}</th></tr>
                @foreach($fields as $f)
                    <tr>
                        <td style="width:40%">{{ $f['label'] }}</td>
                        <td style="width:30%">{{ $values[$f['key']] ?? '' }}</td>
                        <td style="width:30%">{{ $f['ref'] ?? '' }}</td>
                    </tr>
                @endforeach
            </table>
        @endforeach
    @else
        <table>
            <tr>
                <th style="width:40%">{{ isset($template['fields'][0]['ref']) ? 'EXAMINATION / TEST' : 'TEST' }}</th>
                @if(isset($template['fields'][0]['ref']))<th style="width:20%">VALUE / RESULT</th><th style="width:15%">UNIT</th><th style="width:25%">REFERENCE / NORMAL VALUE</th>@else <th style="width:60%">RESULT</th> @endif
            </tr>
            @foreach($template['fields'] as $f)
                <tr>
                    <td>{{ $f['label'] }}</td>
                    @if(isset($f['ref']))
                        <td>{{ $values[$f['key']] ?? '' }}</td>
                        <td>{{ $f['unit'] ?? '' }}</td>
                        <td>{{ $f['ref'] ?? '' }}</td>
                    @else
                        <td>{{ $values[$f['key']] ?? '' }}</td>
                    @endif
                </tr>
            @endforeach
        </table>
    @endif

    <div class="sig-block" style="margin-top:60px;">
        <div>{{ $currentUser->name ?? 'Lab Technician' }}</div>
        <div class="small">License No.: {{ $currentUser->license_number ?? 'N/A' }}</div>
        <div style="font-weight:bold; margin-top:4px;">{{ strtoupper($currentUser->role === 'lab_technician' ? 'MEDICAL TECHNOLOGIST' : ($currentUser->role ?? 'LAB STAFF')) }}</div>
    </div>
</body>
</html>
