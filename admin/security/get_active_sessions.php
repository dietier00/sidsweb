<?php
session_start();
require_once('../../php/db_connect.php');

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

try {
    // Get active sessions within the last 15 minutes
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT session_id) as active_count 
        FROM user_activity_logs 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ");
    $stmt->execute();
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'count' => $result['active_count']
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch active sessions'
    ]);
}