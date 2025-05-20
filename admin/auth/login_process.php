<?php
session_start();
require_once '../../php/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['username']; // Using email for login instead of username
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM admins WHERE email = ? AND role IN ('owner', 'admin', 'agent')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user['password'])) {
            // Update last login time if the column exists
            try {
                $update_sql = "UPDATE admins SET last_login = CURRENT_TIMESTAMP WHERE id = ?";
                $update_stmt = $pdo->prepare($update_sql);
                $update_stmt->execute([$user['id']]);
            } catch (PDOException $e) {
                // Ignore if last_login column doesn't exist
            }
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_email'] = $user['email'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_name'] = $user['name'];
            
            header("Location: ../dashboard.php");
            exit();
        }
    }
    
    $_SESSION['error'] = "Invalid email or password";
    header("Location: ../index.php");
    exit();
}

header("Location: ../index.php");
exit();