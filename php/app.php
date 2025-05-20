<?php
// filepath: c:\Users\Administrator\Downloads\blinds1\php\app.php

require 'vendor/autoload.php'; // Include Composer's autoloader for MongoDB

use MongoDB\Client;

// Replace with your MongoDB connection string
$mongoURI = 'mongodb+srv://dyterljfederiz:akinlangangdb@cluster0.lgvaa4g.mongodb.net/?retryWrites=true&w=majority&appName=Cluster0';

try {
    // Connect to MongoDB
    $client = new Client($mongoURI);
    echo "Connected to MongoDB\n";

    // Select the database and collection
    $database = $client->selectDatabase('test'); // Replace 'test' with your database name
    $collection = $database->selectCollection('reviews');

    // Example: Add a new review
    $newReview = [
        'name' => 'John Doe',
        'rating' => 5,
        'message' => 'Great service!',
        'date' => new MongoDB\BSON\UTCDateTime((new DateTime())->getTimestamp() * 1000),
    ];

    $result = $collection->insertOne($newReview);
    echo "Review saved with ID: " . $result->getInsertedId() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}