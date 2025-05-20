<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}
require_once '../php/db_connect.php';

// Handle order status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['new_status'])) {
    try {
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET status = ?, updated_at = CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
        $stmt->execute([$_POST['new_status'], $_POST['order_id']]);
        $_SESSION['success'] = "Order status updated successfully.";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating order: " . $e->getMessage();
    }
    header("Location: orders.php");
    exit();
}

// Fetch orders with pagination and filtering
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Build the query based on filters
$where_conditions = [];
$params = [];

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_conditions[] = "o.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['payment_status']) && !empty($_GET['payment_status'])) {
    $where_conditions[] = "o.payment_status = ?";
    $params[] = $_GET['payment_status'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(o.order_number LIKE ? OR c.name LIKE ? OR c.email LIKE ?)";
    $search_term = "%" . $_GET['search'] . "%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";

// Get total orders count
$count_sql = "
    SELECT COUNT(*) as total 
    FROM orders o 
    JOIN customers c ON o.user_id = c.id 
    $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_orders = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_orders / $items_per_page);

// Get orders for current page
$orders_sql = "
    SELECT o.*, c.name as customer_name, c.email as customer_email,
           GROUP_CONCAT(p.name) as products,
           SUM(oi.quantity) as total_items,
           SUM(oi.total_price - (oi.quantity * COALESCE(p.cost, p.price * 0.7))) as profit
    FROM orders o
    JOIN customers c ON o.user_id = c.id
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    $where_clause
    GROUP BY o.id
    ORDER BY o.created_at DESC
    LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($orders_sql);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

// Bind other parameters if they exist
$param_position = 1;
foreach ($params as $param) {
    $stmt->bindValue($param_position, $param);
    $param_position++;
}

$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .status-badge {
            min-width: 100px;
            text-align: center;
        }
        .order-details {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark text-white">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>
            <ul class="list-unstyled components">
                <li>
                    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="orders.php"><i class="bi bi-cart"></i> Orders</a>
                </li>
                <li>
                    <a href="products.php"><i class="bi bi-box-seam"></i> Products</a>
                </li>
                <li>
                    <a href="messages.php"><i class="bi bi-envelope"></i> Messages</a>
                </li>
                <li>
                    <a href="settings.php"><i class="bi bi-gear"></i> Settings</a>
                </li>
                <li>
                    <a href="profile.php"><i class="bi bi-person"></i> Profile</a>
                </li>
                <li>
                    <a href="auth/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <div class="container-fluid">
                <div class="row mb-4">
                    <div class="col-12">
                        <h2>Orders Management</h2>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <input type="text" class="form-control" name="search" 
                                       placeholder="Search orders..." 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="processing" <?php echo (isset($_GET['status']) && $_GET['status'] === 'processing') ? 'selected' : ''; ?>>Processing</option>
                                    <option value="shipped" <?php echo (isset($_GET['status']) && $_GET['status'] === 'shipped') ? 'selected' : ''; ?>>Shipped</option>
                                    <option value="delivered" <?php echo (isset($_GET['status']) && $_GET['status'] === 'delivered') ? 'selected' : ''; ?>>Delivered</option>
                                    <option value="cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] === 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" name="payment_status">
                                    <option value="">All Payment Status</option>
                                    <option value="pending" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                                    <option value="failed" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                                    <option value="refunded" <?php echo (isset($_GET['payment_status']) && $_GET['payment_status'] === 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="orders.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total Amount</th>
                                        <th>Items</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($order['customer_name']); ?><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($order['customer_email']); ?></small>
                                        </td>
                                        <td>
                                            ₱<?php echo number_format($order['total_amount'], 2); ?><br>
                                            <small class="text-success">Profit: ₱<?php echo number_format($order['profit'], 2); ?></small>
                                        </td>
                                        <td><?php echo $order['total_items']; ?> items</td>
                                        <td>
                                            <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?> status-badge">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo getPaymentStatusBadgeClass($order['payment_status']); ?> status-badge">
                                                <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-order" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#orderDetailsModal"
                                                    data-order='<?php echo htmlspecialchars(json_encode($order)); ?>'>
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-primary update-status"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateStatusModal"
                                                    data-order-id="<?php echo $order['id']; ?>"
                                                    data-current-status="<?php echo $order['status']; ?>">
                                                <i class="bi bi-arrow-repeat"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <nav aria-label="Orders pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo isset($_GET['status']) ? '&status='.htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.htmlspecialchars($_GET['payment_status']) : ''; ?><?php echo isset($_GET['search']) ? '&search='.htmlspecialchars($_GET['search']) : ''; ?>">Previous</a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['status']) ? '&status='.htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.htmlspecialchars($_GET['payment_status']) : ''; ?><?php echo isset($_GET['search']) ? '&search='.htmlspecialchars($_GET['search']) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo isset($_GET['status']) ? '&status='.htmlspecialchars($_GET['status']) : ''; ?><?php echo isset($_GET['payment_status']) ? '&payment_status='.htmlspecialchars($_GET['payment_status']) : ''; ?><?php echo isset($_GET['search']) ? '&search='.htmlspecialchars($_GET['search']) : ''; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="order-details">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Order Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="updateStatusForm" method="POST">
                        <input type="hidden" name="order_id" id="update_order_id">
                        <div class="mb-3">
                            <label for="new_status" class="form-label">New Status</label>
                            <select class="form-select" name="new_status" id="new_status" required>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="shipped">Shipped</option>
                                <option value="delivered">Delivered</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Helper function to format currency
        function formatCurrency(amount) {
            return '₱' + parseFloat(amount).toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Handle View Order button click
        document.querySelectorAll('.view-order').forEach(button => {
            button.addEventListener('click', function() {
                const order = JSON.parse(this.dataset.order);
                const modalBody = document.querySelector('#orderDetailsModal .order-details');
                
                modalBody.innerHTML = `
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Order Information</h6>
                            <p><strong>Order Number:</strong> ${order.order_number}</p>
                            <p><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</p>
                            <p><strong>Status:</strong> ${order.status}</p>
                            <p><strong>Payment Status:</strong> ${order.payment_status}</p>
                            <p><strong>Payment Method:</strong> ${order.payment_method}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Customer Information</h6>
                            <p><strong>Name:</strong> ${order.customer_name}</p>
                            <p><strong>Email:</strong> ${order.customer_email}</p>
                            <p><strong>Phone:</strong> ${order.contact_number}</p>
                            <p><strong>Shipping Address:</strong> ${order.shipping_address}</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <h6>Order Summary</h6>
                            <p><strong>Total Amount:</strong> ${formatCurrency(order.total_amount)}</p>
                            <p><strong>Total Items:</strong> ${order.total_items}</p>
                            <p><strong>Products:</strong> ${order.products}</p>
                            <p><strong>Profit:</strong> ${formatCurrency(order.profit)}</p>
                        </div>
                    </div>
                `;
            });
        });

        // Handle Update Status button click
        document.querySelectorAll('.update-status').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('update_order_id').value = this.dataset.orderId;
                document.getElementById('new_status').value = this.dataset.currentStatus;
            });
        });

        // Show success message if exists
        <?php if(isset($_SESSION['success'])): ?>
            alert("<?php echo $_SESSION['success']; ?>");
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        // Show error message if exists
        <?php if(isset($_SESSION['error'])): ?>
            alert("<?php echo $_SESSION['error']; ?>");
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>
</body>
</html>
<?php
function getStatusBadgeClass($status) {
    $classes = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'danger'
    ];
    return $classes[$status] ?? 'secondary';
}

function getPaymentStatusBadgeClass($status) {
    $classes = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger',
        'refunded' => 'info'
    ];
    return $classes[$status] ?? 'secondary';
}