<h1>Reset Password</h1>
<p>Enter your new password:</p>
<form action="{{ route('update-password', ['token' => $token]) }}" method="post">
    @csrf
    <input type="password" name="password" placeholder="New password">
    <button type="submit">Reset Password</button>
</form>