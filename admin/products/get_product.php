<?php

session_start();
require_once '../../php/db_connect.php';

// Add error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Product ID required']);
    exit();
}

$product_id = (int)$_GET['id'];

try {
    $sql = "SELECT p.*, a.name as created_by_name 
            FROM products p 
            LEFT JOIN admins a ON p.created_by = a.id 
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Convert BLOB to base64 for image display
        if ($product['images']) {
            $product['image_data'] = base64_encode($product['images']);
            unset($product['images']); // Remove binary data from response
        }
        
        // Add cost to response
        if (!isset($product['cost'])) {
            $product['cost'] = $product['price'] * 0.7; // Default 30% margin if cost not set
        }
        
        header('Content-Type: application/json');
        echo json_encode($product);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
    }
} catch (PDOException $e) {
    error_log("Database error in get_product.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error occurred']);
}