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
    // Resize if too large (max 1600px width)
    resizeImage($dest, 1600);

    $url = '/uploads/blog/' . $filename;
    echo json_encode(['url' => $url]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save file']);
}

/**
 * Resize image if width exceeds max, maintaining aspect ratio.
 * Uses GD library (available on most PHP hosts including Hostinger).
 */
function resizeImage(string $path, int $maxWidth): void {
    $info = @getimagesize($path);
    if (!$info) return;

    list($width, $height, $type) = $info;
    if ($width <= $maxWidth) return; // Already small enough

    $ratio = $maxWidth / $width;
    $newWidth = $maxWidth;
    $newHeight = (int)round($height * $ratio);

    // Create source image
    switch ($type) {
        case IMAGETYPE_JPEG: $src = @imagecreatefromjpeg($path); break;
        case IMAGETYPE_PNG: $src = @imagecreatefrompng($path); break;
        case IMAGETYPE_WEBP: $src = @imagecreatefromwebp($path); break;
        case IMAGETYPE_GIF: $src = @imagecreatefromgif($path); break;
        default: return;
    }
    if (!$src) return;

    // Create resized image
    $dst = imagecreatetruecolor($newWidth, $newHeight);

    // Preserve transparency for PNG/WebP
    if ($type === IMAGETYPE_PNG || $type === IMAGETYPE_WEBP) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save back
    switch ($type) {
        case IMAGETYPE_JPEG: imagejpeg($dst, $path, 85); break;
        case IMAGETYPE_PNG: imagepng($dst, $path, 8); break;
        case IMAGETYPE_WEBP: imagewebp($dst, $path, 85); break;
        case IMAGETYPE_GIF: imagegif($dst, $path); break;
    }

    imagedestroy($src);
    imagedestroy($dst);
}
