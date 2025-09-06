<head>
   <link rel="stylesheet" href="{{ url('css/resetPass.css') }}">
</head>
<body>
    <div class="container">
            <h1>Password Reset Email Sent</h1>
            <p>An email has been sent to your email address with a password reset link.</p>
            <p>If you didn't receive the email, you can try resending it:</p>
    
            <form action="{{ route('resend-email') }}" method="post">
                @csrf
                <button type="submit" id="resend-button">Resend Email</button>
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
    </div>

                    @if (session('last_resend_time'))
                    <p id="countdown-message">Resend email available in <span id="countdown">10</span> seconds.</p>
                    <script>
                        var countdown = 10;
                        var lastResendTime = {{ session('last_resend_time') }};
                        var currentTime = new Date().getTime() / 1000;

                        if (currentTime - lastResendTime < 10) {
                            countdown = 10 - Math.floor(currentTime - lastResendTime);
                        }

                        var interval = setInterval(function() {
                            countdown--;
                            document.getElementById('countdown').innerHTML = countdown;
                            document.getElementById('resend-button').innerHTML = 'Resend Email (' + countdown + ')';

                            if (countdown <= 0) {
                                clearInterval(interval);
                                document.getElementById('countdown-message').style.display = 'none';
                                document.getElementById('resend-button').disabled = false;
                                document.getElementById('resend-button').innerHTML = 'Resend Email';
                            } else {
                                document.getElementById('resend-button').disabled = true;
                            }
                        }, 1000);
                    </script>
                @endif
            </form>
</body>