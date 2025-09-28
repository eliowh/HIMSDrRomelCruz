<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout Successful - Dr. Romel Cruz HIMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(90deg, #367F2B 40%, #1a4931 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }

        .logout-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
            text-align: center;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        .logout-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 40px 30px;
        }

        .logout-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: checkmark 0.8s ease-in-out 0.3s both;
        }

        .logout-header h1 {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .logout-header p {
            font-size: 16px;
            opacity: 0.9;
        }

        .logout-body {
            padding: 40px 30px;
        }

        .logout-message {
            font-size: 18px;
            color: #495057;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .logout-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 30px;
            border-left: 4px solid #17a2b8;
        }

        .logout-info h3 {
            color: #17a2b8;
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .logout-info p {
            font-size: 14px;
            color: #6c757d;
            line-height: 1.5;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-width: 150px;
            justify-content: center;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 123, 255, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(108, 117, 125, 0.3);
        }

        /* Animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes checkmark {
            0% {
                opacity: 0;
                transform: scale(0.3) rotate(-45deg);
            }
            50% {
                opacity: 1;
                transform: scale(1.1) rotate(-45deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        /* Auto-redirect countdown */
        .countdown {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            color: #856404;
        }

        .countdown-timer {
            font-weight: bold;
            color: #d39e00;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .logout-container {
                margin: 20px;
                width: calc(100% - 40px);
            }
            
            .logout-header {
                padding: 30px 20px;
            }
            
            .logout-body {
                padding: 30px 20px;
            }
            
            .logout-icon {
                font-size: 60px;
            }
            
            .logout-header h1 {
                font-size: 24px;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-header">
            <div class="logout-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Logout Successful</h1>
            <p>You have been securely logged out</p>
        </div>
        
        <div class="logout-body">
            <div class="logout-message">
                Thank you for using Dr. Romel Cruz Hospital Information Management System. Your session has been terminated securely.
            </div>
            
            <div class="logout-info">
                <h3>
                    <i class="fas fa-shield-alt"></i>
                    Security Notice
                </h3>
                <p>
                    For your security, all session data has been cleared. If you're using a shared computer, 
                    make sure to close your browser completely.
                </p>
            </div>
            
            <div class="action-buttons">
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i>
                    Login Again
                </a>
                <a href="{{ url('/') }}" class="btn btn-secondary">
                    <i class="fas fa-home"></i>
                    Home Page
                </a>
            </div>
            
            <div class="countdown">
                <i class="fas fa-clock"></i>
                You will be automatically redirected to the login page in 
                <span class="countdown-timer" id="countdown">10</span> seconds.
            </div>
        </div>
    </div>

    <script>
        // Auto-redirect countdown
        let countdown = 10;
        const countdownElement = document.getElementById('countdown');
        
        const countdownTimer = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;
            
            if (countdown <= 0) {
                clearInterval(countdownTimer);
                window.location.href = '{{ route("login") }}';
            }
        }, 1000);
        
        // Clear any remaining data
        localStorage.clear();
        sessionStorage.clear();
        
        // Prevent back button from accessing authenticated pages
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            history.go(1);
        };
    </script>
</body>
</html>