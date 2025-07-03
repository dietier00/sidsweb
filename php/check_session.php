<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    // Get user details from database
    $stmt = $pdo->prepare("SELECT name, email FROM customers WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'loggedIn' => true,
        'fullname' => $user['name'],
        'email' => $user['email']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?>