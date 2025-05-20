<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['admin_role'], ['owner', 'admin'])) {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../settings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input ranges
    $login_attempts = max(1, min(10, intval($_POST['login_attempts'])));
    $session_timeout = max(5, min(120, intval($_POST['session_timeout'])));
    $password_expiry = max(30, min(180, intval($_POST['password_expiry'])));

    // Prepare updates for security settings
    $updates = [
        'login_attempts' => ['value' => $login_attempts, 'type' => 'number'],
        'session_timeout' => ['value' => $session_timeout, 'type' => 'number'],
        'password_expiry' => ['value' => $password_expiry, 'type' => 'number']
    ];

    try {
        $pdo->beginTransaction();

        foreach ($updates as $key => $setting) {
            $sql = "UPDATE site_settings SET setting_value = ? WHERE setting_key = ? AND setting_type = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$setting['value'], $key, $setting['type']]);
        }

        $pdo->commit();
        $_SESSION['success'] = "Security settings updated successfully";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error updating security settings: " . $e->getMessage();
    }
}

header("Location: ../settings.php");
exit();