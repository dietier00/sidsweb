<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || !in_array($_SESSION['admin_role'], ['owner', 'admin'])) {
    $_SESSION['error'] = "Unauthorized access";
    header("Location: ../settings.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Prepare updates for general settings
    $updates = [
        'site_name' => ['value' => $_POST['site_name'], 'type' => 'text'],
        'site_email' => ['value' => $_POST['site_email'], 'type' => 'text'],
        'maintenance_mode' => ['value' => $_POST['maintenance_mode'], 'type' => 'boolean']
    ];

    try {
        $pdo->beginTransaction();

        foreach ($updates as $key => $setting) {
            $sql = "UPDATE site_settings SET setting_value = ? WHERE setting_key = ? AND setting_type = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$setting['value'], $key, $setting['type']]);
        }

        $pdo->commit();
        $_SESSION['success'] = "Site settings updated successfully";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error updating site settings: " . $e->getMessage();
    }
}

header("Location: ../settings.php");
exit();