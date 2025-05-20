<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to view your orders";
    header('Location: ../users/login.php');
    exit();
}

// Get user's orders
$stmt = $pdo->prepare("
    SELECT o.*, oi.quantity, oi.price as item_price, 
           p.name as product_name, p.images
    FROM orders o 
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Group orders by status
$ordersByStatus = [
    'to_pay' => [],
    'shipping' => [],
    'to_receive' => [],
    'to_review' => []
];

foreach ($orders as $order) {
    switch ($order['status']) {
        case 'pending':
            $ordersByStatus['to_pay'][] = $order;
            break;
        case 'processing':
        case 'shipped':
            $ordersByStatus['shipping'][] = $order;
            break;
        case 'delivered':
            $ordersByStatus['to_receive'][] = $order;
            break;
        case 'completed':
            if (!isset($order['is_reviewed']) || !$order['is_reviewed']) {
                $ordersByStatus['to_review'][] = $order;
            }
            break;
        default:
            // Handle any other status
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/theme.min.css">
    <link href="../css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 76px;
        }
        .orders-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 20px;
        }
        .nav-pills .nav-link {
            color: #666;
            font-weight: 500;
            border-radius: 0;
            padding: 15px 25px;
            position: relative;
        }
        .nav-pills .nav-link.active {
            background-color: transparent;
            color: #DDA853;
        }
        .nav-pills .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #DDA853;
        }
        .order-card {
            border: 1px solid #eee;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 5px 15px;
            border-radius: 20px;
        }
        .status-to-pay { background-color: #ffecb5; color: #664d03; }
        .status-shipping { background-color: #cfe2ff; color: #084298; }
        .status-receive { background-color: #d1e7dd; color: #0f5132; }
        .status-review { background-color: #e2e3e5; color: #41464b; }
        .profile-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #0d6efd;
            text-decoration: none;
            margin-left: 15px;
            transition: all 0.3s ease;
        }
        .profile-icon:hover {
            background: #e2e6ea;
            color: #0a58ca;
        }
        .profile-menu {
            min-width: 200px;
        }
        .profile-menu .dropdown-item {
            padding: 8px 16px;
        }
        .profile-menu .dropdown-item i {
            margin-right: 8px;
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

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" href="../index.html#gallery">Gallery</a>
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
              <a class="nav-link" href="../index.html#services">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../index.html#about">About</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="../users/contact.php">Contact</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="cart.php">Cart</a>
            </li>
          </ul>
          <!-- Add Profile Icon -->
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

    <div class="container py-5">
        <div class="orders-container">
            <h2 class="mb-4">My Orders</h2>

            <!-- Order Status Tabs -->
            <ul class="nav nav-pills mb-4" id="ordersTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="to-pay-tab" data-bs-toggle="tab" data-bs-target="#to-pay" type="button" role="tab" aria-controls="to-pay" aria-selected="true">To Pay</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">Shipping</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="receive-tab" data-bs-toggle="tab" data-bs-target="#receive" type="button" role="tab" aria-controls="receive" aria-selected="false">To Receive</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="review-tab" data-bs-toggle="tab" data-bs-target="#review" type="button" role="tab" aria-controls="review" aria-selected="false">To Review</button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="ordersTabContent">
                <!-- To Pay Tab -->
                <div class="tab-pane fade show active" id="to-pay" role="tabpanel">
                    <?php foreach ($ordersByStatus['to_pay'] as $order): ?>
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order #<?= htmlspecialchars($order['id']) ?></h6>
                            <span class="status-badge status-to-pay">To Pay</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if($order['images']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($order['images']) ?>" 
                                         alt="Product" class="img-fluid rounded">
                                <?php else: ?>
                                    <img src="../images/products/default.jpg" alt="Product" class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?= htmlspecialchars($order['product_name']) ?></h6>
                                <p class="text-muted mb-0">Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-1">₱<?= number_format($order['item_price'] * $order['quantity'], 2) ?></h6>
                                <button class="btn btn-primary btn-sm btn-pay-now" data-order-id="<?= htmlspecialchars($order['id']) ?>">Pay Now</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Shipping Tab -->
                <div class="tab-pane fade" id="shipping" role="tabpanel">
                    <?php foreach ($ordersByStatus['shipping'] as $order): ?>
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order #<?= htmlspecialchars($order['id']) ?></h6>
                            <span class="status-badge status-shipping">Shipping</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if($order['images']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($order['images']) ?>" 
                                         alt="Product" class="img-fluid rounded">
                                <?php else: ?>
                                    <img src="../images/products/default.jpg" alt="Product" class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?= htmlspecialchars($order['product_name']) ?></h6>
                                <p class="text-muted mb-0">Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-1">₱<?= number_format($order['item_price'] * $order['quantity'], 2) ?></h6>
                                <button class="btn btn-outline-primary btn-sm btn-track-order" data-order-id="<?= htmlspecialchars($order['id']) ?>">Track Order</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- To Receive Tab -->
                <div class="tab-pane fade" id="receive" role="tabpanel">
                    <?php foreach ($ordersByStatus['to_receive'] as $order): ?>
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order #<?= htmlspecialchars($order['id']) ?></h6>
                            <span class="status-badge status-receive">To Receive</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if($order['images']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($order['images']) ?>" 
                                         alt="Product" class="img-fluid rounded">
                                <?php else: ?>
                                    <img src="../images/products/default.jpg" alt="Product" class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?= htmlspecialchars($order['product_name']) ?></h6>
                                <p class="text-muted mb-0">Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-1">₱<?= number_format($order['item_price'] * $order['quantity'], 2) ?></h6>
                                <button class="btn btn-success btn-sm btn-confirm-receipt" data-order-id="<?= htmlspecialchars($order['id']) ?>">Confirm Receipt</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- To Review Tab -->
                <div class="tab-pane fade" id="review" role="tabpanel">
                    <?php foreach ($ordersByStatus['to_review'] as $order): ?>
                    <div class="order-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Order #<?= htmlspecialchars($order['id']) ?></h6>
                            <span class="status-badge status-review">To Review</span>
                        </div>
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if($order['images']): ?>
                                    <img src="data:image/jpeg;base64,<?= base64_encode($order['images']) ?>" 
                                         alt="Product" class="img-fluid rounded">
                                <?php else: ?>
                                    <img src="../images/products/default.jpg" alt="Product" class="img-fluid rounded">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1"><?= htmlspecialchars($order['product_name']) ?></h6>
                                <p class="text-muted mb-0">Quantity: <?= htmlspecialchars($order['quantity']) ?></p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <h6 class="mb-1">₱<?= number_format($order['item_price'] * $order['quantity'], 2) ?></h6>
                                <button class="btn btn-outline-secondary btn-sm btn-write-review" data-order-id="<?= htmlspecialchars($order['id']) ?>" data-product-id="<?= htmlspecialchars($order['product_id']) ?>">Write Review</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user is logged in
            fetch('../php/check_session.php')
                .then(response => response.json())
                .then(data => {
                    const profileContent = document.getElementById('profileContent');
                    if (data.loggedIn) {
                        profileContent.innerHTML = `
                            <li><span class="dropdown-item-text">Hi, ${data.fullname}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../users/profile.php"><i class="fas fa-user-circle"></i> My Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag"></i> My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../php/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        `;
                    } else {
                        window.location.href = '../users/login.php';
                    }
                })
                .catch(error => console.error('Error:', error));

            // Initialize Bootstrap tabs
            const triggerTabList = [].slice.call(document.querySelectorAll('#ordersTab button'));
            triggerTabList.forEach(function(triggerEl) {
                const tabTrigger = new bootstrap.Tab(triggerEl);
                triggerEl.addEventListener('click', function(event) {
                    event.preventDefault();
                    tabTrigger.show();
                });
            });

            // Tab specific functions
            const orderFunctions = {
                // Handle Pay Now functionality
                handlePayment: function(orderId) {
                    window.location.href = `payment.php?order_id=${orderId}`;
                },

                // Handle Track Order functionality
                handleTracking: function(orderId) {
                    // You can implement tracking logic here
                    alert('Tracking functionality will be implemented soon.');
                },

                // Handle Order Receipt Confirmation
                handleReceiptConfirmation: function(orderId) {
                    if (confirm('Have you received this order? This action cannot be undone.')) {
                        fetch('confirm_receipt.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ order_id: orderId })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert('Failed to confirm receipt. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        });
                    }
                },

                // Handle Review Writing
                handleReviewWriting: function(orderId, productId) {
                    window.location.href = `write-review.php?order_id=${orderId}&product_id=${productId}`;
                }
            };

            // Add event listeners for all action buttons
            document.querySelectorAll('.btn-pay-now').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    orderFunctions.handlePayment(orderId);
                });
            });

            document.querySelectorAll('.btn-track-order').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    orderFunctions.handleTracking(orderId);
                });
            });

            document.querySelectorAll('.btn-confirm-receipt').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    orderFunctions.handleReceiptConfirmation(orderId);
                });
            });

            document.querySelectorAll('.btn-write-review').forEach(button => {
                button.addEventListener('click', function() {
                    const orderId = this.getAttribute('data-order-id');
                    const productId = this.getAttribute('data-product-id');
                    orderFunctions.handleReviewWriting(orderId, productId);
                });
            });
        });

        // Initialize navbar scroll effect
        let scrollpos = window.scrollY;
        const header = document.querySelector(".navbar");
        const header_height = header.offsetHeight;

        const add_class_on_scroll = () => header.classList.add("scrolled", "shadow-sm");
        const remove_class_on_scroll = () => header.classList.remove("scrolled", "shadow-sm");

        window.addEventListener('scroll', function() {
            scrollpos = window.scrollY;
            if (scrollpos >= header_height) { add_class_on_scroll(); }
            else { remove_class_on_scroll(); }
        });

        // Add active class to current tab based on URL hash
        function setActiveTab() {
            const hash = window.location.hash;
            if (hash) {
                const tab = document.querySelector(`#ordersTab button[data-bs-target="${hash}"]`);
                if (tab) {
                    tab.click();
                }
            }
        }

        // Call setActiveTab on page load and when hash changes
        window.addEventListener('load', setActiveTab);
        window.addEventListener('hashchange', setActiveTab);
    </script>

    <!-- Add Bootstrap JS -->
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>