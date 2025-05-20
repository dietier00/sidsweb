<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id']) && isset($_SESSION['fullname'])) {
    echo json_encode([
        'loggedIn' => true,
        'user_id' => $_SESSION['user_id'],
        'fullname' => $_SESSION['fullname']
    ]);
} else {
    echo json_encode([
        'loggedIn' => false
    ]);
}
?>