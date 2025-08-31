<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management</title>
    <link rel="stylesheet" href="{{url('css/admin.css')}}">
</head>
<body>
    @php
        $adminName = auth()->user()->name ?? 'Admin';
    @endphp
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <div class="main-content">
            <div class="admin-card">
                <h2>Users Management</h2>
                <p>Manage user accounts, edit details, and handle account deletions.</p>
                <!-- Add your users management content here -->
            </div>
        </div>
    </div>
</body>
</html>