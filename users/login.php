<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Skye Blinds Interior Design Services</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .brand-logo img {
            width: 120px;
            height: auto;
        }
        .form-control {
            border-radius: 5px;
            padding: 12px;
        }
        .btn-login {
            padding: 12px;
            border-radius: 5px;
            background-color: #0d6efd;
            border: none;
            width: 100%;
            margin-top: 20px;
        }
        .register-link {
            text-align: center;
            margin-top: 20px;
        }
        .password-container {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #6c757d;
            cursor: pointer;
        }
        .password-toggle:hover {
            color: #0d6efd;
        }
        .forgot-password {
            font-size: 0.9rem;
            text-decoration: none;
        }
        .forgot-password:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-logo">
            <img src="../favicon/favicon.ico" alt="Skye Logo">
            <h4 class="mt-3">Welcome Back</h4>
        </div>
        <form id="loginForm" method="POST" action="../php/login_handler.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-6">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>
                <div class="col-6 text-end">
                    <a href="forgot-password.php" class="forgot-password">Forgot Password?</a>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-login">Login</button>
        </form>
        <div class="register-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        $(document).ready(function() {
            // Check for remember me cookie
            const rememberedEmail = getCookie('remembered_email');
            if (rememberedEmail) {
                $('#email').val(rememberedEmail);
                $('#remember').prop('checked', true);
            }

            // Check for error messages in URL
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            if (error) {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "timeOut": "3000"
                };
                toastr.error(error === "invalid" ? "Invalid email or password" : error);
            }
        });

        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
        }
    </script>
</body>
</html>