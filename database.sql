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

-- ─── Site Settings (key-value for hero, about, services) ──────────────
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default site content
INSERT INTO site_settings (setting_key, setting_value) VALUES
('hero_kicker', 'Photography & videography'),
('hero_heading_line1', 'People, places,'),
('hero_heading_line2', 'cultures & food.'),
('hero_sub', 'I''m Ishan Kothari — capturing the essence of people, places, cultures and food, one honest frame at a time.'),
('hero_cta_label', 'View the work'),
('hero_cta_href', '#work'),
('about_kicker', 'About'),
('about_heading', 'Hello, I''m Ishan.'),
('about_paragraphs', 'I''m a photographer and videographer with a passion for capturing the essence of people, places, cultures and food.\n\nStarting out in journalism, I developed a keen eye for detail and a deep appreciation for the power of imagery. Now I dive into various photography realms — from lively streets to serene landscapes, from product showcases to delicious food shots.\n\nI''m fueled by curiosity and driven by passion, always seeking to uncover beauty in the ordinary. Photography isn''t just my job; it''s my way of life — a constant journey of exploration and growth.'),
('about_signature', '— IK'),
('about_photo', ''),
('services_kicker', 'What I offer'),
('services_heading', 'Services'),
('services_items', '[{"title":"People","desc":"Portraits and candid moments, natural light, minimal direction."},{"title":"Places & Culture","desc":"Street, travel and documentary work on location, worldwide."},{"title":"Food & Product","desc":"Editorial-style food and product photography for brands."},{"title":"Videography","desc":"Short-form and documentary video, shot and edited end to end."}]')
ON DUPLICATE KEY UPDATE setting_key = setting_key;
