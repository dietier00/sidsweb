<?php
session_start();
require_once('../php/db_connect.php');

// Verify admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit();
}

// Handle payment verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $order_id = $_POST['order_id'];
        $action = $_POST['action'];
        $notes = $_POST['notes'] ?? '';

        if (!in_array($action, ['approve', 'reject'])) {
            throw new Exception("Invalid action");
        }

        $pdo->beginTransaction();

        // Get order details first
        $stmt = $pdo->prepare("
            SELECT o.*, c.email, c.name as customer_name 
            FROM orders o 
            JOIN customers c ON o.user_id = c.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        if (!$order) {
            throw new Exception("Order not found");
        }

        if ($action === 'approve') {
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_status = 'paid',
                    status = CASE 
                        WHEN status = 'pending' THEN 'processing'
                        ELSE status 
                    END,
                    admin_notes = CONCAT(COALESCE(admin_notes, ''), '\nPayment approved on ', NOW(), ': ', ?),
                    payment_verified_by = ?,
                    payment_verified_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            // Send payment confirmation email
            $to = $order['email'];
            $subject = "Payment Confirmed - Order #{$order['order_number']}";
            $message = "Dear {$order['customer_name']},\n\n";
            $message .= "Your payment for Order #{$order['order_number']} has been confirmed.\n";
            $message .= "We will start processing your order shortly.\n\n";
            $message .= "Order Details:\n";
            $message .= "Total Amount: ₱" . number_format($order['total_amount'], 2) . "\n";
            $message .= "Payment Method: " . ucfirst($order['payment_method']) . "\n\n";
            $message .= "Thank you for shopping with us!\n";
            $message .= "Skye Blinds Interior Design Services";
            
            mail($to, $subject, $message);
            
        } else {
            $stmt = $pdo->prepare("
                UPDATE orders 
                SET payment_status = 'failed',
                    admin_notes = CONCAT(COALESCE(admin_notes, ''), '\nPayment rejected on ', NOW(), ': ', ?),
                    payment_verified_by = ?,
                    payment_verified_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            
            // Send payment rejection email
            $to = $order['email'];
            $subject = "Payment Rejected - Order #{$order['order_number']}";
            $message = "Dear {$order['customer_name']},\n\n";
            $message .= "Unfortunately, we could not verify your payment for Order #{$order['order_number']}.\n\n";
            $message .= "Reason: " . $notes . "\n\n";
            $message .= "Please submit a new payment proof or contact our support team for assistance.\n";
            $message .= "You can submit your payment again by logging into your account and viewing your orders.\n\n";
            $message .= "Order Details:\n";
            $message .= "Total Amount: ₱" . number_format($order['total_amount'], 2) . "\n";
            $message .= "Payment Method: " . ucfirst($order['payment_method']) . "\n\n";
            $message .= "If you have any questions, please don't hesitate to contact us.\n\n";
            $message .= "Best regards,\n";
            $message .= "Skye Blinds Interior Design Services";
            
            mail($to, $subject, $message);
        }

        $stmt->execute([$notes, $_SESSION['admin_id'], $order_id]);
        $pdo->commit();
        
        $_SESSION['success'] = "Payment " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: verify_payment.php');
    exit();
}

// Fetch pending payments
$stmt = $pdo->prepare("
    SELECT o.*, 
           c.name as customer_name, 
           c.email,
           c.phone
    FROM orders o
    JOIN customers c ON o.user_id = c.id
    WHERE o.payment_status = 'pending'
        OR (o.payment_status = 'awaiting_payment' AND o.payment_proof IS NOT NULL)
    ORDER BY 
        CASE 
            WHEN o.payment_status = 'pending' THEN 1
            ELSE 2
        END,
        o.created_at DESC
");
$stmt->execute();
$pending_payments = $stmt->fetchAll();

// Get admin details
$stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$admin = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Verify Payments - Admin Dashboard</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .payment-proof-img {
            max-width: 300px;
            max-height: 300px;
            object-fit: contain;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .payment-proof-img:hover {
            transform: scale(1.05);
        }
        .payment-card {
            transition: all 0.3s ease;
        }
        .payment-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .badge-awaiting {
            background-color: #ffc107;
            color: #000;
        }
        .badge-pending {
            background-color: #17a2b8;
            color: #fff;
        }
        .verification-history {
            max-height: 150px;
            overflow-y: auto;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Payment Verifications</h2>
            <div class="d-flex align-items-center">
                <span class="badge bg-secondary me-2">
                    <?= count($pending_payments) ?> pending verifications
                </span>
                <button type="button" class="btn btn-outline-primary" onclick="window.location.reload()">
                    <i class="fas fa-sync-alt me-2"></i>Refresh
                </button>
            </div>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if(empty($pending_payments)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No pending payments to verify
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach($pending_payments as $payment): ?>
                    <div class="col-12">
                        <div class="card payment-card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <h5 class="card-title mb-1">
                                                    Order #<?= htmlspecialchars($payment['order_number']) ?>
                                                </h5>
                                                <span class="badge <?= $payment['payment_status'] === 'pending' ? 'badge-pending' : 'badge-awaiting' ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $payment['payment_status'])) ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('M j, Y g:i A', strtotime($payment['created_at'])) ?>
                                            </small>
                                        </div>

                                        <div class="mb-3">
                                            <p class="mb-1"><strong>Customer:</strong> <?= htmlspecialchars($payment['customer_name']) ?></p>
                                            <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($payment['email']) ?></p>
                                            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($payment['phone']) ?></p>
                                            <p class="mb-1"><strong>Amount:</strong> ₱<?= number_format($payment['total_amount'], 2) ?></p>
                                            <p class="mb-1"><strong>Payment Method:</strong> <?= ucfirst($payment['payment_method']) ?></p>
                                            <?php if($payment['payment_proof']): ?>
                                                <p class="mb-1">
                                                    <strong>Proof Uploaded:</strong> 
                                                    <?= date('M j, Y g:i A', strtotime($payment['payment_uploaded_at'])) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>

                                        <?php if(!empty($payment['admin_notes'])): ?>
                                            <div class="verification-history small bg-light p-2 rounded mb-3">
                                                <strong>Verification History:</strong><br>
                                                <?= nl2br(htmlspecialchars($payment['admin_notes'])) ?>
                                            </div>
                                        <?php endif; ?>

                                        <form method="POST" class="verify-payment-form">
                                            <input type="hidden" name="order_id" value="<?= $payment['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Admin Notes:</label>
                                                <textarea name="notes" class="form-control" rows="2" 
                                                          placeholder="Add notes about the verification..."></textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" name="action" value="approve" 
                                                        class="btn btn-success flex-grow-1"
                                                        onclick="return confirm('Are you sure you want to approve this payment?')">
                                                    <i class="fas fa-check me-2"></i>Approve Payment
                                                </button>
                                                <button type="submit" name="action" value="reject" 
                                                        class="btn btn-danger flex-grow-1"
                                                        onclick="return confirm('Are you sure you want to reject this payment?')">
                                                    <i class="fas fa-times me-2"></i>Reject Payment
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-md-6">
                                        <?php if($payment['payment_proof']): ?>
                                            <div class="text-center">
                                                <img src="../images/payment_proofs/<?= htmlspecialchars($payment['payment_proof']) ?>" 
                                                     class="payment-proof-img img-fluid rounded" 
                                                     alt="Payment Proof"
                                                     data-bs-toggle="modal" 
                                                     data-bs-target="#proofModal<?= $payment['id'] ?>">
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                Payment proof not yet uploaded
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Proof Modal -->
                    <?php if($payment['payment_proof']): ?>
                        <div class="modal fade" id="proofModal<?= $payment['id'] ?>" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Payment Proof - Order #<?= htmlspecialchars($payment['order_number']) ?>
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <img src="../images/payment_proofs/<?= htmlspecialchars($payment['payment_proof']) ?>" 
                                             class="img-fluid rounded" 
                                             alt="Payment Proof">
                                    </div>
                                    <div class="modal-footer">
                                        <a href="../images/payment_proofs/<?= htmlspecialchars($payment['payment_proof']) ?>" 
                                           class="btn btn-primary" 
                                           download="payment_proof_<?= htmlspecialchars($payment['order_number']) ?>">
                                            <i class="fas fa-download me-2"></i>Download Image
                                        </a>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>