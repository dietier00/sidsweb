<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
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
        .forgot-container {
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
        .btn-reset {
            padding: 12px;
            border-radius: 5px;
            background-color: #0d6efd;
            border: none;
            width: 100%;
            margin-top: 20px;
        }
        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="brand-logo">
            <img src="favicon/favicon.ico" alt="Skye Logo">
            <h4 class="mt-3">Forgot Password</h4>
            <p class="text-muted">Enter your email address and we'll send you a link to reset your password.</p>
        </div>
        <form id="forgotForm" method="POST" action="php/forgot_handler.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <button type="submit" class="btn btn-primary btn-reset">Send Reset Link</button>
        </form>
        <div class="back-to-login">
            <p>Remember your password? <a href="login.php">Back to Login</a></p>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const error = urlParams.get('error');

            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "5000"
            };

            if (status === 'sent') {
                toastr.success("Password reset link has been sent to your email!");
            } else if (error) {
                toastr.error(error);
            }
        });
    </script>
</body>
</html>