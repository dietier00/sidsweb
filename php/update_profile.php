<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $facebook_id = trim($_POST['facebook_id'] ?? '');
    $viber_id = trim($_POST['viber_id'] ?? '');

    try {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email is already taken by another user";
            header('Location: ../users/profile.php');
            exit();
        }

        // Check if username is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username is already taken by another user";
            header('Location: ../users/profile.php');
            exit();
        }

        // Update user profile
        $stmt = $pdo->prepare("
            UPDATE customers 
            SET name = ?, username = ?, email = ?, 
                phone = ?, address = ?, facebook_id = ?, 
                viber_id = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $name, $username, $email, 
            $phone, $address, $facebook_id, 
            $viber_id, $user_id
        ]);

        $_SESSION['success'] = "Profile updated successfully";
    } catch (PDOException $e) {
        $_SESSION['error'] = "An error occurred while updating your profile";
        error_log("Profile update error: " . $e->getMessage());
    }

    header('Location: ../users/profile.php');
    exit();
} else {
    header('Location: ../users/profile.php');
    exit();
}
?>