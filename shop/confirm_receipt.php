<?php
session_start();
require_once('../php/db_connect.php');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['user_id']) || !isset($data['order_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

try {
    // Verify that the order belongs to the user
    $stmt = $pdo->prepare("
        SELECT id, status FROM orders 
        WHERE id = ? AND user_id = ?
        AND status = 'delivered'
    ");
    $stmt->execute([$data['order_id'], $_SESSION['user_id']]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Order not found or cannot be confirmed at this time');
    }

    // Update order status to completed
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'completed',
            updated_at = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $stmt->execute([$data['order_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>