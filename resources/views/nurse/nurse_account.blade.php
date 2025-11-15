<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nurse Account Settings</title>
    <link rel="stylesheet" href="{{ asset('css/nursecss/nurse.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    @php
        $nurseName = auth()->user()->name ?? 'Nurse';
    @endphp
    @include('nurse.nurse_header')
    <div class="nurse-layout">
        @include('nurse.nurse_sidebar')
        <main class="main-content">
            <div class="nurse-card">
                <h2>Account Settings</h2>
                <p>Manage your account information and preferences.</p>
            </div>
            
            <div class="nurse-card">
                <h3>Profile Information</h3>
                <div class="profile-info">
                    <p><strong>Name:</strong> {{ Auth::user()->name }}</p>
                    <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
                    <p><strong>Role:</strong> Nurse</p>
                </div>
            </div>

            <div class="nurse-card">
                <h3>Change Password</h3>
                <!-- Trigger sending reset password email to the user's email -->
                @if(session('status_error'))
                    <div class="alert alert-danger">{{ session('status_error') }}</div>
                @endif
                <form method="POST" action="{{ route('account.sendResetEmail') }}">
                    @csrf
                    <p>Click the button below to send a password reset email to your account email address.</p>
                    <!-- Use existing site action button styles to match other buttons -->
                    <button type="submit" class="action-btn primary">Reset password</button>
                </form>
            </div>
        </main>
    </div>
    @include('nurse.modals.notification_system')
</body>
</html>
