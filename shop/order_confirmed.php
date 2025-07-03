<?php
session_start();
require_once '../php/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

$orderId = $_GET['order_id'] ?? null;
$userId = $_SESSION['user_id'];
$order = null;

if ($orderId) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed - Skye Blinds</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/theme.min.css">
</head>
<body>
    <main class="container py-5" style="margin-top: 60px;">
        <div class="text-center">
            <h2 class="mb-4">Thank you for your order!</h2>
            <?php if ($order): ?>
                <div class="alert alert-success">
                    <strong>Order #<?php echo htmlspecialchars($order['id']); ?></strong> has been placed successfully.<br>
                    Payment Method: <strong><?php echo strtoupper(htmlspecialchars($order['payment_method'])); ?></strong><br>
                    Payment Status: <strong><?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></strong>
                </div>
                <a href="orders.php" class="btn btn-primary">View My Orders</a>
                <a href="products.php" class="btn btn-outline-secondary ms-2">Continue Shopping</a>
            <?php else: ?>
                <div class="alert alert-danger">Order not found.</div>
                <a href="products.php" class="btn btn-primary">Shop Now</a>
            <?php endif; ?>
        </div>
    </main>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
