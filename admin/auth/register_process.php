<?php
session_start();
require_once '../../php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = $_POST['contact'];
    $role = $_POST['role'];
    
    // Validate passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords do not match";
        header("Location: ../register.php");
        exit();
    }
    
    // Check if email already exists
    $check_sql = "SELECT * FROM admins WHERE email = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$email]);
    
    if ($check_stmt->rowCount() > 0) {
        $_SESSION['error'] = "Email already exists";
        header("Location: ../register.php");
        exit();
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new admin
    $sql = "INSERT INTO admins (name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$name, $email, $hashed_password, $phone, $role])) {
        $_SESSION['success'] = "Registration successful. Please login.";
        header("Location: ../index.php");
        exit();
    } else {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: ../register.php");
        exit();
    }
}

header("Location: ../register.php");
exit();