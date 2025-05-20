<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'owner') {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../settings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_id'])) {
    $admin_id = $_POST['admin_id'];

    // Verify not deleting owner account
    $check_sql = "SELECT role FROM admins WHERE id = ?";
    $check_stmt = $pdo->prepare($check_sql);
    $check_stmt->execute([$admin_id]);
    $current_role = $check_stmt->fetch(PDO::FETCH_ASSOC)['role'];

    if ($current_role === 'owner') {
        $_SESSION['error'] = "Cannot delete owner account";
        header("Location: ../settings.php");
        exit();
    }

    // Delete admin user
    $sql = "DELETE FROM admins WHERE id = ? AND role != 'owner'";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$admin_id])) {
        $_SESSION['success'] = "Admin user deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting admin user";
    }
}

header("Location: ../settings.php");
exit();