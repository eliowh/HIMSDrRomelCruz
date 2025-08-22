<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{url('css/app.css')}}">
</head>
<body>
<form action="/forgot-password" method="POST">
    @csrf
    <h3>Forgot Password</h3>
    <input type="email" placeholder="Enter your email address" name="email">
    <button type="submit">Send Reset Link</button>
</form>
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