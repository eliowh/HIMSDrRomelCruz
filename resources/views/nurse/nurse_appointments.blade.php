<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Appointments</title>
    <link rel="stylesheet" href="{{url('css/nursecss/nurse.css')}}">
</head>
<body>
    @php
        $nurseName = auth()->user()->name ?? 'Nurse';
    @endphp
    @include('nurse.nurse_header')
    <div class="nurse-layout">
        @include('nurse.nurse_sidebar')
        <div class="main-content">
            <div class="nurse-card">
                <h2>Your Appointments</h2>
                <p>View and manage your scheduled appointments and patient care tasks.</p>
                <!-- Add appointments content here -->
            </div>
        </div>
    </div>
</body>
</html>
