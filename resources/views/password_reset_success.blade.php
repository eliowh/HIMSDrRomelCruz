<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{url('css/forgotPass.css')}}">
</head>
<body>
<div class="container">
    <div class="left">
        <img src="{{ asset('img/hospital_logo.png')}}" alt="">
    </div>
    <div class="right">
        <div class="formbox">
            <h3 class="header">Password Reset Successful</h3>
            <p style="margin-bottom: 30px; color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin: auto; text-align: center; width: 84%">
                Your password has been successfully updated. 
            </p>
            <p style="margin-bottom: 5px; margin-top: 15px; color: #666; text-align: center; font-size: 0.9em;">
                You can now login with your new password.
            </p>
            <a href="{{ route('login') }}" class="back-link">
                ← Login Now
            </a>
        </div>
    </div>  
</div>
</body>
</html>
