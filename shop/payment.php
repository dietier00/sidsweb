<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if not logged in or no order ID provided
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['order_id']);

// Get order details, allow both 'pending' and 'processing' payment_status
$stmt = $pdo->prepare("
    SELECT o.*, c.name, c.email 
    FROM orders o 
    JOIN customers c ON o.user_id = c.id 
    WHERE o.id = ? AND o.user_id = ? 
    AND (o.payment_status = 'pending' OR o.payment_status = 'processing')
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Order not found or already paid";
    header('Location: orders.php');
    exit();
}

// Store order ID in session for payment gateway
$_SESSION['order_id'] = $order_id;

// Redirect to payment gateway
header('Location: payment_gateway.php');
exit();
?>