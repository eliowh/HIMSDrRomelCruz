<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - HIMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Fallback for production -->
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}" media="print" onload="this.media='all'">
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔒</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-message">
            {{ $exception->getMessage() ?: 'You do not have permission to access this resource.' }}
        </p>
        
        @if(auth()->check())
            <div class="error-details">
                <strong>Current Role:</strong> {{ ucfirst(auth()->user()->role) }}<br>
                <strong>User:</strong> {{ auth()->user()->name }}<br>
                <strong>Time:</strong> {{ now()->format('M d, Y h:i A') }}
            </div>
            
            <a href="javascript:history.back()" class="back-button">Go Back</a>
            
            @php
                $userRole = auth()->user()->role;
                $dashboardRoutes = [
                    'admin' => '/admin/home',
                    'inventory' => '/inventory/home', 
                    'pharmacy' => '/pharmacy/home',
                    'doctor' => '/doctor/home',
                    'nurse' => '/nurse/home',
                    'lab_technician' => '/labtech/home',
                    'cashier' => '/cashier/home',
                ];
                $dashboardUrl = $dashboardRoutes[$userRole] ?? '/login';
            @endphp
            
            <a href="{{ $dashboardUrl }}" class="login-button">Go to My Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="login-button">Login</a>
        @endif
    </div>
</body>
</html>