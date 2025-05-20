<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Get user information
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// If user not found, redirect to login
if (!$user) {
    session_destroy();
    header('Location: ../login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme.min.css">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 76px;
        }
        .profile-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
            background: #DDA853;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .edit-btn {
            background-color: #DDA853;
            border: none;
            padding: 8px 20px;
            transition: all 0.3s;
        }
        .edit-btn:hover {
            background-color: #27548A;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" tabindex="0">
        <div class="container">
            <a class="navbar-brand pe-4 fs-4" href="../index.html">
                <img src="../favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
                <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../shop/cart.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../php/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h2 class="mt-3"><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="text-muted mb-0">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="info-label mb-1">Username</p>
                                <p class="info-value"><?php echo htmlspecialchars($user['username']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="info-label mb-1">Email</p>
                                <p class="info-value"><?php echo htmlspecialchars($user['email']); ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="info-label mb-1">Contact Number</p>
                                <p class="info-value"><?php echo htmlspecialchars($user['phone']); ?></p>
                            </div>
                        </div>
                        <?php if ($user['address']): ?>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <p class="info-label mb-1">Address</p>
                                <p class="info-value"><?php echo htmlspecialchars($user['address']); ?></p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="text-center mt-4">
                        <a href="#" class="btn btn-primary edit-btn">Edit Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../php/update_profile.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit_username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_phone" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="edit_phone" name="phone" 
                                   value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize edit profile button
        document.querySelector('.edit-btn').addEventListener('click', function() {
            var editProfileModal = new bootstrap.Modal(document.getElementById('editProfileModal'));
            editProfileModal.show();
        });
    </script>
</body>
</html>