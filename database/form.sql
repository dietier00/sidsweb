-- Active: 1702184882038@@localhost@3306@blinds_db

USE blinds_db;

CREATE TABLE form_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(255),
    price_range VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE table reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    TEXT text,
    avatar VARCHAR(255),
    name VARCHAR(255)
);
