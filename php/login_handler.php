<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    try {
        // Get user by email
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['fullname'] = $user['name'];
            
            // Handle remember me
            if ($remember) {
                // Set cookie for 30 days
                setcookie('remembered_email', $email, time() + (30 * 24 * 60 * 60), '/');
            } else {
                // Remove cookie if exists
                setcookie('remembered_email', '', time() - 3600, '/');
            }
            
            // Redirect to index page
            header("Location: ../index.html");
            exit();
        } else {
            // Invalid credentials
            header("Location: ../users/login.php?error=invalid");
            exit();
        }
    } catch(PDOException $e) {
        header("Location: ../users/login.php?error=" . urlencode("Login failed. Please try again."));
        exit();
    }
} else {
    header("Location: ../users/login.php");
    exit();
}
?>