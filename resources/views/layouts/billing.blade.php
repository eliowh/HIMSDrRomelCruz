<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/hospital_logo.png') }}">
    <title>@yield('title', 'HIMS Billing')</title>
    
    {{-- Billing specific styles --}}
    <link rel="stylesheet" href="{{ asset('css/billingcss/billing.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Custom billing styles for modern UI --}}
    <style>
        .billing-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            margin-left: 250px;
            transition: margin-left 0.3s ease;
        }
        
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
        }
        
        .card {
            border: none;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .card-header {
            background: linear-gradient(135deg, #367F2B, #2d6624);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            transform: translateY(-1px);
        }
        
        .table thead th {
            background: #f8f9fa;
            border-top: none;
            font-weight: 600;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .form-control {
            border-radius: 6px;
        }
        
        .badge {
            border-radius: 4px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    @include('billing.billing_header')

    <div class="billing-layout">
        @include('billing.billing_sidebar')
        
        <main class="main-content">
            @yield('content')
        </main>
    </div>
    
    {{-- JavaScript libraries --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @stack('scripts')
</body>
</html>