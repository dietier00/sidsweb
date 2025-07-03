<?php
session_start();
require_once '../php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../users/login.php');
    exit();
}

// Check if there are items to checkout
if (!isset($_SESSION['checkout_items']) || empty($_SESSION['checkout_items'])) {
    header('Location: cart.php');
    exit();
}

$userId = $_SESSION['user_id'];
$cartIds = $_SESSION['checkout_items'];

// Fetch selected cart items
$placeholders = str_repeat('?,', count($cartIds) - 1) . '?';
$params = array_merge([$userId], $cartIds);
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price, p.images, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ? AND c.id IN ($placeholders)
");
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$shipping = 100; // Fixed shipping fee
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal + $shipping;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Create order
        $orderNumber = 'ORD' . date('YmdHis') . rand(100, 999);
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                user_id, order_number, total_amount, payment_method,
                shipping_address, shipping_fee
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId,
            $orderNumber,
            $total,
            $_POST['payment_method'],
            json_encode($_POST['shipping']), // Store shipping details as JSON
            $shipping
        ]);
        $orderId = $pdo->lastInsertId();

        // Create order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        foreach ($items as $item) {
            $itemSubtotal = $item['price'] * $item['quantity'];
            $stmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $itemSubtotal
            ]);

            // Update product stock
            $updateStock = $pdo->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ? AND stock >= ?
            ");
            $updateStock->execute([$item['quantity'], $item['product_id'], $item['quantity']]);
        }

        // Remove items from cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE id IN ($placeholders)");
        $stmt->execute($cartIds);

        $pdo->commit();

        // Store order ID in session for payment processing
        $_SESSION['pending_order_id'] = $orderId;
        
        // Redirect based on payment method
        switch ($_POST['payment_method']) {
            case 'gcash':
            case 'maya':
                header("Location: process_payment.php?method={$_POST['payment_method']}");
                break;
            case 'cod':
                header('Location: order_confirmed.php?order_id=' . $orderId);
                break;
        }
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "An error occurred while processing your order. Please try again.";
        error_log($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Skye Blinds</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/theme.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .payment-method-option {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .payment-method-option:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-method-option.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-logo {
            height: 40px;
            width: auto;
        }
    </style>
</head>
<body>
    <?php include '../includes/navigation.php'; ?>

    <main class="container py-5" style="margin-top: 60px;">
        <h2 class="mb-4">Checkout</h2>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="checkoutForm">
            <div class="row">
                <div class="col-md-8">
                    <!-- Shipping Information -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Shipping Information</h4>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="shipping[first_name]" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="shipping[last_name]" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address</label>
                                    <input type="text" name="shipping[address]" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City</label>
                                    <input type="text" name="shipping[city]" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Region</label>
                                    <input type="text" name="shipping[region]" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Postal Code</label>
                                    <input type="text" name="shipping[postal_code]" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="shipping[phone]" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="shipping[email]" class="form-control" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Payment Method</h4>
                            
                            <div class="payment-method-option" data-method="gcash">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="gcash" required>
                                    <label class="form-check-label d-flex align-items-center">
                                        <span class="me-3">GCash</span>
                                        <img src="../images/payment/gcash.png" alt="GCash" class="payment-logo">
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method-option" data-method="maya">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="maya" required>
                                    <label class="form-check-label d-flex align-items-center">
                                        <span class="me-3">Maya</span>
                                        <img src="../images/payment/maya.png" alt="Maya" class="payment-logo">
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method-option" data-method="cod">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" value="cod" required>
                                    <label class="form-check-label">
                                        Cash on Delivery
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Order Summary</h4>
                            <?php foreach ($items as $item): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <h6 class="mb-0"><?= htmlspecialchars($item['name']) ?></h6>
                                        <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                    </div>
                                    <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                                </div>
                            <?php endforeach; ?>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal</span>
                                <span>₱<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping Fee</span>
                                <span>₱<?= number_format($shipping, 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total</span>
                                <span class="fw-bold">₱<?= number_format($total, 2) ?></span>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Place Order
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle payment method selection
            const paymentOptions = document.querySelectorAll('.payment-method-option');
            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    // Check the radio input
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                });
            });

            // Form validation
            const form = document.getElementById('checkoutForm');
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    </script>
</body>
</html>