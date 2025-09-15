<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{ url('css/forgotPass.css') }}">
    <title>Forgot Password | Dr. Romel Cruz Hospital</title>
</head>
<body>
    <div class="container">
        <div class="left">
            <div class="formbox">
                <h3 class="forgot-title">Forgot Password</h3>
                <p class="forgot-desc">
                    Enter your email address to receive a password reset link.
                </p>
                <form action="/forgot-password" method="POST">
                    @csrf
                    <h2 class="mail">Email</h2>
                    <div class="mb-3">
                        <input type="email" placeholder="Enter your email address" name="email" class="eField" required>
                    </div>
                    <button type="submit" class="submitBtn">Send Reset Link</button>
                    <a href="{{ url('/login') }}" class="back-link">
                        ← Back to Login
                    </a>
                </form>
            </div>
        </div>
        <div class="right">
            <img src="{{ asset('img/hospital_logo.png') }}" alt="Hospital Logo" class="hospital-logo">
        </div>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger" style="position: fixed; bottom: 0; width: 100%; text-align: center;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>
</html>