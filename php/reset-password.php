<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Skye Blinds</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
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
        .reset-container {
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
        .btn-reset {
            padding: 12px;
            border-radius: 5px;
            background-color: #0d6efd;
            border: none;
            width: 100%;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="brand-logo">
            <img src="favicon/favicon.ico" alt="Skye Logo">
            <h4 class="mt-3">Reset Password</h4>
            <p class="text-muted">Enter your new password below.</p>
        </div>
        <form id="resetForm" method="POST" action="php/reset_handler.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-reset">Reset Password</button>
        </form>
    </div>

    <script src="js/jquery.min.js"></script>
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
            $("#resetForm").on("submit", function(e) {
                const password = $("#password").val();
                const confirmPassword = $("#confirm_password").val();
                
                if (password !== confirmPassword) {
                    e.preventDefault();
                    toastr.error("Passwords do not match!");
                    return false;
                }
            });

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