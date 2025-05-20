<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "blinds_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$product_name = $_POST['product-name'];
$price_range = $_POST['price-range'];

$sql = "INSERT INTO form_data (product_name, price_range) VALUES ('$product_name', '$price_range')";

if ($conn->query($sql) === TRUE) {
    echo "Form submitted successfully!";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>