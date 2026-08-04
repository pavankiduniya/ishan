<?php
/**
 * Nazarbandi — Site Configuration
 */

// Base paths
define('SITE_TITLE', 'Nazarbandi');
define('SITE_ROOT', dirname(__DIR__));

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

// Blog content directory
define('BLOG_DIR', SITE_ROOT . '/content/blog');

// Site content data file
define('DATA_DIR', SITE_ROOT . '/data');
define('SITE_CONTENT_FILE', DATA_DIR . '/site-content.json');

// Image extensions
define('IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif']);

/**
 * Base URL path — adjust based on your server setup:
 * - If serving from project root (document root = project root): '/php'
 * - If serving from php/ folder (document root = php/): ''
 */
define('BASE_URL', '');
