<?php
session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $id = $_POST['product_id'];
    
    try {
        // Get image path before deleting the product
        $sql = "SELECT image_path FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the product
        $sql = "DELETE FROM products WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        
        if ($stmt->execute([$id])) {
            // Delete the product image file
            if ($product && $product['image_path']) {
                $image_path = str_replace('../', '../../', $product['image_path']);
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            $_SESSION['success'] = "Product deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting product";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting product: " . $e->getMessage();
    }
} else {
    $_SESSION['error'] = "Invalid request";
}

header("Location: ../products.php");
exit();