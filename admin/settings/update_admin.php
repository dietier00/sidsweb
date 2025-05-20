<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'owner') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../settings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin_id = $_POST['admin_id'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Verify not updating owner account
    $check_sql = "SELECT role FROM admins WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$admin_id]);
    $current_role = $check_stmt->fetch(PDO::FETCH_ASSOC)['role'];

    if ($current_role === 'owner') {
        $_SESSION['error'] = "Cannot modify owner account";
        header("Location: ../settings.php");
        exit();
    }

    // Update admin user
    $sql = "UPDATE admins SET role = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$role, $admin_id])) {
        $_SESSION['success'] = "Admin user updated successfully";
    } else {
        $_SESSION['error'] = "Error updating admin user";
    }
}

header("Location: ../settings.php");
exit();