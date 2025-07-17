<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>User Approval</title>
        <link rel="stylesheet" href="{{url('css/admin.css')}}">
    </head>
    @include('admin.admin_header')
    <body>
        @php
            $adminName = auth()->user()->name ?? 'Admin';
        @endphp
        <div class="admin-layout">
            @include('admin.admin_sidebar')
            <div class="main-content">
                <h1>Welcome, Admin!</h1>
                <p>This is your dashboard. Use the sidebar to navigate. (UI is ready for more features!)</p>
            </div>
        </div>
    </body>
</html>