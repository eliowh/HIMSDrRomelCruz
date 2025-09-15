<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link rel="stylesheet" href="{{url('css/doctor.css')}}">
</head>
<body>
    @php
        $doctorName = auth()->user()->name ?? 'Doctor';
    @endphp
    @include('doctor.doctor_header')
    <div class="doctor-layout">
        @include('doctor.doctor_sidebar')
        <div class="main-content">
            <div class="doctor-card">
                <h2>Patient Records</h2>
                <p>Access and manage your patient records and medical histories.</p>
                <!-- Add patient management content here -->
            </div>
        </div>
    </div>
</body>
</html>
