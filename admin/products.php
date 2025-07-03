<?php 

session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}
require_once '../php/db_connect.php';

// Fetch products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total products count
$count_sql = "SELECT COUNT(*) as total FROM products";
$count_stmt = $pdo->query($count_sql);
$total_products = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $items_per_page);

// Get products for current page
$products_sql = "SELECT p.*, a.name as created_by_name
                FROM products p
                LEFT JOIN admins a ON p.created_by = a.id
                ORDER BY p.created_at DESC 
                LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($products_sql);
$stmt->bindValue(':limit', (int)$items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get categories for filter
$categories_sql = "SELECT DISTINCT category FROM products ORDER BY category";
$categories_stmt = $pdo->query($categories_sql);
$categories = $categories_stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="bg-dark text-white" aria-label="Admin navigation">
            <div class="sidebar-header">
                <h3>Admin Panel</h3>
            </div>
            <ul class="list-unstyled components">
                <li>
                    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="active">
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
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Products Management</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="bi bi-plus-lg"></i> Add Product
                    </button>
                </div>

                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form class="row g-3" method="GET">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                       placeholder="Search products...">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    <?php foreach($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category); ?>"
                                            <?php echo (isset($_GET['category']) && $_GET['category'] === $category) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    <option value="discontinued" <?php echo (isset($_GET['status']) && $_GET['status'] === 'discontinued') ? 'selected' : ''; ?>>Discontinued</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Filter</button>
                                <a href="products.php" class="btn btn-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Cost</th>
                                        <th>Price</th>
                                        <th>Margin</th>
                                        <th>Stock</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($products as $product): ?>
                                    <tr>
                                        <td>
                                            <?php if ($product['images']): ?>
                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($product['images']); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                     class="product-image" style="max-width: 100px; height: auto;">
                                            <?php else: ?>
                                                <span class="text-muted">No image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                                        <td>₱<?php echo number_format($product['cost'] ?? ($product['price'] * 0.7), 2); ?></td>
                                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <?php 
                                            $cost = $product['cost'] ?? ($product['price'] * 0.7);
                                            $margin = (($product['price'] - $cost) / $cost) * 100;
                                            echo number_format($margin, 2) . '%';
                                            ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['stock'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo $product['stock']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            // Determine status badge color
                                            $statusBadgeClass = '';
                                            if ($product['status'] === 'active') {
                                                $statusBadgeClass = 'success';
                                            } else if ($product['status'] === 'inactive') {
                                                $statusBadgeClass = 'warning';
                                            } else {
                                                $statusBadgeClass = 'danger';
                                            }
                                            ?>
                                            <span class="badge bg-<?php echo $statusBadgeClass; ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['created_by_name']); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info view-product" 
                                                    data-id="<?php echo $product['id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewProductModal">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-warning edit-product"
                                                    data-id="<?php echo $product['id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editProductModal">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-product"
                                                    data-id="<?php echo $product['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteProductModal">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                        <nav aria-label="Products pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1">Previous</a>
                                </li>
                                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="products/add_product.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="category" name="category" required>
                                </div>
                                <div class="mb-3">
                                    <label for="color" class="form-label">Color</label>
                                    <select class="form-select" id="color" name="color" required>
                                        <option value="">Select Color</option>
                                        <option value="White">White</option>
                                        <option value="Beige">Beige</option>
                                        <option value="Gray">Gray</option>
                                        <option value="Black">Black</option>
                                        <option value="Brown">Brown</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Green">Green</option>
                                        <option value="Red">Red</option>
                                        <option value="Yellow">Yellow</option>
                                        <option value="Custom">Custom</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 d-none" id="custom_color" name="custom_color" placeholder="Enter custom color">
                                </div>
                                <div class="mb-3">
                                    <label for="fabric" class="form-label">Fabric Type</label>
                                    <select class="form-select" id="fabric" name="fabric" required>
                                        <option value="">Select Fabric</option>
                                        <option value="Polyester">Polyester</option>
                                        <option value="Cotton">Cotton</option>
                                        <option value="Linen">Linen</option>
                                        <option value="Silk">Silk</option>
                                        <option value="Velvet">Velvet</option>
                                        <option value="Blackout">Blackout</option>
                                        <option value="Sheer">Sheer</option>
                                        <option value="Custom">Custom</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 d-none" id="custom_fabric" name="custom_fabric" placeholder="Enter custom fabric type">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Price</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="cost" class="form-label">Cost</label>
                                    <input type="number" class="form-control" id="cost" name="cost" step="0.01" min="0" required>
                                    <small class="text-muted">Purchase cost per unit</small>
                                </div>
                                <div class="mb-3">
                                    <label for="stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="discontinued">Discontinued</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="margin_display" class="form-label">Profit Margin</label>
                                    <span id="margin_display" class="form-control"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="products/update_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="product_id" id="edit_product_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_name" class="form-label">Product Name</label>
                                    <input type="text" class="form-control" id="edit_name" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_category" class="form-label">Category</label>
                                    <input type="text" class="form-control" id="edit_category" name="category" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_color" class="form-label">Color</label>
                                    <select class="form-select" id="edit_color" name="color" required>
                                        <option value="">Select Color</option>
                                        <option value="White">White</option>
                                        <option value="Beige">Beige</option>
                                        <option value="Gray">Gray</option>
                                        <option value="Black">Black</option>
                                        <option value="Brown">Brown</option>
                                        <option value="Blue">Blue</option>
                                        <option value="Green">Green</option>
                                        <option value="Red">Red</option>
                                        <option value="Yellow">Yellow</option>
                                        <option value="Custom">Custom</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 d-none" id="edit_custom_color" name="custom_color" placeholder="Enter custom color">
                                </div>
                                <div class="mb-3">
                                    <label for="edit_fabric" class="form-label">Fabric Type</label>
                                    <select class="form-select" id="edit_fabric" name="fabric" required>
                                        <option value="">Select Fabric</option>
                                        <option value="Polyester">Polyester</option>
                                        <option value="Cotton">Cotton</option>
                                        <option value="Linen">Linen</option>
                                        <option value="Silk">Silk</option>
                                        <option value="Velvet">Velvet</option>
                                        <option value="Blackout">Blackout</option>
                                        <option value="Sheer">Sheer</option>
                                        <option value="Custom">Custom</option>
                                    </select>
                                    <input type="text" class="form-control mt-2 d-none" id="edit_custom_fabric" name="custom_fabric" placeholder="Enter custom fabric type">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_price" class="form-label">Price</label>
                                    <input type="number" class="form-control" id="edit_price" name="price" 
                                           step="0.01" min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_cost" class="form-label">Cost</label>
                                    <input type="number" class="form-control" id="edit_cost" name="cost" step="0.01" min="0" required>
                                    <small class="text-muted">Purchase cost per unit</small>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_stock" class="form-label">Stock</label>
                                    <input type="number" class="form-control" id="edit_stock" name="stock" 
                                           min="0" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select class="form-select" id="edit_status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="discontinued">Discontinued</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="edit_image" name="image" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_description" class="form-label">Description</label>
                                    <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="edit_margin" class="form-label">Profit Margin</label>
                                    <span id="edit_margin" class="form-control"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Product Modal -->
    <div class="modal fade" id="viewProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">View Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <img id="view_image" src="" alt="" class="img-fluid mb-3">
                        </div>
                        <div class="col-md-6">
                            <h4 id="view_name" aria-live="polite">Product Name</h4>
                            <p class="text-muted mb-2" id="view_category"></p>
                            <h5 class="text-primary mb-3">₱<span id="view_price"></span></h5>
                            <div class="mb-3">
                                <strong>Cost:</strong> ₱<span id="view_cost"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Profit Margin:</strong> <span id="view_margin"></span>%
                            </div>
                            <div class="mb-3">
                                <strong>Stock:</strong> <span id="view_stock"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> <span id="view_status"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Color:</strong> <span id="view_color"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Fabric:</strong> <span id="view_fabric"></span>
                            </div>
                            <div class="mb-3">
                                <strong>Description:</strong>
                                <p id="view_description"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Product Modal -->
    <div class="modal fade" id="deleteProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="products/delete_product.php" method="POST">
                    <input type="hidden" name="product_id" id="delete_product_id">
                    <div class="modal-body">
                        <p>Are you sure you want to delete <strong id="delete_product_name"></strong>?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle View Product Modal
        document.querySelectorAll('.view-product').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch(`products/get_product.php?id=${id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(product => {
                        if (product.error) {
                            throw new Error(product.error);
                        }
                        
                        document.getElementById('view_name').textContent = product.name || 'N/A';
                        document.getElementById('view_category').textContent = product.category || 'N/A';
                        document.getElementById('view_price').textContent = (product.price ? parseFloat(product.price).toFixed(2) : '0.00');
                        document.getElementById('view_cost').textContent = (product.cost ? parseFloat(product.cost).toFixed(2) : '0.00');
                        
                        const margin = product.cost && product.price ? 
                            ((product.price - product.cost) / product.cost * 100).toFixed(2) : '0.00';
                        document.getElementById('view_margin').textContent = margin;
                        
                        document.getElementById('view_stock').textContent = product.stock || '0';
                        document.getElementById('view_status').textContent = product.status || 'N/A';
                        document.getElementById('view_color').textContent = product.color || 'N/A';
                        document.getElementById('view_fabric').textContent = product.fabric || 'N/A';
                        document.getElementById('view_description').textContent = product.description || 'No description available';
                        
                        if (product.image_data) {
                            document.getElementById('view_image').src = `data:image/jpeg;base64,${product.image_data}`;
                            document.getElementById('view_image').alt = product.name;
                        } else {
                            document.getElementById('view_image').src = '../images/products/default.jpg';
                            document.getElementById('view_image').alt = 'No image available';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load product details: ' + error.message);
                    });
            });
        });

        // Handle Edit Product Modal
        document.querySelectorAll('.edit-product').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                fetch(`products/get_product.php?id=${id}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(product => {
                        if (product.error) {
                            throw new Error(product.error);
                        }

                        // Set form values
                        document.getElementById('edit_product_id').value = product.id;
                        document.getElementById('edit_name').value = product.name || '';
                        document.getElementById('edit_category').value = product.category || '';
                        document.getElementById('edit_price').value = product.price || '0';
                        document.getElementById('edit_cost').value = product.cost || '0';
                        document.getElementById('edit_stock').value = product.stock || '0';
                        document.getElementById('edit_status').value = product.status || 'active';
                        document.getElementById('edit_description').value = product.description || '';

                        // Calculate and display profit margin
                        const margin = product.cost && product.price ? 
                            ((product.price - product.cost) / product.cost * 100).toFixed(2) : '0.00';
                        document.getElementById('edit_margin').textContent = margin + '%';

                        // Handle color selection
                        const editColorSelect = document.getElementById('edit_color');
                        const editCustomColor = document.getElementById('edit_custom_color');
                        const colorExists = Array.from(editColorSelect.options).some(opt => opt.value === product.color);
                        
                        if (product.color && !colorExists) {
                            editColorSelect.value = 'Custom';
                            editCustomColor.value = product.color;
                            editCustomColor.classList.remove('d-none');
                            editCustomColor.required = true;
                        } else {
                            editColorSelect.value = product.color || '';
                            editCustomColor.classList.add('d-none');
                            editCustomColor.required = false;
                        }

                        // Handle fabric selection
                        const editFabricSelect = document.getElementById('edit_fabric');
                        const editCustomFabric = document.getElementById('edit_custom_fabric');
                        const fabricExists = Array.from(editFabricSelect.options).some(opt => opt.value === product.fabric);
                        
                        if (product.fabric && !fabricExists) {
                            editFabricSelect.value = 'Custom';
                            editCustomFabric.value = product.fabric;
                            editCustomFabric.classList.remove('d-none');
                            editCustomFabric.required = true;
                        } else {
                            editFabricSelect.value = product.fabric || '';
                            editCustomFabric.classList.add('d-none');
                            editCustomFabric.required = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to load product details: ' + error.message);
                    });
            });
        });

        // Handle Delete Product Modal
        document.querySelectorAll('.delete-product').forEach(button => {
            button.addEventListener('click', function() {
                const data = this.dataset;
                const deleteId = document.getElementById('delete_product_id');
                const deleteName = document.getElementById('delete_product_name');
                
                if (deleteId && deleteName) {
                    deleteId.value = data.id;
                    deleteName.textContent = data.name || 'this product';
                } else {
                    console.error('Delete modal elements not found');
                    alert('Error preparing delete operation');
                }
            });
        });

        // Handle custom color input
        document.getElementById('color').addEventListener('change', function() {
            const customColorInput = document.getElementById('custom_color');
            if (this.value === 'Custom') {
                customColorInput.classList.remove('d-none');
                customColorInput.required = true;
            } else {
                customColorInput.classList.add('d-none');
                customColorInput.required = false;
                customColorInput.value = '';
            }
        });

        // Handle custom fabric input
        document.getElementById('fabric').addEventListener('change', function() {
            const customFabricInput = document.getElementById('custom_fabric');
            if (this.value === 'Custom') {
                customFabricInput.classList.remove('d-none');
                customFabricInput.required = true;
            } else {
                customFabricInput.classList.add('d-none');
                customFabricInput.required = false;
                customFabricInput.value = '';
            }
        });

        // Handle custom color input in edit form
        document.getElementById('edit_color').addEventListener('change', function() {
            const customColorInput = document.getElementById('edit_custom_color');
            if (this.value === 'Custom') {
                customColorInput.classList.remove('d-none');
                customColorInput.required = true;
            } else {
                customColorInput.classList.add('d-none');
                customColorInput.required = false;
                customColorInput.value = '';
            }
        });

        // Handle custom fabric input in edit form
        document.getElementById('edit_fabric').addEventListener('change', function() {
            const customFabricInput = document.getElementById('edit_custom_fabric');
            if (this.value === 'Custom') {
                customFabricInput.classList.remove('d-none');
                customFabricInput.required = true;
            } else {
                customFabricInput.classList.add('d-none');
                customFabricInput.required = false;
                customFabricInput.value = '';
            }
        });

        // Calculate profit margin function
        function calculateMargin(price, cost) {
            if (!price || !cost || cost <= 0) return 0;
            return ((price - cost) / cost * 100).toFixed(2);
        }

        // Add real-time margin calculation for Add Product form
        document.getElementById('price').addEventListener('input', function() {
            const cost = parseFloat(document.getElementById('cost').value) || 0;
            const price = parseFloat(this.value) || 0;
            const margin = calculateMargin(price, cost);
            if (document.getElementById('margin_display')) {
                document.getElementById('margin_display').textContent = margin + '%';
            }
        });

        document.getElementById('cost').addEventListener('input', function() {
            const price = parseFloat(document.getElementById('price').value) || 0;
            const cost = parseFloat(this.value) || 0;
            const margin = calculateMargin(price, cost);
            if (document.getElementById('margin_display')) {
                document.getElementById('margin_display').textContent = margin + '%';
            }
        });

        // Add real-time margin calculation for Edit Product form
        document.getElementById('edit_price').addEventListener('input', function() {
            const cost = parseFloat(document.getElementById('edit_cost').value) || 0;
            const price = parseFloat(this.value) || 0;
            const margin = calculateMargin(price, cost);
            document.getElementById('edit_margin').textContent = margin + '%';
        });

        document.getElementById('edit_cost').addEventListener('input', function() {
            const price = parseFloat(document.getElementById('edit_price').value) || 0;
            const cost = parseFloat(this.value) || 0;
            const margin = calculateMargin(price, cost);
            document.getElementById('edit_margin').textContent = margin + '%';
        });
    </script>
</body>
</html>