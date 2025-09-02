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
        <img src="{{ asset('img/romelcruz1.jpg')}}" alt="">
    </div>
    <div class="right">
        <div class="formbox">
            <form action="/forgot-password" method="POST">
                @csrf
                <h3 class="header">Forgot Password</h3>
                <div class="mb-3">
                    <input type="email" placeholder="Enter your email address" name="email" class="eField">
                </div>
                <button type="submit" class="submitBtn">Send Reset Link</button>
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