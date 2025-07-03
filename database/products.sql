-- Active: 1702184882038@@localhost@3306@blinds_db
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    cost DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT,
    color VARCHAR(50),
    fabric VARCHAR(50),
    images BLOB,
    status ENUM('active', 'inactive', 'discontinued') NOT NULL DEFAULT 'active',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
CREATE INDEX idx_category ON products(category);
CREATE INDEX idx_status ON products(status);
CREATE INDEX idx_created_by ON products(created_by);

ALTER Table products add COLUMN color VARCHAR(50) after description,
    add COLUMN fabric VARCHAR(50) after color;

alter table products CHANGE image_path images BLOB;

drop table products;