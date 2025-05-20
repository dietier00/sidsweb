<?php
session_start();
require_once('../php/db_connect.php');

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Please login to access your cart";
    header('Location: ../users/login.php');
    exit();
}

// Initialize cart if not set
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle checkout action
if (isset($_POST['checkout']) && !empty($_SESSION['cart'])) {
    try {
        $pdo->beginTransaction();

        // Get user's address and contact from their profile
        $userStmt = $pdo->prepare("SELECT address, phone FROM customers WHERE id = ?");
        $userStmt->execute([$_SESSION['user_id']]);
        $user = $userStmt->fetch();

        if (!$user['address'] || !$user['phone']) {
            throw new Exception("Please complete your profile with address and phone number before checkout.");
        }

        // Calculate total amount
        $totalAmount = 0;
        foreach ($_SESSION['cart'] as $item) {
            $totalAmount += $item['price'] * $item['quantity'];
        }

        // Generate unique order number (e.g., ORD-20250514-XXXX)
        $orderNumber = 'ORD-' . date('Ymd') . '-' . substr(uniqid(), -4);

        // Create new order
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                order_number, user_id, total_amount, shipping_address, 
                billing_address, contact_number, status, payment_status
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'pending')
        ");

        $stmt->execute([
            $orderNumber,
            $_SESSION['user_id'],
            $totalAmount,
            $user['address'],
            $user['address'], // Using same address for billing and shipping
            $user['phone']
        ]);

        $orderId = $pdo->lastInsertId();

        // Add order items
        $stmt = $pdo->prepare("
            INSERT INTO order_items (
                order_id, product_id, quantity, price, total_price
            ) VALUES (?, ?, ?, ?, ?)
        ");

        // Update product stock and insert order items
        foreach ($_SESSION['cart'] as $item) {
            // Check stock availability
            $stockStmt = $pdo->prepare("SELECT stock FROM products WHERE id = ? AND status = 'active'");
            $stockStmt->execute([$item['id']]);
            $product = $stockStmt->fetch();
            
            if (!$product || $product['stock'] < $item['quantity']) {
                throw new Exception("Sorry, some items in your cart are no longer available in the requested quantity.");
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

        // Clear the cart after successful checkout
        $_SESSION['cart'] = [];
        $_SESSION['success'] = "Order placed successfully! Order number: " . $orderNumber;
        header('Location: orders.php');
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}

// Fetch products from database
$products = [];
try {
    $stmt = $pdo->query("SELECT id, name, price, images, stock, status FROM products WHERE status = 'active'");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle add/update/remove actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add product
    if (isset($_POST['product_id'], $_POST['quantity'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Check stock availability before adding to cart
        $stockStmt = $pdo->prepare("SELECT id, name, price, images, stock FROM products WHERE id = ? AND status = 'active'");
        $stockStmt->execute([$product_id]);
        $product = $stockStmt->fetch();

        if (!$product) {
            $_SESSION['error'] = "Product not found or no longer available.";
        } elseif ($product['stock'] < $quantity) {
            $_SESSION['error'] = "Sorry, only " . $product['stock'] . " units available.";
        } else {
            $found = false;
            // Check if product already in cart
            if (isset($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] === $product_id) {
                        // Check if new total quantity exceeds stock
                        if (($item['quantity'] + $quantity) > $product['stock']) {
                            $_SESSION['error'] = "Cannot add more units. Stock limit reached.";
                        } else {
                            $item['quantity'] += $quantity;
                            $_SESSION['success'] = "Cart updated successfully.";
                        }
                        $found = true;
                        break;
                    }
                }
            }
            
            if (!$found && !isset($_SESSION['error'])) {
                $_SESSION['cart'][] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['images'],
                    'quantity' => $quantity
                ];
                $_SESSION['success'] = "Product added to cart successfully.";
            }
        }
    }
    // Update quantity
    if (isset($_POST['update_id'], $_POST['quantity'])) {
        $update_id = (int)$_POST['update_id'];
        $quantity = (int)$_POST['quantity'];
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] === $update_id) {
                $item['quantity'] = $quantity;
                break;
            }
        }
    }
    // Remove item
    if (isset($_POST['remove_id'])) {
        $remove_id = (int)$_POST['remove_id'];
        $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($remove_id) {
            return $item['id'] !== $remove_id;
        });
    }
    // Redirect to cart page after any POST action
    header('Location: cart.php');
    exit;
}
?>
<!doctype html>
<html class="h-100" lang="en">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
      <meta name="description" content="Skye Interior Design Services - Your trusted provider of quality window blinds and treatments">
      <meta name="author" content="Skye Interior Design Services">
      <meta name="HandheldFriendly" content="true">
      <title>Skye Blinds Interior Design Services - Cart</title>

      <link rel="stylesheet" href="../css/theme.min.css">
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@100;300;400;600;700&display=swap" rel="stylesheet">
      <link href="../css/bootstrap.min.css" rel="stylesheet">
      <link href="../css/bootstrap-icons.css" rel="stylesheet">
      <link href="../css/owl.carousel.min.css" rel="stylesheet">
      <link href="../css/card.css" rel="stylesheet">
      <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

      <link rel="icon" type="image/x-icon" href="../favicon/favicon.ico">
      <link rel="apple-touch-icon" sizes="180x180" href="../favicon/apple-touch-icon.png">
      <link rel="icon" type="image/png" sizes="192x192" href="../favicon/android-chrome-192x192.png">
      <link rel="icon" type="image/png" sizes="512x512" href="../favicon/android-chrome-512x512.png">
      <link rel="icon" type="image/png" sizes="32x32" href="../favicon/favicon-32x32.png">
      <link rel="icon" type="image/png" sizes="16x16" href="../favicon/favicon-16x16.png">
      <link rel="manifest" href="/site.webmanifest">

  </head>
  <body data-bs-spy="scroll" data-bs-target="#navScroll">
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
              <a class="nav-link dropdown-toggle" href="../index.html#products" id="productsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
              <a class="nav-link" href="../index.html#contact">Contact</a>
            </li>
            <li class="nav-item">
              <a class="nav-link active" href="cart.php">Cart</a>
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
    
    <!-- Add CSS for profile menu -->
    <style>
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

    <!-- Products Table -->
    <main style="margin-top: 80px;">
      <div class="container cart-section">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="row">
          <div class="col-lg-8 col-12 mb-4">
            <div class="mb-4">
              <h1 class="display-6 fw-bold mb-3">Your Cart</h1>
            </div>
            <div id="cart-items">
              <?php if(empty($_SESSION['cart'])): ?>
                <div class="alert alert-info">Your cart is empty.</div>
              <?php else: ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                  <div class="cart-item d-flex mb-4">
                    <div class="cart-item-image-container" style="width: 120px; height: 120px; overflow: hidden; border-radius: 8px;">
                      <?php if($item['image']): ?>
                        <img src="data:image/jpeg;base64,<?= base64_encode($item['image']) ?>" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                      <?php else: ?>
                        <img src="../images/products/default.jpg" 
                             alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                      <?php endif; ?>
                    </div>
                    <div class="cart-item-details ms-4 flex-grow-1">
                      <div class="d-flex justify-content-between align-items-start">
                        <div>
                          <h5 class="cart-item-title mb-2"><?= htmlspecialchars($item['name']) ?></h5>
                          <p class="mb-1 text-muted">Price: ₱<?= number_format($item['price'], 2) ?></p>
                        </div>
                        <div class="text-end">
                          <div class="cart-quantity-controls d-flex align-items-center mb-2">
                            <form method="post" action="cart.php" class="d-flex align-items-center">
                              <input type="hidden" name="update_id" value="<?= $item['id'] ?>">
                              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="decrementQuantity(this)">-</button>
                              <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                     min="1" class="form-control mx-2" style="width: 60px;">
                              <button type="button" class="btn btn-outline-secondary btn-sm" onclick="incrementQuantity(this)">+</button>
                              <button type="submit" class="btn btn-primary btn-sm ms-2">Update</button>
                            </form>
                          </div>
                          <form method="post" action="cart.php" class="d-inline">
                            <input type="hidden" name="remove_id" value="<?= $item['id'] ?>">
                            <button class="btn btn-danger btn-sm">Remove</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>

          <div class="col-lg-4 col-12">
            <div class="summary-card">
              <h2 class="summary-title">Order Summary</h2>
              <div class="summary-item">
                <span>Subtotal (<?= count($_SESSION['cart']) ?> items)</span>
                <span>₱<?= number_format(array_sum(array_map(function($item) {
                  return $item['price'] * $item['quantity'];
                }, $_SESSION['cart'])), 2) ?></span>
              </div>
              <div class="summary-item">
                <span>Shipping & Handling</span>
                <span>₱0.00</span>
              </div>
              <div class="summary-item">
                <span>Tax</span>
                <span>₱0.00</span>
              </div>
              <div class="summary-total">
                <span>Total</span>
                <span>₱<?= number_format(array_sum(array_map(function($item) {
                  return $item['price'] * $item['quantity'];
                }, $_SESSION['cart'])), 2) ?></span>
              </div>
              <?php if(!empty($_SESSION['cart'])): ?>
                <form method="post" action="billing.php">
                  <button type="submit" class="btn custom-btn w-100 mt-3">Proceed to Checkout</button>
                </form>
              <?php else: ?>
                <button class="btn custom-btn w-100 mt-3" disabled>Cart is Empty</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </main>

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
                        profileContent.innerHTML = `
                            <li><a class="dropdown-item" href="../users/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                            <li><a class="dropdown-item" href="../users/register.php"><i class="fas fa-user-plus"></i> Register</a></li>
                        `;
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        function confirmCheckout() {
            return confirm('Are you sure you want to place this order?');
        }

        // Update quantity controls
        function incrementQuantity(button) {
            const input = button.parentElement.querySelector('input[type="number"]');
            input.value = parseInt(input.value) + 1;
        }

        function decrementQuantity(button) {
            const input = button.parentElement.querySelector('input[type="number"]');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        // Navbar scroll shadow
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
    </script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/jquery.min.js"></script>
    <script src="../js/custom.js"></script>
  </body>
</html>