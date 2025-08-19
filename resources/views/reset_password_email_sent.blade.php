<h1>Password Reset Email Sent</h1>
<p>An email has been sent to your email address with a password reset link.</p>
<p>If you didn't receive the email, you can try resending it:</p>
<form action="{{ route('resend-email') }}" method="post">
    @csrf
    <button type="submit">Resend Email</button>
</form>