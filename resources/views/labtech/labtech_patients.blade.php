<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Records</title>
    <link rel="stylesheet" href="{{ url('css/labtech.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @php
        $labtechName = auth()->user()->name ?? 'Lab Technician';
    @endphp
    @include('labtech.labtech_header')

    <div class="labtech-layout">
        @include('labtech.labtech_sidebar')

        <main class="main-content">
            <div class="labtech-card">
                <h2>Patient Records</h2>
                <p>View patient laboratory records and test history.</p>
            </div>
            
            <div class="labtech-card">
                <h3>Recent Patients</h3>
                <!-- Placeholder for patients list -->
                <div class="placeholder-content">
                    <p>No recent patient records to display.</p>
                </div>
            </div>

            <div class="labtech-card">
                <h3>Test History</h3>
                <!-- Placeholder for test history -->
                <div class="placeholder-content">
                    <p>No test history available.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
