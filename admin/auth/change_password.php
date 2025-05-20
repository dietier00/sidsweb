<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id = $_SESSION['admin_id'];

    // Verify passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['error'] = "New passwords do not match";
        header("Location: ../profile.php");
        exit();
    }

    // Get current user's password
    $sql = "SELECT password FROM admins WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = "Current password is incorrect";
        header("Location: ../profile.php");
        exit();
    }

    // Update password
    $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE admins SET password = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$new_hashed_password, $user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = "Password updated successfully";
    } else {
        $_SESSION['error'] = "Error updating password";
    }
}

header("Location: ../profile.php");
exit();