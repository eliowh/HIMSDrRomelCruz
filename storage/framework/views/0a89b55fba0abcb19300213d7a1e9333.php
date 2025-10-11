<!-- filepath: d:\xampp\htdocs\DrRomelCruzHP\resources\views\home.blade.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="<?php echo e(asset('img/hospital_logo.png')); ?>">
    <title>Welcome | Romel Cruz Hospital</title>
    <!-- Add Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Add Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            min-width: 100vw;
            font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
            overflow-x: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: none;
        }
        body::before {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: url('img/romelcruz1.JPG') no-repeat center center/cover;
            z-index: -2;
        }
        body::after {
            content: "";
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(20, 40, 40, 0.55);
            z-index: -1;
        }
        .welcome-box {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(8px) saturate(120%);
            border-radius: 22px;
            box-shadow: 0 12px 40px rgba(30,60,60,0.12);
            padding: 56px 44px 44px 44px;
            text-align: center;
            min-width: 340px;
            max-width: 400px;
            margin: 0 16px;
            border: 1.5px solid rgba(200,200,200,0.18);
            position: relative;
            z-index: 1;
            animation: fadeInUp 0.8s cubic-bezier(.23,1.02,.58,.99);
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .welcome-box h1 {
            margin-bottom: 24px;
            font-size: 2.3rem;
            font-weight: 900;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #24571d;
            text-shadow: 0 2px 8px rgba(54,127,43,0.08);
            position: relative;
            padding-bottom: 8px;
        }
        .welcome-box h1::after {
            display: none;
        }
        .welcome-box p {
            color: #24571d;
            margin-bottom: 40px;
            font-size: 1.08em;
            font-style: italic;
            background: rgba(200,255,200,0.13);
            padding: 12px 0;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }
        .welcome-box a {
            display: inline-block;
            margin: 0 12px;
            padding: 14px 36px;
            background: #367F2B;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.08em;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px 0 rgba(45, 108, 223, 0.10);
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
            border: none;
            outline: none;
        }
        .welcome-box a:hover, .welcome-box a:focus {
            background: #24571d;
            transform: scale(1.04);
            box-shadow: 0 4px 16px 0 rgba(45, 108, 223, 0.15);
            color: #fff;
            text-decoration: none;
        }
        .button-row {
            display: flex;
            flex-direction: column;
            gap: 18px;
            align-items: center;
            margin-top: 10px;
        }
        .button-row a {
            margin: 0;
        }
        @media (max-width: 600px) {
            .welcome-box {
                padding: 28px 8px 28px 8px;
                min-width: unset;
                max-width: 98vw;
            }
            .welcome-box h1 {
                font-size: 1.4rem;
                padding-bottom: 6px;
            }
            .welcome-box p {
                margin-bottom: 28px;
                padding: 8px 0;
            }
            .welcome-box a {
                margin: 8px 4px;
                padding: 12px 18px;
                font-size: 1em;
            }
            .button-row {
                flex-direction: column;
                gap: 12px;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <div class="welcome-box">
        <h1>Welcome!</h1>
        <p>Welcome to Dr. Romel Cruz Hospital's portal.<br>
        Please log in to continue.</p>
        <a href="<?php echo e(asset('/login')); ?>">Log In</a>

    </div>
</body>
</html><?php /**PATH D:\xamppLatest\htdocs\HIMSDrRomelCruz\resources\views/home.blade.php ENDPATH**/ ?>