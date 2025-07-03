<?php
session_start();
require_once('../../php/db_connect.php');
require_once('../../php/security/security_handler.php');

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$ip = $data['ip'] ?? null;

if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) {
    echo json_encode(['success' => false, 'message' => 'Invalid IP address']);
    exit();
}

try {
    $security = new SecurityHandler($pdo);
    
    // Add IP to blocked list
    $stmt = $pdo->prepare("
        INSERT INTO blocked_ips (ip_address, blocked_by, reason)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$ip, $_SESSION['admin_id'], 'Multiple failed login attempts']);

    // Log the action
    $security->logSecurityEvent(
        'ip_blocked',
        "IP address $ip blocked due to suspicious activity",
        null,
        $_SESSION['admin_id']
    );

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to block IP']);
}
?>