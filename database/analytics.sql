-- Active: 1702184882038@@localhost@3306@blinds_db
CREATE TABLE IF NOT EXISTS website_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_ip VARCHAR(45) NOT NULL,
    page_url VARCHAR(255) NOT NULL,
    user_agent TEXT,
    referrer_url VARCHAR(255),
    visit_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    user_id INT,
    session_id VARCHAR(64),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL UNIQUE,
    user_id INT,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add indexes for better performance
CREATE INDEX idx_visitor_ip ON website_visits(visitor_ip);
CREATE INDEX idx_visit_timestamp ON website_visits(visit_timestamp);
CREATE INDEX idx_session_visits ON website_visits(session_id);
CREATE INDEX idx_user_sessions ON user_sessions(user_id);
CREATE INDEX idx_session_activity ON user_sessions(last_activity);