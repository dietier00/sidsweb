<?php
include_once('../header.php');

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $orderId = $_POST['order_id'];
    $reason = $_POST['reason'];
    
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = 'cancelled', cancellation_reason = ? 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$reason, $orderId, $userId]);
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'review') {
    $orderId = $_POST['order_id'];
    $productId = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    
    $stmt = $pdo->prepare("
        INSERT INTO reviews (user_id, product_id, order_id, rating, comment)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$userId, $productId, $orderId, $rating, $comment]);
}

// Fetch orders grouped by status
function getOrdersByStatus($pdo, $userId, $status) {
    $stmt = $pdo->prepare("
        SELECT o.*, oi.*, p.name as product_name, p.images,
               ua.street_address, ua.city, ua.region, ua.postal_code,
               r.rating, r.comment as review_comment
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN user_addresses ua ON o.shipping_address = ua.id
        LEFT JOIN reviews r ON oi.order_id = r.order_id AND oi.product_id = r.product_id
        WHERE o.user_id = ? AND o.order_status = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId, $status]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// New function to fetch shipping-related orders
function getShippingOrders($pdo, $userId) {
    $statuses = ['processing', 'shipping', 'shipped'];
    $in  = str_repeat('?,', count($statuses) - 1) . '?';
    $sql = "
        SELECT o.*, oi.*, p.name as product_name, p.images,
               ua.street_address, ua.city, ua.region, ua.postal_code,
               r.rating, r.comment as review_comment
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN user_addresses ua ON o.shipping_address = ua.id
        LEFT JOIN reviews r ON oi.order_id = r.order_id AND oi.product_id = r.product_id
        WHERE o.user_id = ? AND o.order_status IN ($in)
        ORDER BY o.created_at DESC
    ";
    $params = array_merge([$userId], $statuses);
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$payOrders = getOrdersByStatus($pdo, $userId, 'pending');
$shippingOrders = getShippingOrders($pdo, $userId);
$completedOrders = getOrdersByStatus($pdo, $userId, 'completed');
$cancelledOrders = getOrdersByStatus($pdo, $userId, 'cancelled');
?>

<body data-bs-spy="scroll" data-bs-target="#navScroll" style="background:#fff;">
<nav id="navScroll" class="navbar navbar-expand-lg navbar-light fixed-top" aria-label="Main navigation">
      <div class="container">
        <a class="navbar-brand pe-4 fs-4" href="../index.php">
          <img src="../favicon/favicon.ico" alt="Skye Logo" style="height:40px; width:auto; vertical-align:middle; margin-right:8px;">
          <span class="ms-1 fw-bolder">Skye Blinds Interior Design Services</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                  <a class="nav-link" href="../index.php#gallery">Gallery</a>
                </li>
                <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#products" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Products
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="productsDropdown">
                    <li><a class="dropdown-item" href="productsdetail.php">Product Details</a></li>
                    <li><a class="dropdown-item" href="products.php">Product Listing</a></li>
                  </ul>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="../index.php#services">Services</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="../index.php#about">About</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="../users/contact.php">Contact</a>
                </li>
            </ul>
            <div class="nav-item dropdown">
                <a href="#" class="profile-icon" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="profileDropdown">
                    <div id="profileContent">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </ul>
            </div>
        </div>
      </div>
    </nav>

    <main class="container py-5" style="margin-top: 60px;">
        <h2 class="mb-4">My Orders</h2>
        
        <!-- Order Status Tabs -->
        <ul class="nav nav-pills mb-4" id="orderTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pay">
                    Pay
                    <?php if(count($payOrders) > 0): ?>
                        <span class="badge bg-danger"><?php echo count($payOrders); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#shipping">
                    Shipping
                    <?php if(count($shippingOrders) > 0): ?>
                        <span class="badge bg-primary"><?php echo count($shippingOrders); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#completed">
                    Completed
                    <?php if(count($completedOrders) > 0): ?>
                        <span class="badge bg-success"><?php echo count($completedOrders); ?></span>
                    <?php endif; ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cancelled">
                    Cancelled
                    <?php if(count($cancelledOrders) > 0): ?>
                        <span class="badge bg-secondary"><?php echo count($cancelledOrders); ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Pay Tab -->
            <div class="tab-pane fade show active" id="pay">
                <?php if (empty($payOrders)): ?>
                    <div class="alert alert-info">No pending payments.</div>
                <?php else: ?>
                    <?php foreach ($payOrders as $order): ?>
                        <div class="card order-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <span class="status-badge badge bg-warning">Pending Payment</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['images']); ?>" 
                                             class="product-image-small" 
                                             alt="<?php echo htmlspecialchars($order['product_name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                        <p class="text-muted mb-1">
                                            Quantity: <?php echo $order['quantity']; ?> × 
                                            ₱<?php echo number_format($order['price'], 2); ?>
                                        </p>
                                        <p class="text-muted mb-0">
                                            Payment Method: <?php echo strtoupper($order['payment_method']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <p class="fw-bold mb-3">
                                            Total: ₱<?php echo number_format($order['total_amount'], 2); ?>
                                        </p>
                                        <?php if ($order['payment_method'] === 'gcash'): ?>
                                            <button class="btn btn-primary btn-sm" onclick="showPaymentModal(<?php echo $order['id']; ?>)">
                                                Pay with GCash
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Shipping Tab -->
            <div class="tab-pane fade" id="shipping">
                <?php if (empty($shippingOrders)): ?>
                    <div class="alert alert-info">No orders in transit.</div>
                <?php else: ?>
                    <?php foreach ($shippingOrders as $order): ?>
                        <div class="card order-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <span class="status-badge badge bg-info">In Transit</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['images']); ?>" 
                                             class="product-image-small" 
                                             alt="<?php echo htmlspecialchars($order['product_name']); ?>">
                                    </div>
                                    <div class="col-md-9">
                                        <h6><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                        <div class="tracking-timeline mt-3">
                                            <div class="timeline-item">
                                                <p class="mb-0"><strong>Order Confirmed</strong></p>
                                                <small class="text-muted">Your order has been confirmed</small>
                                            </div>
                                            <div class="timeline-item">
                                                <p class="mb-0"><strong>Out for Delivery</strong></p>
                                                <small class="text-muted">Expected delivery: 2-3 business days</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Completed Tab -->
            <div class="tab-pane fade" id="completed">
                <?php if (empty($completedOrders)): ?>
                    <div class="alert alert-info">No completed orders.</div>
                <?php else: ?>
                    <?php foreach ($completedOrders as $order): ?>
                        <div class="card order-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <span class="status-badge badge bg-success">Completed</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['images']); ?>" 
                                             class="product-image-small" 
                                             alt="<?php echo htmlspecialchars($order['product_name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                        <p class="text-muted mb-1">
                                            Delivered on <?php echo date('M d, Y', strtotime($order['updated_at'])); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-3 text-end">
                                        <?php if (!$order['rating']): ?>
                                            <button class="btn btn-outline-primary btn-sm" 
                                                    onclick="showReviewModal(<?php echo $order['id']; ?>, <?php echo $order['product_id']; ?>)">
                                                Write a Review
                                            </button>
                                        <?php else: ?>
                                            <div class="rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star<?php echo $i <= $order['rating'] ? '' : '-o'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['review_comment']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Cancelled Tab -->
            <div class="tab-pane fade" id="cancelled">
                <?php if (empty($cancelledOrders)): ?>
                    <div class="alert alert-info">No cancelled orders.</div>
                <?php else: ?>
                    <?php foreach ($cancelledOrders as $order): ?>
                        <div class="card order-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title mb-0">Order #<?php echo $order['id']; ?></h5>
                                    <span class="status-badge badge bg-danger">Cancelled</span>
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($order['images']); ?>" 
                                             class="product-image-small" 
                                             alt="<?php echo htmlspecialchars($order['product_name']); ?>">
                                    </div>
                                    <div class="col-md-9">
                                        <h6><?php echo htmlspecialchars($order['product_name']); ?></h6>
                                        <p class="text-muted mb-1">
                                            Cancelled on <?php echo date('M d, Y', strtotime($order['updated_at'])); ?>
                                        </p>
                                        <p class="text-danger mb-0">
                                            Reason: <?php echo htmlspecialchars($order['cancellation_reason']); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Write a Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="reviewForm" method="POST">
                        <input type="hidden" name="action" value="review">
                        <input type="hidden" name="order_id" id="reviewOrderId">
                        <input type="hidden" name="product_id" id="reviewProductId">
                        
                        <div class="mb-3">
                            <label class="form-label">Rating</label>
                            <div class="rating-input">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>">
                                    <label for="star<?php echo $i; ?>"><i class="far fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Comment</label>
                            <textarea class="form-control" name="comment" rows="3" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- GCash Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GCash Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Add GCash payment implementation here -->
                    <p>GCash payment integration will be implemented here.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        function showReviewModal(orderId, productId) {
            document.getElementById('reviewOrderId').value = orderId;
            document.getElementById('reviewProductId').value = productId;
            new bootstrap.Modal(document.getElementById('reviewModal')).show();
        }
        
        function showPaymentModal(orderId) {
            new bootstrap.Modal(document.getElementById('paymentModal')).show();
        }
        
        // Star rating functionality
        document.querySelectorAll('.rating-input label').forEach(label => {
            label.addEventListener('mouseover', function() {
                const stars = this.parentElement.querySelectorAll('label');
                const starValue = this.previousElementSibling.value;
                stars.forEach(s => {
                    if (s.previousElementSibling.value <= starValue) {
                        s.querySelector('i').classList.remove('far');
                        s.querySelector('i').classList.add('fas');
                    }
                });
            });
            
            label.addEventListener('mouseout', function() {
                const stars = this.parentElement.querySelectorAll('label');
                const checkedStar = this.parentElement.querySelector('input:checked');
                const checkedValue = checkedStar ? checkedStar.value : 0;
                stars.forEach(s => {
                    if (s.previousElementSibling.value > checkedValue) {
                        s.querySelector('i').classList.remove('fas');
                        s.querySelector('i').classList.add('far');
                    }
                });
            });
            
            label.addEventListener('click', function() {
                const stars = this.parentElement.querySelectorAll('label');
                const starValue = this.previousElementSibling.value;
                this.previousElementSibling.checked = true;
                stars.forEach(s => {
                    const i = s.querySelector('i');
                    if (s.previousElementSibling.value <= starValue) {
                        i.classList.remove('far');
                        i.classList.add('fas');
                    } else {
                        i.classList.remove('fas');
                        i.classList.add('far');
                    }
                });
            });
        });
    </script>
</body>
</html>