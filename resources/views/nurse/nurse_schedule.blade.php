<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Schedule</title>
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
                <h2>Schedule Management</h2>
                <p>View and manage your work schedule and shift assignments.</p>
                <!-- Add schedule content here -->
            </div>
        </div>
    </div>
    @include('nurse.modals.notification_system')
</body>
</html>
