<div class="patient-details-simple">
    <h4>Patient Information</h4>
    <p><strong>Patient Number:</strong> {{ $patient->patient_no ?? 'N/A' }}</p>
    <p><strong>Name:</strong> {{ ($patient->first_name ?? '') . ' ' . ($patient->last_name ?? '') }}</p>
    <p><strong>Status:</strong> {{ ucfirst($patient->status ?? 'active') }}</p>
    <p><strong>Room:</strong> {{ $patient->room_no ?? 'Not assigned' }}</p>
    @php
        $ageYears = $patient->date_of_birth ? intval(\Carbon\Carbon::parse($patient->date_of_birth)->diffInYears(now())) : null;
    @endphp
    <p><strong>Age:</strong> {{ $ageYears !== null ? $ageYears.' years' : 'N/A' }}</p>
    <p><strong>Created:</strong> {{ $patient->created_at ?? 'N/A' }}</p>
</div>

<style>
.patient-details-simple {
    padding: 20px;
    font-family: Arial, sans-serif;
}
.patient-details-simple h4 {
    color: #333;
    margin-bottom: 15px;
}
.patient-details-simple p {
    margin: 8px 0;
    line-height: 1.5;
}
</style>