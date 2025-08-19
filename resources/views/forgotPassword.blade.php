<form action="/forgot-password" method="POST">
    @csrf
    <h3>Forgot Password</h3>
    <input type="email" placeholder="Enter your email address" name="email">
    <button type="submit">Send Reset Link</button>
</form>