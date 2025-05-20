<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $user_id = $_SESSION['user_id'];

    try {
        // Check if email is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email is already taken by another user";
            header("Location: ../users/profile.php");
            exit();
        }

        // Check if username is already taken by another user
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE username = ? AND id != ?");
        $stmt->execute([$username, $user_id]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Username is already taken by another user";
            header("Location: ../users/profile.php");
            exit();
        }

        // Update profile
        $stmt = $pdo->prepare("UPDATE customers SET name = ?, username = ?, email = ?, phone = ?, address = ? WHERE id = ?");
        if ($stmt->execute([$name, $username, $email, $phone, $address, $user_id])) {
            $_SESSION['success'] = "Profile updated successfully";
        } else {
            $_SESSION['error'] = "Error updating profile";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "An error occurred while updating your profile";
    }
}

header("Location: ../users/profile.php");
exit();
?>