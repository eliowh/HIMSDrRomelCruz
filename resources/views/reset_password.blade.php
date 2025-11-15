<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <link rel="icon" type="image/png" href="{{ asset('img/hospital_logo.png') }}">
    <link rel="stylesheet" href="{{asset('css/forgotPass.css')}}">
</head>
<body>
<div class="container">
    <div class="left">
        <img src="{{ asset('img/hospital_logo.png')}}" alt="">
    </div>
    <div class="right">
        <div class="formbox">
            <form action="{{ secure_url('/reset-password/'.$token) }}" method="post">
                @csrf
                <h3 class="header">Reset Password</h3>
                <p style="margin-bottom: 30px; margin-top: 5px; color: #666; text-align: center; font-size: 0.9em;">
                Enter your new password and confirm it.
                </p>
                <h2 class="pass">New Password</h2>
                <div class="mb-3">
                    <input type="password" placeholder="New password" name="password" class="eField">
                </div>
                <h2 class="pass">Confirm Password</h2>
                <div class="mb-3">
                    <input type="password" placeholder="Confirm new password" name="password_confirmation" class="eField">
                </div>
                <button type="submit" class="submitBtn">Reset Password</button>
            </form>
        </div>
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