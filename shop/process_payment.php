<?php
session_start();
require_once '../php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $orderId = $_POST['order_id'] ?? null;
    $paymentMethod = $_POST['payment_method'] ?? '';
    $paymentStatus = 'pending';
    $redirectUrl = 'order_confirmed.php?order_id=' . urlencode($orderId);

    if ($orderId && $paymentMethod) {
        // Example: If GCash, mark as paid; if COD, mark as pending
        if ($paymentMethod === 'gcash') {
            $paymentStatus = 'paid';
        } elseif ($paymentMethod === 'cod') {
            $paymentStatus = 'pending';
        }
        // Update order payment status
        $stmt = $pdo->prepare("UPDATE orders SET payment_method = ?, payment_status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$paymentMethod, $paymentStatus, $orderId, $userId]);
        // Optionally, log payment or send notification here
        header('Location: ' . $redirectUrl);
        exit();
    } else {
        // Invalid request
        header('Location: checkout.php');
        exit();
    }
} else {
    header('Location: checkout.php');
    exit();
}
