<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{url('css/app.css')}}">
</head>
<body>
<form action="{{ route('update-password', ['token' => $token]) }}" method="post">
    @csrf
    <h3>Reset Password</h3>
    <input type="password" placeholder="New password" name="password">
    @error('password')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
    <input type="password" placeholder="Confirm new password" name="password_confirmation">
    @error('password_confirmation')
        @if($message == 'The password confirmation must be at least 8 characters.')
            <div class="alert alert-danger">Passwords do not match.</div>
        @else
            <div class="alert alert-danger">{{ $message }}</div>
        @endif
    @enderror
    <button type="submit">Reset Password</button>
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