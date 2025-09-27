<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Settings</title>
    <link rel="stylesheet" href="{{ url('css/admincss/admin.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @php
        $adminName = auth()->user()->name ?? 'Admin';
    @endphp
    @include('admin.admin_header')
    <div class="admin-layout">
        @include('admin.admin_sidebar')
        <main class="main-content">
            <div class="admin-card">
                <h2>Account Settings</h2>
                <p>Manage your account information and preferences.</p>
            </div>
            
            <div class="admin-card">
                <h3>Profile Information</h3>
                <div class="profile-info">
                    <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p><strong>Role:</strong> Administrator</p>
                </div>
            </div>

            <div class="admin-card">
                <h3>Change Password</h3>
                <!-- Placeholder for password change form -->
                <div class="placeholder-content">
                    <p>Password change functionality will be implemented soon.</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html>