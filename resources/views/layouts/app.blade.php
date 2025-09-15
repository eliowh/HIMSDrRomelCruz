<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title','HIMS Nurse')</title>

    {{-- main styles used by nurse pages --}}
    <link rel="stylesheet" href="{{ url('css/nurse.css') }}">
    <link rel="stylesheet" href="{{ url('css/nurse_addPatients.css') }}">
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