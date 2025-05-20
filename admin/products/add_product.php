<?php

use PDO;

session_start();
require_once '../../php/db_connect.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $cost = $_POST['cost'];
    $stock = $_POST['stock'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    
    // Handle color selection
    $color = $_POST['color'];
    if ($color === 'Custom') {
        $color = $_POST['custom_color'];
    }
    
    // Handle fabric selection
    $fabric = $_POST['fabric'];
    if ($fabric === 'Custom') {
        $fabric = $_POST['custom_fabric'];
    }

    // Handle image upload
    $image_data = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        
        if (!in_array($_FILES["image"]["type"], $allowed_types)) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            header("Location: ../products.php");
            exit();
        }
        
        // Read image file content
        $image_data = file_get_contents($_FILES["image"]["tmp_name"]);
    }

    try {
        // Insert product into database
        $sql = "INSERT INTO products (name, category, color, fabric, price, cost, stock, status, "
             . "description, images, created_by, created_at) "
             . "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $name,
            $category,
            $color,
            $fabric,
            $price,
            $cost,
            $stock,
            $status,
            $description,
            $image_data,
            $_SESSION['admin_id'] // Using admin_id from session
        ]);

        $_SESSION['success'] = "Product added successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error adding product: " . $e->getMessage();
    }

    header("Location: ../products.php");
    exit();
}