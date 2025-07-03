<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Skye Blinds</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
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
        .btn-register {
            padding: 12px;
            border-radius: 5px;
            background-color: #0d6efd;
            border: none;
            width: 100%;
            margin-top: 20px;
        }
        .login-link {
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
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand-logo">
            <img src="../favicon/favicon.ico" alt="Skye Logo">
            <h4 class="mt-3">Create an Account</h4>
        </div>
        <form id="registerForm" method="POST" action="../php/register_handler.php">
            <div class="mb-3">
                <label for="fullname" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="contactnum" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="contactnum" name="contactnum" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="form-group mb-3">
                <label for="phone">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number">
            </div>
            
            <div class="form-group mb-3">
                <label for="facebook">Facebook Profile (optional)</label>
                <input type="text" class="form-control" id="facebook" name="facebook_id" placeholder="Enter your Facebook profile URL or username">
            </div>
            
            <div class="form-group mb-3">
                <label for="viber">Viber Number (optional)</label>
                <input type="tel" class="form-control" id="viber" name="viber_id" placeholder="Enter your Viber number">
            </div>
            <button type="submit" class="btn btn-primary btn-register">Register</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
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
            // Form validation
            $("#registerForm").on("submit", function(e) {
                const password = $("#password").val();
                const confirmPassword = $("#confirm_password").val();
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    toastr.error("Passwords do not match!");
                    return false;
                }
            });

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
                toastr.error(error);
            }
        });
    </script>
</body>
</html>