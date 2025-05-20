<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if not logged in or cart is empty
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to proceed with checkout";
    header('Location: ../users/login.php');
    exit();
}

if (empty($_SESSION['cart'])) {
    $_SESSION['error'] = "Your cart is empty";
    header('Location: cart.php');
    exit();
}

// Get user's information
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Calculate cart total
$cartTotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transaction_started = false;
    try {
        // Validate required fields
        $required_fields = ['name', 'address', 'phone', 'payment_method'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                $missing_fields[] = ucfirst($field);
            }
        }
        
        if (!empty($missing_fields)) {
            throw new Exception("Please fill in all required fields: " . implode(", ", $missing_fields));
        }

        $name = trim($_POST['name']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $payment_method = trim($_POST['payment_method']);
        
        // Validate payment method
        if (!in_array($payment_method, ['cod', 'ewallet'])) {
            throw new Exception("Invalid payment method selected");
        }

        $pdo->beginTransaction();
        $transaction_started = true;

        // Generate unique order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . substr(uniqid(), -4);
        
        $payment_status = ($payment_method === 'cod') ? 'pending' : 'processing';

        // Create new order
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, user_id, total_amount, 
                shipping_address, billing_address, contact_number,
                payment_method, payment_status, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        $stmt->execute([
            $orderNumber,
            $_SESSION['user_id'],
            $cartTotal,
            $address,
            $address, // Using same address for billing
            $phone,
            $payment_method,
            $payment_status
        ]);

        $orderId = $pdo->lastInsertId();

        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, quantity, price, total_price
            ) VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($_SESSION['cart'] as $item) {
            // Check stock availability
            $stockStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND status = 'active'");
            $stockStmt->execute([$item['id']]);
            $product = $stockStmt->fetch();
            
            if (!$product || $product['stock'] < $item['quantity']) {
                throw new Exception("Sorry, some items in your cart are no longer available.");
            }

            // Insert order item
            $itemTotal = $item['price'] * $item['quantity'];
            $stmt->execute([
                $orderId,
                $item['id'],
                $item['quantity'],
                $item['price'],
                $itemTotal
            ]);

            // Update product stock
            $updateStock = $pdo->prepare("
                UPDATE products 
                SET stock = stock - ? 
                WHERE id = ?
            ");
            $updateStock->execute([$item['quantity'], $item['id']]);
        }

        $pdo->commit();
        $transaction_started = false;

        // Clear the cart
        $_SESSION['cart'] = [];
        
        // Redirect based on payment method
        if ($payment_method === 'cod') {
            $_SESSION['success'] = "Order placed successfully! Order number: " . $orderNumber;
            header('Location: orders.php');
            exit();
        } else {
            // Redirect to payment handling page
            header('Location: payment.php?order_id=' . $orderId);
            exit();
        }

    } catch (Exception $e) {
        if ($transaction_started) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <title>Checkout - Billing Information</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme.min.css">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 76px;
        }
        .billing-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .summary-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .payment-method-card {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method-card.selected {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }
        .payment-method-card:hover {
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" tabindex="0">
        <div class="container">
            <a class="navbar-brand pe-4 fs-4" href="../index.html">
                <img src="../favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
                <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
            </a>
            <!-- Rest of the navigation -->
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="billing-container">
                    <h2 class="mb-4">Billing Information</h2>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="billing.php" id="billingForm">
                        <div class="mb-4">
                            <h5 class="mb-3">Contact Information</h5>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="address" class="form-label">Delivery Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" 
                                              required><?= htmlspecialchars($user['address']) ?></textarea>
                                </div>
                                <div class="col-12">
                                    <label for="phone" class="form-label">Contact Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="mb-3">Payment Method</h5>
                            <div class="payment-method-card" onclick="selectPaymentMethod('cod')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="codPayment" value="cod" checked>
                                    <label class="form-check-label" for="codPayment">
                                        <i class="fas fa-money-bill-wave me-2"></i>
                                        Cash on Delivery
                                    </label>
                                </div>
                            </div>
                            <div class="payment-method-card" onclick="selectPaymentMethod('ewallet')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="ewalletPayment" value="ewallet">
                                    <label class="form-check-label" for="ewalletPayment">
                                        <i class="fas fa-wallet me-2"></i>
                                        E-Wallet
                                    </label>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="billing-container">
                    <h4 class="mb-4">Order Summary</h4>
                    <div class="summary-card">
                        <?php foreach ($_SESSION['cart'] as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                                <span>₱<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                            </div>
                        <?php endforeach; ?>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total</strong>
                            <strong>₱<?= number_format($cartTotal, 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectPaymentMethod(method) {
            document.querySelectorAll('.payment-method-card').forEach(card => {
                card.classList.remove('selected');
            });
            const selectedCard = document.querySelector(`#${method}Payment`).closest('.payment-method-card');
            selectedCard.classList.add('selected');
            document.querySelector(`#${method}Payment`).checked = true;
        }

        // Initialize selected payment method
        document.addEventListener('DOMContentLoaded', function() {
            selectPaymentMethod('cod');
        });
    </script>
</body>
</html>