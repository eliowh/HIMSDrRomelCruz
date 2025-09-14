@extends('layouts.app')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

<link rel="stylesheet" href="{{ url('css/nurse_patients.css') }}">

<div class="nurse-card">
    <div class="patients-header">
        <h3>Patients</h3>
        {{-- Add Patient button removed as requested --}}
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" class="patients-search">
        <input type="search" name="q" value="{{ $q }}" placeholder="Search name or patient no" class="search-input">
    </form>

    @if($patients->count())
        <div class="table-wrap">
            <table class="patients-table">
                <thead>
                    <tr>
                        <th>Patient No</th>
                        <th>Name</th>
                        <th>DOB / Age</th>
                        <th>Location</th>
                        <th>Nationality</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($patients as $p)
                    <tr>
                        <td class="col-no">{{ $p->patient_no }}</td>
                        <td class="col-name">{{ $p->last_name }}, {{ $p->first_name }}{{ $p->middle_name ? ' '.$p->middle_name : '' }}</td>
                        <td class="col-dob">
                            {{ $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-' }}<br>
                            <small class="text-muted">{{ $p->age_years ?? '-' }}y {{ $p->age_months ?? '-' }}m {{ $p->age_days ?? '-' }}d</small>
                        </td>
                        <td class="col-location">{{ $p->barangay ? $p->barangay.',' : '' }} {{ $p->city }}, {{ $p->province }}</td>
                        <td class="col-natl">{{ $p->nationality }}</td>
                        <td class="col-actions">
                            <a href="{{ url('/nurse/patients/'.$p->patient_no) }}" class="btn view-btn">View</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">
            {{ $patients->links() }}
        </div>
    @else
        <div class="alert alert-info">No patients found. <a href="{{ url('/nurse/addPatients') }}">Add first patient</a>.</div>
    @endif
</div>
@endsection