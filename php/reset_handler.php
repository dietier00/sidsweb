<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = trim($_POST['token']);
    $password = $_POST['password'];
    
    try {
        // First check customers table
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();

        if ($user) {
            // Hash new password and update customer
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE customers SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hashedPassword, $user['id']]);
            
            header("Location: ../users/login.php?status=reset_success");
            exit();
        }

        // If not found in customers, check admins table
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Hash new password and update admin
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE admins SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hashedPassword, $admin['id']]);
            
            header("Location: ../admin/index.php?status=reset_success");
            exit();
        }

        header("Location: ../reset-password.php?error=Invalid or expired reset link");
        exit();
    } catch(PDOException $e) {
        header("Location: ../reset-password.php?error=An error occurred");
        exit();
    }
} else {
    header("Location: ../login.php");
    exit();
}
?>