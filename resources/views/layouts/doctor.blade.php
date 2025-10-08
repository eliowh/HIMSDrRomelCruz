<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/hospital_logo.png') }}">
    <title>@yield('title','HIMS Doctor')</title>

    {{-- main styles used by doctor pages --}}
    <link rel="stylesheet" href="{{ asset('css/doctorcss/doctor.css') }}">
    <link rel="stylesheet" href="{{ asset('css/doctorcss/doctor_patients.css') }}">
</head>
<body>
    {{-- Add top navigation/header (DR. Romel Cruz Hospital + user info) --}}
    @includeIf('doctor.doctor_header')

    <div class="doctor-layout">
        {{-- include sidebar/header when available, fail silently if not --}}
        @includeIf('doctor.doctor_sidebar')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    
    {{-- place for page scripts --}}
    @stack('scripts')
</body>
</html>