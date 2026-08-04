-- ===================================================================
-- Nazarbandi — Database Schema
-- Run this on your MySQL server to set up all required tables.
-- ===================================================================

CREATE DATABASE IF NOT EXISTS nazarbandi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE nazarbandi;

-- ─── Admin Users ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin user (password: nazarbandi)
-- Re-generate hash with: php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
INSERT INTO admin_users (username, password_hash) VALUES
    ('admin', '$2y$12$sNTzbAzErh5JQcLxUetwmO9.j9Pa/PRInnk0ZUP71Qn./AcTty7q6')
ON DUPLICATE KEY UPDATE username = username;

-- ─── Visitors (unique visitor profiles) ───────────────────────────────
CREATE TABLE IF NOT EXISTS visitors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visitor_id VARCHAR(36) NOT NULL UNIQUE,
    ip VARCHAR(45) DEFAULT '',
    country VARCHAR(100) DEFAULT '',
    region VARCHAR(100) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    lat DECIMAL(10,6) DEFAULT 0,
    lon DECIMAL(10,6) DEFAULT 0,
    first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── Visits (every page view) ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    path VARCHAR(255) NOT NULL,
    visitor_id VARCHAR(36) NOT NULL,
    ip VARCHAR(45) DEFAULT '',
    country VARCHAR(100) DEFAULT '',
    region VARCHAR(100) DEFAULT '',
    city VARCHAR(100) DEFAULT '',
    lat DECIMAL(10,6) DEFAULT 0,
    lon DECIMAL(10,6) DEFAULT 0,
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_path (path),
    INDEX idx_visitor (visitor_id),
    INDEX idx_date (visited_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
