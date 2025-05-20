<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['contact'];
    $user_id = $_SESSION['admin_id'];

    // Check if email is already taken by another user
    $check_sql = "SELECT id FROM admins WHERE email = ? AND id != ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$email, $user_id]);

    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email is already taken by another user";
        header("Location: ../profile.php");
        exit();
    }

    // Update profile
    $sql = "UPDATE admins SET name = ?, email = ?, phone = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$name, $email, $phone, $user_id])) {
        $_SESSION['admin_name'] = $name;
        $_SESSION['admin_email'] = $email;
        $_SESSION['success'] = "Profile updated successfully";
    } else {
        $_SESSION['error'] = "Error updating profile";
    }
}

header("Location: ../profile.php");
exit();