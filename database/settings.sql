-- Active: 1702184882038@@localhost@3306@blinds_db
CREATE TABLE IF NOT EXISTS site_settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT,
    setting_type ENUM('text', 'number', 'boolean', 'json') NOT NULL DEFAULT 'text',
    category VARCHAR(50) NOT NULL DEFAULT 'general',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO site_settings (setting_key, setting_value, setting_type, category, description) VALUES
('site_name', 'Blinds Interior', 'text', 'general', 'Website name'),
('site_email', 'contact@blinds.com', 'text', 'general', 'Contact email address'),
('maintenance_mode', '0', 'boolean', 'general', 'Site maintenance mode'),
('login_attempts', '5', 'number', 'security', 'Maximum login attempts before account lock'),
('session_timeout', '30', 'number', 'security', 'Session timeout in minutes'),
('password_expiry', '90', 'number', 'security', 'Password expiry in days')
ON DUPLICATE KEY UPDATE setting_key=setting_key;