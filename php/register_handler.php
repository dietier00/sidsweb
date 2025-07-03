<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $contactnum = trim($_POST['contactnum']);
    $facebook_id = $_POST['facebook_id'] ?? '';
    $viber_id = $_POST['viber_id'] ?? '';

    // Validate required fields
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        header('Location: ../users/register.php?error=' . urlencode('All required fields must be filled'));
        exit;
    }

    try {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            header("Location: ../users/register.php?error=" . urlencode("Email already exists"));
            exit();
        }

        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            header("Location: ../users/register.php?error=" . urlencode("Username already taken"));
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO customers (name, username, password, email, phone, facebook_id, viber_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$fullname, $username, $hashed_password, $email, $contactnum, $facebook_id, $viber_id]);

        // Redirect to login page with success message
        header("Location: ../users/login.php?success=1");
        exit();

    } catch(PDOException $e) {
        header("Location: ../users/register.php?error=" . urlencode("Registration failed. Please try again."));
        exit();
    }
} else {
    header("Location: ../users/register.php");
    exit();
}
?>