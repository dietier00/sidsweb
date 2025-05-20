CREATE Table customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),    
    address TEXT,
    email VARCHAR(255),
    phone VARCHAR(255),
    username VARCHAR(255),
    password VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE table admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(255),
    password VARCHAR(255),
    role ENUM('owner', 'admin', 'agent'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE customers ADD reset_token VARCHAR(64), ADD reset_expires DATETIME;
ALTER TABLE admins ADD reset_token VARCHAR(64), ADD reset_expires DATETIME;