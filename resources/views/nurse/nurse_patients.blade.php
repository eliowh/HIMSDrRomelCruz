
@extends('layouts.app')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

<div class="nurse-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
        <h3 style="margin:0;">Patients</h3>
        <a href="{{ url('/nurse/addPatients') }}" class="btn">+ Add Patient</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" style="margin-bottom:12px;">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search name or patient no" style="width:100%;padding:8px;border-radius:8px;border:1px solid #e6e9ef;">
    </form>

    @if($patients->count())
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid #eee;">
                        <th style="padding:8px">Patient No</th>
                        <th style="padding:8px">Name</th>
                        <th style="padding:8px">DOB / Age</th>
                        <th style="padding:8px">Location</th>
                        <th style="padding:8px">Nationality</th>
                        <th style="padding:8px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($patients as $p)
                    <tr style="border-bottom:1px solid #fbfbfb;">
                        <td style="padding:8px;vertical-align:middle;">{{ $p->patient_no }}</td>
                        <td style="padding:8px;vertical-align:middle;">{{ $p->last_name }}, {{ $p->first_name }}{{ $p->middle_name ? ' '.$p->middle_name : '' }}</td>
                        <td style="padding:8px;vertical-align:middle;">
                            {{ $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-' }}<br>
                            <small class="text-muted">{{ $p->age_years ?? '-' }}y {{ $p->age_months ?? '-' }}m {{ $p->age_days ?? '-' }}d</small>
                        </td>
                        <td style="padding:8px;vertical-align:middle;">{{ $p->barangay ? $p->barangay.',' : '' }} {{ $p->city }}, {{ $p->province }}</td>
                        <td style="padding:8px;vertical-align:middle;">{{ $p->nationality }}</td>
                        <td style="padding:8px;vertical-align:middle;">
                            <a href="{{ url('/nurse/patients/'.$p->patient_no) }}" class="btn" style="background:#f3f4f6;color:#111827;border:none;padding:6px 8px;border-radius:6px;text-decoration:none;">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;">
            {{ $patients->links() }}
        </div>
    @else
        <div class="alert alert-info">No patients found. <a href="{{ url('/nurse/addPatients') }}">Add first patient</a>.</div>
    @endif
</div>
@endsection
