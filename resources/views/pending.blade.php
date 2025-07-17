<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending</title>
    <link rel="stylesheet" href="{{url('css/app.css')}}">
    <style>
        body { display: flex; align-items: center; justify-content: center; height: 100vh; background: #f8f9fa; }
        .pending-box { background: #fff; padding: 40px 60px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
        .pending-box h2 { color: #333; margin-bottom: 20px; }
        .pending-box p { color: #666; }
    </style>
</head>
<body>
    <div class="pending-box">
        <h2>Your account is pending approval</h2>
        <p>Your registration was successful, but your account has not yet been assigned a role by the admin.<br>
        Please wait for an administrator to review and approve your account.<br>
        You will be notified once your account is activated.</p>
        <a href="/logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="margin-top:20px; display:inline-block; color:#fff; background:#1a4931; padding:10px 30px; border-radius:5px; text-decoration:none;">Log Out</a>
        <form id="logout-form" action="/logout" method="POST" style="display:none;">@csrf</form>
    </div>
</body>
</html>
