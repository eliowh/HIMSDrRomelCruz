@extends('layouts.app')

@section('title','Patients')

@section('content')
@php $patients = $patients ?? collect(); $q = $q ?? ''; @endphp

<link rel="stylesheet" href="{{ url('css/nursecss/nurse_patients.css') }}">

<div class="patients-grid">
    <div class="list-column">
        <div class="nurse-card">
            <div class="patients-header">
                <h3>Patients</h3>
                <form method="GET" class="patients-search" style="margin-left:auto;">
                    <input type="search" name="q" value="{{ $q }}" placeholder="Search name or patient no" class="search-input">
                </form>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($patients->count())
                <div class="table-wrap">
                    <table class="patients-table" id="patientsTable">
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
                            <tr class="patient-row" data-patient='@json($p)'>
                                <td class="col-no">{{ $p->patient_no }}</td>
                                <td class="col-name">{{ $p->last_name }}, {{ $p->first_name }}{{ $p->middle_name ? ' '.$p->middle_name : '' }}</td>
                                <td class="col-dob">
                                    {{ $p->date_of_birth ? $p->date_of_birth->format('Y-m-d') : '-' }}<br>
                                    <small class="text-muted">{{ $p->age_years ?? '-' }}y {{ $p->age_months ?? '-' }}m {{ $p->age_days ?? '-' }}d</small>
                                </td>
                                <td class="col-location">{{ $p->barangay ? $p->barangay.',' : '' }} {{ $p->city }}, {{ $p->province }}</td>
                                <td class="col-natl">{{ $p->nationality }}</td>
                                <td class="col-actions">
                                    <button type="button" class="btn view-btn js-open-patient">View</button>
                                    <button type="button" class="request-btn btn">Request</button>
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
                <div class="alert alert-info">No patients found.</div>
            @endif
        </div>
    </div>

    <div class="details-column">
        <div class="nurse-card details-card" id="detailsCard">
            <div class="patients-header">
                <h3>Patient Details</h3>
            </div>

            <div class="details-empty" id="detailsEmpty">Select a patient to view details.</div>

            <div class="details-content" id="detailsContent" style="display:none;">
                <dl class="patient-details">
                    <dt>Patient No</dt><dd id="md-patient_no">-</dd>
                    <dt>Full Name</dt><dd id="md-name">-</dd>
                    <dt>Date of Birth</dt><dd id="md-dob">-</dd>
                    <dt>Age</dt><dd id="md-age">-</dd>
                    <dt>Province / City / Barangay</dt><dd id="md-location">-</dd>
                    <dt>Nationality</dt><dd id="md-nationality">-</dd>
                    <dt>Room No.</dt><dd id="md-room_no">-</dd>
                    <dt>Admission Diagnosis</dt><dd id="md-admission_diagnosis">-</dd>
                    <dt>Admission Type</dt><dd id="md-admission_type">-</dd>
                    <dt>Service</dt><dd id="md-service">-</dd>
                    <dt>Doctor</dt><dd id="md-doctor_name">-</dd>
                    <dt>Doctor Type</dt><dd id="md-doctor_type">-</dd>
                    <dt>Created At</dt><dd id="md-created_at">-</dd>
                </dl>

                <div style="margin-top:12px;text-align:right;">
                    <a href="#" id="detailsViewFull" class="btn secondary">Open Full</a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const table = document.getElementById('patientsTable');
    const rows = table ? table.querySelectorAll('.patient-row') : [];
    const detailsCard = document.getElementById('detailsCard');
    const detailsEmpty = document.getElementById('detailsEmpty');
    const detailsContent = document.getElementById('detailsContent');

    function or(v){ return v===null||v===undefined||v==='' ? '-' : v; }

    function renderPatient(patient){
        document.getElementById('md-patient_no').textContent = or(patient.patient_no);
        document.getElementById('md-name').textContent = or([patient.last_name, patient.first_name, patient.middle_name].filter(Boolean).join(', '));
        document.getElementById('md-dob').textContent = or(patient.date_of_birth);
        const years = patient.age_years ?? '-';
        const months = patient.age_months ?? '-';
        const days = patient.age_days ?? '-';
        document.getElementById('md-age').textContent = `${years}y ${months}m ${days}d`;
        document.getElementById('md-location').textContent = or((patient.barangay ? patient.barangay + ', ' : '') + or(patient.city) + ', ' + or(patient.province));
        document.getElementById('md-nationality').textContent = or(patient.nationality);
        document.getElementById('md-room_no').textContent = or(patient.room_no);
        document.getElementById('md-admission_diagnosis').textContent = or(patient.admission_diagnosis);
        document.getElementById('md-admission_type').textContent = or(patient.admission_type);
        document.getElementById('md-service').textContent = or(patient.service);
        document.getElementById('md-doctor_name').textContent = or(patient.doctor_name);
        document.getElementById('md-doctor_type').textContent = or(patient.doctor_type);
        document.getElementById('md-created_at').textContent = or(patient.created_at);
    }

    function clearActive(){
        rows.forEach(r => r.classList.remove('active'));
    }

    rows.forEach(row => {
        const btn = row.querySelector('.js-open-patient');
        btn.addEventListener('click', function(){
            const payload = row.getAttribute('data-patient');
            try {
                const patient = JSON.parse(payload);
                clearActive();
                row.classList.add('active');
                detailsEmpty.style.display = 'none';
                detailsContent.style.display = '';
                renderPatient(patient);

                // update "Open Full" link to go to patient page if route exists
                const btnFull = document.getElementById('detailsViewFull');
                if (btnFull) {
                    btnFull.href = `/nurse/patients/${encodeURIComponent(patient.patient_no)}`;
                }
            } catch(e){
                console.error('Invalid patient JSON', e);
            }
        });
    });

    // optionally auto-select first row
    if (rows.length && !document.querySelector('.patient-row.active')) {
        rows[0].querySelector('.js-open-patient').click();
    }
});
</script>
@endpush

@endsection