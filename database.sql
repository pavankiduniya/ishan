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
