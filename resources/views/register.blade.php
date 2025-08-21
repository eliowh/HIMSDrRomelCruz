
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="{{url('css/app.css')}}">
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
                <form action="/register" method="POST">
                    @csrf
                    <h3>Dr. Romel Cruz Hospital</h3>
                    <h4>Create an account</h4>
                    <h3 class="welcome2">Welcome! Please enter your details.</h3>
                    <h2 class="name">Name*</h2>
                    <input type="text" placeholder = "Enter your Name" name = "name" value="{{ old('name') }}">
                    <h2 class="mail">Email*</h2>
                    <input type="text" placeholder = "Enter your Email" name = "email" value="{{ old('email') }}">
                    <h2 class="pass">Password*</h2>
                    <input type="password" placeholder = "Enter your Password" name = "password"><br>
                    <h2 class="rem">Must be atleast 8 characters</h2>
                    <button type="submit">Register</button> 
                    <div class="social-login">
                        <p>Already have an account?</p>
                        <a href="{{ url('/login') }}" style="color: #000; font-weight: bold;">Login</a>
                    </div>
                </form>
           </div>
        </div>
        <div class="right">
            <img src="{{ asset('img/logPic.png')}}" alt="">
        </div>
    </div>
</body>
</html>