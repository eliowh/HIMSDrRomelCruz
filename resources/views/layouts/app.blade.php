<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','HIMS Nurse')</title>

    {{-- main styles used by nurse pages --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Fallback for production -->
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}" media="print" onload="this.media='all'">
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