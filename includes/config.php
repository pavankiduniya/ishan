<?php
/**
 * Nazarbandi — Site Configuration
 */

// Timezone — keep PHP and MySQL in sync
date_default_timezone_set('Asia/Kolkata');

// Base paths
define('SITE_TITLE', 'Nazarbandi');
define('SITE_ROOT', dirname(__DIR__));

// ─── Database Configuration ───────────────────────────────────────────
// Detects environment: localhost uses socket-based connection,
// production (Hostinger) uses TCP with credentials.
if (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1']) ||
    php_sapi_name() === 'cli') {
    // Local development — socket-based, no password
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'nazarbandi');
    define('DB_USER', 'pavan.bhatt');
    define('DB_PASS', '');
    define('DB_SOCKET', '/tmp/mysql.sock');
} else {
    // Production (Hostinger)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'u247231431_nazarbandi');
    define('DB_USER', 'u247231431_nazarbandi');
    define('DB_PASS', 'Kmnh#123');
    define('DB_SOCKET', '');
}

/**
 * Get a PDO database connection (singleton).
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        if (DB_SOCKET) {
            $dsn = 'mysql:unix_socket=' . DB_SOCKET . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        }
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
    return $pdo;
}

// Photos are served from the main project's public folder
// Adjust this path based on your deployment setup
define('PHOTOS_DIR', realpath(SITE_ROOT . '/../public/photos'));
define('PHOTOS_OPTIMIZED_DIR', realpath(SITE_ROOT . '/../public/photos-optimized'));
define('PHOTOS_GALLERY_DIR', realpath(SITE_ROOT . '/../public/photos-gallery'));
define('PHOTOS_THUMB_DIR', realpath(SITE_ROOT . '/../public/photos-thumb'));

// URL prefix for photos (relative to web root)
define('PHOTOS_URL', '/photos');
define('PHOTOS_OPTIMIZED_URL', '/photos-optimized');
define('PHOTOS_GALLERY_URL', '/photos-gallery');
define('PHOTOS_THUMB_URL', '/photos-thumb');

// Blog content directory (will move to DB later)
define('BLOG_DIR', SITE_ROOT . '/content/blog');

// Image extensions
define('IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);

/**
 * Base URL path — adjust based on your server setup:
 * - If serving from project root (document root = project root): '/php'
 * - If serving from php/ folder (document root = php/): ''
 */
define('BASE_URL', '');
