<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('img/hospital_logo.png') }}">
    <title>@yield('title','HIMS Nurse')</title>

    {{-- main styles used by nurse pages --}}
    <link rel="stylesheet" href="{{ asset('css/nursecss/nurse.css') }}">
    <link rel="stylesheet" href="{{ asset('css/nursecss/nurse_patients.css') }}">
    {{-- per-page styles can be pushed by views (e.g. nurse_home) --}}
    @stack('styles')
</head>
<body>
    {{-- Add top navigation/header (DR. Romel Cruz Hospital + user info) --}}
    @includeIf('nurse.nurse_header')

    <div class="nurse-layout">
        {{-- include sidebar/header when available, fail silently if not --}}
        @includeIf('nurse.nurse_sidebar')
        <div class="main-content">
            @yield('content')
        </div>
    </div>
    
    {{-- place for page scripts --}}
    @stack('scripts')
</body>
</html>