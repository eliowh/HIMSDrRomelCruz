<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Dashboard</title>
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
                <h2>Welcome, Nurse {{ $nurseName }}</h2>
                <p>Access and manage your nursing tasks and responsibilities.</p>
                <!-- Add dashboard content here -->
            </div>
        </div>
    </div>
</body>
</html>
