-- Active: 1702184882038@@localhost@3306@blinds_db
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied', 'archived') NOT NULL DEFAULT 'unread',
    assigned_to INT,
    read_at TIMESTAMP NULL,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    facebook VARCHAR(100) DEFAULT NULL,
    viber VARCHAR(50) DEFAULT NULL,
    preferred_contact ENUM('email', 'facebook', 'viber') NOT NULL DEFAULT 'email',
    FOREIGN KEY (assigned_to) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
CREATE INDEX idx_message_status ON contact_messages(status);
CREATE INDEX idx_message_email ON contact_messages(email);
CREATE INDEX idx_message_assigned ON contact_messages(assigned_to);
CREATE INDEX idx_message_created ON contact_messages(created_at);