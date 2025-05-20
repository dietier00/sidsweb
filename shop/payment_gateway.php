<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if no order ID or not logged in
if (!isset($_SESSION['order_id']) || !isset($_SESSION['user_id'])) {
    header('Location: orders.php');
    exit();
}

// Get order details
$stmt = $pdo->prepare("
    SELECT o.*, c.name, c.email 
    FROM orders o 
    JOIN customers c ON o.user_id = c.id 
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$_SESSION['order_id'], $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: orders.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <title>Payment - E-Wallet</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme.min.css">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 76px;
        }
        .payment-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .qr-code {
            max-width: 300px;
            margin: 20px auto;
        }
        .payment-method-icon {
            width: 60px;
            height: 60px;
            object-fit: contain;
            margin-bottom: 10px;
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
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="payment-container">
                    <h2 class="text-center mb-4">E-Wallet Payment</h2>
                    
                    <div class="alert alert-info">
                        <h5 class="alert-heading">Order #<?= htmlspecialchars($order['order_number']) ?></h5>
                        <p class="mb-0">Total Amount: ₱<?= number_format($order['total_amount'], 2) ?></p>
                    </div>

                    <div class="row text-center g-4">
                        <div class="col-md-6">
                            <div class="p-3">
                                <img src="../images/payment/gcash-qr.png" alt="GCash" class="payment-method-icon">
                                <h5>GCash</h5>
                                <button class="btn btn-primary" onclick="showQRCode('gcash')">Pay with GCash</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3">
                                <img src="../images/payment/maya-qr.png" alt="Maya" class="payment-method-icon">
                                <h5>Maya</h5>
                                <button class="btn btn-primary" onclick="showQRCode('maya')">Pay with Maya</button>
                            </div>
                        </div>
                    </div>

                    <div id="qrCodeSection" class="text-center mt-4" style="display: none;">
                        <div class="qr-code">
                            <img src="" alt="QR Code" id="qrCodeImage" class="img-fluid mb-3">
                        </div>
                        <p class="text-muted">Scan the QR code using your e-wallet app to complete the payment</p>
                        <div class="mt-4">
                            <button class="btn btn-success" onclick="confirmPayment()">I've Completed the Payment</button>
                            <button class="btn btn-outline-secondary ms-2" onclick="cancelPayment()">Cancel Payment</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showQRCode(method) {
            const qrCodeSection = document.getElementById('qrCodeSection');
            const qrCodeImage = document.getElementById('qrCodeImage');
            qrCodeImage.src = `../images/payment/${method}-qr.png`;
            qrCodeSection.style.display = 'block';
        }

        function confirmPayment() {
            if (confirm('Have you completed the payment?')) {
                // In a real implementation, you would verify the payment with the e-wallet provider
                fetch('update_payment_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        order_id: <?= json_encode($order['id']) ?>,
                        status: 'paid'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = 'orders.php';
                    }
                });
            }
        }

        function cancelPayment() {
            if (confirm('Are you sure you want to cancel this payment?')) {
                window.location.href = 'orders.php';
            }
        }
    </script>
</body>
</html>