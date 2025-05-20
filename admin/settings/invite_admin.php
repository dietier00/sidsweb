<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'owner') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../settings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Check if email already exists
    $check_sql = "SELECT id FROM admins WHERE email = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$email]);

    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already registered";
        header("Location: ../settings.php");
        exit();
    }

    // Generate temporary password
    $temp_password = bin2hex(random_bytes(8));
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

    // Create new admin user with default name
    $name = "New Admin";
    $phone = "";  // Empty by default

    $sql = "INSERT INTO admins (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$name, $email, $hashed_password, $phone, $role])) {
        // TODO: Implement email sending functionality
        // For now, we'll just show the credentials in the session
        $_SESSION['success'] = "Admin invited successfully. Temporary credentials:<br>" .
                             "Email: " . htmlspecialchars($email) . "<br>" .
                             "Password: " . htmlspecialchars($temp_password);
    } else {
        $_SESSION['error'] = "Error inviting admin";
    }
}

header("Location: ../settings.php");
exit();