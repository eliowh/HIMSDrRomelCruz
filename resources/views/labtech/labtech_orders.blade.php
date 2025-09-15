<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Orders</title>
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
                <h2>Lab Orders</h2>
                <p>Here you can view and manage laboratory test orders from doctors.</p>
            </div>
            
            <div class="labtech-card">
                <h3>Pending Orders</h3>
                <!-- Placeholder for pending orders list -->
                <div class="placeholder-content">
                    <p>No pending orders at the moment.</p>
                </div>
            </div>

            <div class="labtech-card">
                <h3>Completed Orders</h3>
                <!-- Placeholder for completed orders list -->
                <div class="placeholder-content">
                    <p>No completed orders to display.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
