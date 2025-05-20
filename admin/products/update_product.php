<?php

use PDO;

session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    
    // Handle color selection
    $color = $_POST['color'];
    if ($color === 'Custom' && isset($_POST['custom_color'])) {
        $color = $_POST['custom_color'];
    }
    
    // Handle fabric selection
    $fabric = $_POST['fabric'];
    if ($fabric === 'Custom' && isset($_POST['custom_fabric'])) {
        $fabric = $_POST['custom_fabric'];
    }

    try {
        // Start with basic product data
        $sql = "UPDATE products SET name = ?, category = ?, color = ?, "
             . "fabric = ?, price = ?, cost = ?, stock = ?, status = ?, description = ?, "
             . "updated_at = NOW()";
        $params = [
            $name,
            $category,
            $color,
            $fabric,
            $price,
            $cost,
            $stock,
            $status,
            $description
        ];

        // If a new image is uploaded, add it to the update
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            
            if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
                header("Location: ../products.php");
                exit();
            }
            
            // Add image update to SQL
            $sql .= ", images = ?";
            $params[] = file_get_contents($_FILES["image"]["tmp_name"]);
        }

        // Complete the SQL statement
        $sql .= " WHERE id = ?";
        $params[] = $product_id;

        // Execute the update
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $_SESSION['success'] = "Product updated successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating product: " . $e->getMessage();
    }

    header("Location: ../products.php");
    exit();
}