<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/forgotPass.css')); ?>"
</head>
<body>
<div class="container">
    <div class="left">
        <img src="<?php echo e(asset('img/hospital_logo.png')); ?>" alt="">
    </div>
    <div class="right">
        <div class="formbox">
            <h3 class="header">Email Sent</h3>
            <p style="marginc-bottom: 5px; margin-top: 5px; color: #333; text-align: center;">
                An email has been sent to your email address with a password reset link.
            </p>
            <p style="margin-bottom: 5px; margin-top: 5px; color: #666; text-align: center; font-size: 0.9em;">
                If you didn't receive the email, you can try resending it:
            </p>

            <form action="<?php echo e(route('resend-email')); ?>" method="post" id="resend-form">
                <?php echo csrf_field(); ?>
                <button type="submit" class="submitBtn" id="resend-button">Resend Email</button>
                <?php if(session('resend_success')): ?>
                    <div style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px; margin: auto; margin-top: 10px; text-align: center; width: 54%">
                        <?php echo e(session('resend_success')); ?>

                    </div>
                <?php endif; ?>
            </form>
            <br><br>
            <a href="/login" class="back-link">
                ← Back to Login
            </a>
        </div>
    </div>  
</div>

                    <?php if(session('last_resend_time')): ?>
                    <script>
                        (function() {
                            var countdown = 10;
                            var lastResendTime = <?php echo e(session('last_resend_time')); ?>;
                            var currentTime = new Date().getTime() / 1000;

                            if (currentTime - lastResendTime < 10) {
                                countdown = 10 - Math.floor(currentTime - lastResendTime);
                            }

                            var btn = document.getElementById('resend-button');

                            function startCountdown() {
                                btn.disabled = true;
                                btn.classList.add('disabled');
                                btn.textContent = 'Resend Email (' + countdown + ')';

                                var interval = setInterval(function() {
                                    countdown--;
                                    if (countdown <= 0) {
                                        clearInterval(interval);
                                        btn.disabled = false;
                                        btn.classList.remove('disabled');
                                        btn.textContent = 'Resend Email';
                                    } else {
                                        btn.textContent = 'Resend Email (' + countdown + ')';
                                    }
                                }, 1000);
                            }

                            // Initialize immediately if needed
                            if (countdown > 0) {
                                startCountdown();
                            }
                        })();
                    </script>
                <?php endif; ?>
</body><?php /**PATH D:\xampp\htdocs\DrRomelCruzHP\resources\views\reset_password_email_sent.blade.php ENDPATH**/ ?>