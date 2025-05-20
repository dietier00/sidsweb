<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if not logged in or no order/product ID
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id']) || !isset($_GET['product_id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['order_id']);
$product_id = intval($_GET['product_id']);

// Verify order belongs to user and is completed
$stmt = $pdo->prepare("
    SELECT o.*, p.name as product_name, p.images
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    WHERE o.id = ? AND o.user_id = ? 
    AND o.status = 'completed'
    AND p.id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id'], $product_id]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['error'] = "Order not found or cannot be reviewed at this time";
    header('Location: orders.php');
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['rating']) || empty($_POST['review'])) {
        $_SESSION['error'] = "Please provide both rating and review";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO reviews (
                    user_id, product_id, order_id, 
                    rating, review_text, status
                ) VALUES (?, ?, ?, ?, ?, 'pending')
            ");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $product_id,
                $order_id,
                $_POST['rating'],
                $_POST['review']
            ]);

            $_SESSION['success'] = "Thank you for your review!";
            header('Location: orders.php#review');
            exit();
        } catch (Exception $e) {
            $_SESSION['error'] = "Failed to submit review. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
    <title>Write Review</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme.min.css">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 76px;
        }
        .review-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .star-rating {
            direction: rtl;
            display: inline-block;
            padding: 20px;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            color: #bbb;
            font-size: 2rem;
            padding: 0 2px;
            cursor: pointer;
            transition: all .3s ease;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label {
            color: #f7b731;
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
        <div class="review-container">
            <h2 class="mb-4">Write a Review</h2>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <div class="product-info mb-4">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        <?php if($order['images']): ?>
                            <img src="data:image/jpeg;base64,<?= base64_encode($order['images']) ?>" 
                                 alt="Product" class="img-fluid rounded">
                        <?php else: ?>
                            <img src="../images/products/default.jpg" alt="Product" class="img-fluid rounded">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-10">
                        <h5 class="mb-1"><?= htmlspecialchars($order['product_name']) ?></h5>
                        <p class="text-muted mb-0">Order #<?= htmlspecialchars($order['order_number']) ?></p>
                    </div>
                </div>
            </div>

            <form method="POST" action="">
                <div class="mb-4 text-center">
                    <label class="form-label d-block">Your Rating</label>
                    <div class="star-rating">
                        <input type="radio" id="star5" name="rating" value="5" required>
                        <label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star4" name="rating" value="4">
                        <label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star3" name="rating" value="3">
                        <label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star2" name="rating" value="2">
                        <label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                        <input type="radio" id="star1" name="rating" value="1">
                        <label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="review" class="form-label">Your Review</label>
                    <textarea class="form-control" id="review" name="review" rows="5" required
                              placeholder="Tell us what you think about this product..."></textarea>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="orders.php#review" class="btn btn-outline-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>