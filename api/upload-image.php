<?php
/**
 * Nazarbandi — Image Upload API for blog editor
 * Returns JSON with the uploaded image URL.
 */
require_once __DIR__ . '/../includes/config.php';

session_start();
header('Content-Type: application/json');

// Auth check
if (empty($_SESSION['admin'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
if (!in_array($ext, $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit;
}

$uploadDir = SITE_ROOT . '/uploads/blog';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

$filename = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $file['name']);
$dest = $uploadDir . '/' . $filename;

if (move_uploaded_file($file['tmp_name'], $dest)) {
    $url = '/uploads/blog/' . $filename;
    echo json_encode(['url' => $url]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
}
