<?php
session_start();
require_once '../php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-style.css">
    <style>
        .product-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 4px;
        }
        .timeline-item {
            border-left: 2px solid #dee2e6;
            margin-bottom: 1rem;
            padding-left: 1rem;
            position: relative;
        }
        .timeline-item::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #0d6efd;
            position: absolute;
            left: -6px;
            top: 6px;
        }
        .transaction-list .transaction-item {
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        .transaction-list .transaction-item:last-child {
            border-bottom: none;
        }
        .refresh-controls {
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .data-tooltip {
            cursor: help;
        }
        .timeline-item .badge {
            font-size: 0.75rem;
        }
        .transaction-item:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transition: background-color 0.2s ease;
        }
        .product-performance td {
            vertical-align: middle;
        }
        .status-badge {
            min-width: 80px;
            text-align: center;
        }
        .metric-card {
            transition: transform 0.2s ease;
        }
        .metric-card:hover {
            transform: translateY(-5px);
        }
        .chart-container {
            position: relative;
            min-height: 300px;
        }
        @media (max-width: 768px) {
            .refresh-controls {
                position: fixed;
                bottom: 10px;
                right: 10px;
                max-width: calc(100% - 20px);
            }
        }

        .notification-dot {
            width: 10px;
            height: 10px;
            background-color: #dc3545;
            border-radius: 50%;
            position: absolute;
            top: 10px;
            right: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 6px rgba(220, 53, 69, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
            }
        }

        .messages.metric-card {
            position: relative;
            overflow: hidden;
        }

        .messages .card-footer {
            background: rgba(0, 0, 0, 0.1);
            padding: 0.5rem 1rem;
            transition: background-color 0.2s ease;
        }

        .messages .card-footer:hover {
            background: rgba(0, 0, 0, 0.2);
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
                <li class="active">
                    <a href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li>
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
            <!-- Top Section - Metrics -->
            <div class="container-fluid mb-4">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="mb-0">Dashboard Overview</h2>
                            <div class="refresh-status d-none d-md-flex align-items-center">
                                <small class="text-muted me-2">Auto-refresh:</small>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="autoRefreshToggle" checked>
                                    <label class="form-check-label" for="autoRefreshToggle">Enabled</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-primary text-white weekly-sales metric-card">
                            <div class="card-body">
                                <h5>Monthly Sales</h5>
                                <h3>₱0</h3>
                                <p class="growth-text mb-0"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white weekly-profit metric-card">
                            <div class="card-body">
                                <h5>Monthly Profit</h5>
                                <h3>₱0</h3>
                                <p class="profit-growth-text mb-0"></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark purchase-orders metric-card">
                            <div class="card-body">
                                <h5>Purchase Orders</h5>
                                <h3>0</h3>
                                <p>Pending orders</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white messages metric-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5>Messages</h5>
                                        <h3>0</h3>
                                        <p class="mb-0">Unread messages</p>
                                    </div>
                                    <div class="notification-dot d-none"></div>
                                </div>
                            </div>
                            <a href="messages.php" class="card-footer text-white text-decoration-none d-flex align-items-center">
                                <span>View Messages</span>
                                <i class="bi bi-chevron-right ms-auto"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Product Overview Section -->
                <div class="row g-4 mb-4">
                    <div class="col-xl-3 col-sm-6">
                        <div class="card total-products">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 rounded-circle bg-primary bg-opacity-10 p-3">
                                        <i class="bi bi-box fs-4 text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Total Products</h6>
                                        <h4 class="mb-0 value">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card active-products">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 rounded-circle bg-success bg-opacity-10 p-3">
                                        <i class="bi bi-check-circle fs-4 text-success"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Active Products</h6>
                                        <h4 class="mb-0 value">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card low-stock">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 rounded-circle bg-warning bg-opacity-10 p-3">
                                        <i class="bi bi-exclamation-triangle fs-4 text-warning"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Low Stock Items</h6>
                                        <h4 class="mb-0 value">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6">
                        <div class="card total-stock">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 rounded-circle bg-info bg-opacity-10 p-3">
                                        <i class="bi bi-boxes fs-4 text-info"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-1">Total Stock</h6>
                                        <h4 class="mb-0 value">0</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Overview Chart -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="card-title mb-0">Product Overview</h5>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="products.php">View All Products</a></li>
                                    <li><a class="dropdown-item" href="products/add_product.php">Add New Product</a></li>
                                </ul>
                            </div>
                        </div>
                        <div style="height: 300px;">
                            <canvas id="productsChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Order Timeline and Recent Transactions -->
                <div class="row">
                    <!-- Order Timeline -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Order Timeline</h5>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-secondary" onclick="filterTimeline('today')">Today</button>
                                    <button class="btn btn-outline-secondary active" onclick="filterTimeline('all')">All</button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <!-- Timeline items will be dynamically populated -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">Recent Transactions</h5>
                            </div>
                            <div class="card-body">
                                <div class="transaction-list">
                                    <!-- Transaction items will be dynamically populated -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Performance -->
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Top Products</h5>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        This Month
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="updateProductMetrics('month')">This Month</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateProductMetrics('quarter')">This Quarter</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="updateProductMetrics('year')">This Year</a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover product-performance">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th class="text-center">Sales</th>
                                                <th class="text-end">Revenue</th>
                                                <th>Growth</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Product data will be dynamically populated -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Refresh Status Toast -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="refreshToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <i class="bi bi-info-circle text-primary me-2"></i>
                <strong class="me-auto">Dashboard Update</strong>
                <small class="text-muted">just now</small>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Dashboard data has been refreshed.
            </div>
        </div>
    </div>

    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html>