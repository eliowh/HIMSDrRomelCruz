<!-- filepath: d:\xampp\htdocs\DrRomelCruzHP\resources\views\home.blade.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Welcome | Dr. Romel Cruz Hospital</title>
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }
        .welcome-box {
            background: #fff;
            padding: 40px 60px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .welcome-box h1 {
            margin-bottom: 20px;
            color: #1a4931;
        }
        .welcome-box p {
            color: #555;
            margin-bottom: 30px;
        }
        .welcome-box a {
            display: inline-block;
            margin: 0 10px;
            padding: 12px 32px;
            background: #1a4931;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.2s;
        }
        .welcome-box a:hover {
            background: #1d7033;
        }
    </style>
</head>
<body>
    <div class="welcome-box">
        <h1>Welcome!</h1>
        <p>Welcome to Dr. Romel Cruz Hospital's portal.<br>
        Please log in or create an account to continue.</p>
        <a href="{{ url('/login') }}">Log In</a>
        <a href="{{ url('/register') }}">Create an Account</a>
    </div>
</body>
</html>