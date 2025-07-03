-- Notification Logs Table
CREATE TABLE IF NOT EXISTS notification_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    channel VARCHAR(20) NOT NULL,
    notification_type VARCHAR(50) NOT NULL,
    status VARCHAR(20) NOT NULL,
    response_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_order_channel (order_id, channel),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notification Errors Table
CREATE TABLE IF NOT EXISTS notification_errors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    notification_type VARCHAR(50) NOT NULL,
    order_id INT NOT NULL,
    error_message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Webhook Endpoints Table
CREATE TABLE IF NOT EXISTS webhook_endpoints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    url VARCHAR(255) NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    secret_key VARCHAR(255) NOT NULL,
    description TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_url_event (url, event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Webhook Logs Table
CREATE TABLE IF NOT EXISTS webhook_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    endpoint_id INT NOT NULL,
    event_type VARCHAR(50) NOT NULL,
    status_code INT NOT NULL,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (endpoint_id) REFERENCES webhook_endpoints(id),
    INDEX idx_endpoint_event (endpoint_id, event_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default webhook events
INSERT INTO webhook_endpoints (url, event_type, secret_key, description, active)
VALUES 
    ('https://example.com/webhook/orders', 'order.created', SHA2(UUID(), 256), 'Order creation notifications', false),
    ('https://example.com/webhook/payments', 'payment.confirmed', SHA2(UUID(), 256), 'Payment confirmation notifications', false),
    ('https://example.com/webhook/all', 'all', SHA2(UUID(), 256), 'All events notifications', false);