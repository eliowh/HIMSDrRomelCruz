<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Fallback for when Vite build is not available -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" media="print" onload="this.media='all'">
    <title>Login | Romel Cruz Hospital</title>
</head>
<body>
    <div id="errorPopup" class="popup">
        <div class="popup-content">
            <h4>Error</h4>
            <ul id="popupErrorList">
                <!-- Error messages will be injected here -->
            </ul>
            <button onclick="closePopup()">Close</button>
        </div>
    </div>
    <script>
        function closePopup() {
            document.getElementById('errorPopup').style.display = 'none';
        }
        window.onload = function() {
            var errors = @json($errors->all());
            if(errors.length > 0) {
                var ul = document.getElementById('popupErrorList');
                ul.innerHTML = '';
                errors.forEach(function(err) {
                    var li = document.createElement('li');
                    li.textContent = err;
                    ul.appendChild(li);
                });
                document.getElementById('errorPopup').style.display = 'flex';
            }
        }
    </script>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="container">
        <div class="left">
            <div class="formbox">
                <h3 class="main-title">ROMEL CRUZ HOSPITAL</h3>
                <h4>Sign in to your hospital account</h4>
                <p class="welcome">Please enter your details</p>
                <form action="/login" method="POST" autocomplete="off">
                    @csrf
                    <div style="text-align:left; margin-bottom: 18px;">
                        <label for="loginemail" style="color:#1a4931; font-weight:600; font-size:1rem; margin-bottom:4px; display:block;">
                            Email
                        </label>
                        <input 
                            type="text" 
                            id="loginemail"
                            name="loginemail" 
                            placeholder="Email Address" 
                            value="{{ old('loginemail') }}" 
                            required 
                            autofocus
                        >
                    </div>
                    <div style="text-align:left; margin-bottom: 10px;">
                        <label for="loginpassword" style="color:#1a4931; font-weight:600; font-size:1rem; margin-bottom:4px; display:block;">
                            Password
                        </label>
                        <input 
                            type="password" 
                            id="loginpassword"
                            name="loginpassword" 
                            placeholder="Password" 
                            required
                        >
                    </div>
                    <button type="submit">Log In</button>
                </form>
                <div class="social-login">
                    <p>
                        <a href="{{ url('/forgot-password') }}">Forgot Password?</a>
                    </p>
                </div>
                <p style="margin-top: 15px; font-size: 0.9em; color: #555; font-style: italic;">
                    Need an account? Please contact the hospital administrator
                </p>
            </div>
        </div>
        <div class="right">
            <img src="{{ asset('img/hospital_logo.png') }}" alt="Hospital Logo" class="hospital-logo">
        </div>
    </div>
</body>
</html>