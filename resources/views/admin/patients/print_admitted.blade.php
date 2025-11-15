<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admitted Patients</title>
    <style>
        body { font-family: Arial, sans-serif; color:#000; margin:20px; }
        /* Header styles borrowed from billing receipt for consistent prints */
        .header { display: table; width: 100%; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 12px; }
        .logo-section { display: table-cell; width: 80px; vertical-align: middle; padding-right: 12px; }
        .logo-section img { width: 80px; height: 80px; }
        .header-content { display: table-cell; vertical-align: middle; text-align: center; }
        .hospital-name { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .hospital-address { font-size: 12px; margin-bottom: 6px; }
        .report-title { font-size: 16px; font-weight: bold; margin-top: 6px; }

        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; font-size: 13px; }
        th { background: #f5f5f5; }
        .small { font-size: 12px; color: #666; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div id="receipt-fragment-wrapper">
        <style>
            /* Header styles copied from receipt fragment for consistent hospital header */
            #receipt-fragment-wrapper { font-family: Arial, sans-serif; color: #000; }
            #receipt-fragment-wrapper .header { display: table; width:100%; border-bottom:2px solid #000; padding-bottom:20px; margin-bottom:20px; }
            #receipt-fragment-wrapper .logo-section { display: table-cell; width:80px; vertical-align: middle; padding-right:20px; }
            #receipt-fragment-wrapper .logo-section img { width:80px; height:80px; }
            #receipt-fragment-wrapper .header-content { display: table-cell; vertical-align: middle; text-align:center; }
            #receipt-fragment-wrapper .hospital-name { font-size:24px; font-weight:bold; margin-bottom:5px; }
            #receipt-fragment-wrapper .hospital-address { font-size:12px; margin-bottom:6px; }
            #receipt-fragment-wrapper .receipt-title { font-size:18px; font-weight:bold; margin-top:10px; }
        </style>

        <div class="header">
            <div class="logo-section">
                <img src="{{ asset('img/hospital_logo.png') }}" alt="Hospital Logo">
            </div>
            <div class="header-content">
                <div class="hospital-name">ROMEL CRUZ HOSPITAL</div>
                <div class="hospital-address">702 Matimbo, City of Malolos, Bulacan<br>Tel/Fax No. (044) 791-3025</div>
                <div class="receipt-title">Admitted Patients</div>
                <div class="small">Generated: {{ now()->format('Y-m-d H:i:s') }}</div>
                @php
                    $period = request()->get('period');
                    $dateFrom = request()->get('date_from');
                    $dateTo = request()->get('date_to');
                    // Normalize and map period values to friendly labels
                    $p = $period ? strtolower($period) : '';
                    if ($p === 'past_year') { $periodLabel = 'Past Year'; }
                    elseif ($p === 'past_month') { $periodLabel = 'Past Month'; }
                    elseif ($p === 'past_week') { $periodLabel = 'Past Week'; }
                    elseif ($p === 'this_year') { $periodLabel = 'This Year'; }
                    elseif ($p === 'this_month') { $periodLabel = 'This Month'; }
                    elseif ($p === 'this_week') { $periodLabel = 'This Week'; }
                    elseif ($dateFrom || $dateTo) { $periodLabel = trim(($dateFrom ? $dateFrom : '') . ' - ' . ($dateTo ? $dateTo : '')); }
                    else { $periodLabel = 'All time'; }
                @endphp
                <div class="small">Period: {{ $periodLabel }}</div>
            </div>
        </div>
    </div>

    @if(isset($totalAdmittedPatients) || isset($currentlyAdmitted))
        <div style="margin-top:8px; margin-bottom:8px; font-size:14px;">
            <strong>Total Patients Admitted:</strong> {{ $totalAdmittedPatients ?? 0 }}
            &nbsp;&nbsp;&nbsp;
            <strong>Currently Admitted:</strong> {{ $currentlyAdmitted ?? 0 }}
        </div>
    @endif

    <div class="no-print" style="margin-bottom:12px;">
        <button onclick="window.print()">Print</button>
        <a href="{{ url()->previous() }}" style="margin-left:8px;">Back</a>
    </div>

    <table>
        <thead>
            @if(isset($patients))
                <tr>
                    <th>Patient No.</th>
                    <th>Patient Name</th>
                    <th>DOB</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>Address</th>
                    <th>Nationality</th>
                    <th>Contact</th>
                    <th>Room</th>
                    <th>Admitted At</th>
                    <th>Status</th>
                </tr>
            @else
                <tr>
                    <th>Admission No.</th>
                    <th>Patient No.</th>
                    <th>Patient Name</th>
                    <th>DOB</th>
                    <th>Age</th>
                    <th>Sex</th>
                    <th>Address</th>
                    <th>Nationality</th>
                    <th>Contact</th>
                    <th>Room</th>
                    <th>Admitted At</th>
                    <th>Status</th>
                </tr>
            @endif
        </thead>
        <tbody>
            @if(isset($patients))
                @forelse($patients as $index => $patient)
                    @php
                        $adms = $admissionsMap->get($patient->id, collect());
                        // Build a display address from barangay/city/province if address not present
                        $addressParts = array_filter([
                            $patient->barangay ?? null,
                            $patient->city ?? null,
                            $patient->province ?? null,
                        ]);
                        $displayAddress = $patient->address ?? (count($addressParts) ? implode(', ', $addressParts) : '-');
                        $displayNationality = $patient->nationality ?? '-';
                        $displaySex = $patient->sex ? ucfirst(strtolower($patient->sex)) : '-';
                    @endphp
                    @if($adms->count() > 0)
                        @foreach($adms as $admIndex => $adm)
                            <tr>
                                @if($admIndex === 0)
                                    <td rowspan="{{ $adms->count() }}">{{ $patient->patient_no ?? '-' }}</td>
                                    <td rowspan="{{ $adms->count() }}">{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</td>
                                    <td rowspan="{{ $adms->count() }}">
                                        @if(!empty($patient->date_of_birth))
                                            {{ \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td rowspan="{{ $adms->count() }}">
                                        @if(!empty($patient->date_of_birth))
                                            @php
                                                try { $age = \Carbon\Carbon::parse($patient->date_of_birth)->age; } catch (\Exception $e) { $age = 'N/A'; }
                                            @endphp
                                            {{ $age }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td rowspan="{{ $adms->count() }}">{{ $displaySex }}</td>
                                    <td rowspan="{{ $adms->count() }}">{{ $displayAddress }}</td>
                                    <td rowspan="{{ $adms->count() }}">{{ $displayNationality }}</td>
                                    <td rowspan="{{ $adms->count() }}">{{ $patient->contact_number ?? '-' }}</td>
                                @endif

                                <td>{{ $adm->room_no ?? '-' }}</td>
                                <td>{{ optional($adm->admission_date)->format('Y-m-d H:i') ?? '-' }}</td>
                                <td>{{ ucfirst($adm->status ?? '') }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $patient->patient_no ?? '-' }}</td>
                            <td>{{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</td>
                            <td>
                                @if(!empty($patient->date_of_birth))
                                    {{ \Carbon\Carbon::parse($patient->date_of_birth)->format('Y-m-d') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if(!empty($patient->date_of_birth))
                                    @php
                                        try { $age = \Carbon\Carbon::parse($patient->date_of_birth)->age; } catch (\Exception $e) { $age = 'N/A'; }
                                    @endphp
                                    {{ $age }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $displaySex }}</td>
                            <td>{{ $displayAddress }}</td>
                            <td>{{ $displayNationality }}</td>
                            <td>{{ $patient->contact_number ?? '-' }}</td>
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="11" style="text-align:center;">No active patients found.</td>
                    </tr>
                @endforelse
            @else
                @forelse($admissions as $index => $admission)
                    @php
                        $pat = $admission->patient;
                        $dob = optional($pat)->date_of_birth;
                        $addrParts = array_filter([
                            $pat->barangay ?? null,
                            $pat->city ?? null,
                            $pat->province ?? null,
                        ]);
                        $patAddress = $pat->address ?? (count($addrParts) ? implode(', ', $addrParts) : '-');
                        $patNationality = $pat->nationality ?? '-';
                        $patSex = $pat?->sex ? ucfirst(strtolower($pat->sex)) : '-';
                        try { $patAge = $dob ? \Carbon\Carbon::parse($dob)->age : 'N/A'; } catch (\Exception $e) { $patAge = 'N/A'; }
                    @endphp
                    <tr>
                        <td>{{ $admission->admission_number ?? $admission->id }}</td>
                        <td>{{ $pat->patient_no ?? '-' }}</td>
                        <td>{{ $pat->first_name ?? '' }} {{ $pat->last_name ?? '' }}</td>
                        <td>
                            @if($dob)
                                {{ \Carbon\Carbon::parse($dob)->format('Y-m-d') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $patAge }}</td>
                        <td>{{ $patSex }}</td>
                        <td>{{ $patAddress }}</td>
                        <td>{{ $patNationality }}</td>
                        <td>{{ $pat->contact_number ?? '-' }}</td>
                        <td>{{ $admission->room_no ?? '-' }}</td>
                        <td>{{ optional($admission->admission_date)->format('Y-m-d H:i') ?? '-' }}</td>
                        <td>{{ ucfirst($admission->status ?? '') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align:center;">No active admissions found.</td>
                    </tr>
                @endforelse
            @endif
        </tbody>
    </table>
</table>

@if(request()->get('print'))
    <script>
        // Auto-trigger print when this view is opened specifically for printing.
        (function() {
            try {
                // Focus the window first (helps some browsers bring up print dialog)
                window.focus();

                // Give the browser a short moment to render before printing
                setTimeout(function() {
                    window.print();
                }, 250);

                // Attempt to close the window after printing (some browsers may block this)
                window.onafterprint = function() {
                    try { window.close(); } catch (e) { /* ignore */ }
                };
            } catch (e) {
                console.error('Auto-print failed', e);
            }
        })();
    </script>
@endif

</body>
</html>

<!-- Report footer: who generated the report -->
@php
    $generatedBy = auth()->user()->name ?? (auth()->user()->email ?? 'System');
    $generatedRole = auth()->user()->role ?? null;
    $footerText = $generatedBy . ($generatedRole ? ' (' . ucfirst($generatedRole) . ')' : '');
@endphp

<style>
    /* Footer for printed report */
    .report-footer {
        position: fixed;
        bottom: 10px;
        left: 20px;
        right: 20px;
        font-size: 12px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    @media screen {
        .report-footer { display: none; }
    }
</style>

<div class="report-footer">
    <div>Generated by: {{ $footerText }}</div>
    <div class="small">Printed: {{ now()->format('Y-m-d H:i:s') }}</div>
</div>
</html>