<?php
/**
 * Nazarbandi — Visit Tracking API
 * 
 * Called by a beacon script in the footer on every page load.
 * Records the visit, resolves geo for new visitors, and returns total view count.
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');

// GET request — just return current count (for live polling)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['count'])) {
    try {
        $db = getDB();
        $stmt = $db->query('SELECT COUNT(*) as total FROM visits');
        $totalViews = (int)$stmt->fetch()['total'];
        echo json_encode(['totalViews' => $totalViews]);
    } catch (Exception $e) {
        echo json_encode(['totalViews' => 0]);
    }
    exit;
}

// Only accept POST for recording visits
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Read JSON body
$body = json_decode(file_get_contents('php://input'), true) ?: [];
$path = $body['path'] ?? '/';
$clientIp = $body['clientIp'] ?? '';

// ─── Determine real IP ────────────────────────────────────────────────
function getClientIp(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '';
}

function isPrivateIp(string $ip): bool {
    if (!$ip) return true;
    $patterns = [
        '/^127\./', '/^10\./', '/^192\.168\./',
        '/^172\.(1[6-9]|2\d|3[0-1])\./',
        '/^::1$/', '/^::ffff:127\./', '/^fc/i', '/^fd/i', '/^fe80:/i',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $ip)) return true;
    }
    return false;
}

$serverIp = getClientIp();
// Trust server-observed IP unless it's private (local dev), then use client-reported
$ip = (isPrivateIp($serverIp) && $clientIp) ? $clientIp : $serverIp;

// ─── Visitor Cookie ───────────────────────────────────────────────────
$cookieName = 'nz_vid';
$visitorId = $_COOKIE[$cookieName] ?? '';

if (!$visitorId) {
    $visitorId = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
    setcookie($cookieName, $visitorId, [
        'expires' => time() + (5 * 365 * 24 * 60 * 60), // 5 years
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

// ─── Geo Lookup (for new visitors only) ───────────────────────────────
function lookupGeo(string $ip): ?array {
    if (isPrivateIp($ip)) return null;
    $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,country,regionName,city,lat,lon';
    $ctx = stream_context_create(['http' => ['timeout' => 3]]);
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return null;
    $data = json_decode($res, true);
    if (!$data || ($data['status'] ?? '') !== 'success') return null;
    return [
        'country' => $data['country'] ?? '',
        'region' => $data['regionName'] ?? '',
        'city' => $data['city'] ?? '',
        'lat' => (float)($data['lat'] ?? 0),
        'lon' => (float)($data['lon'] ?? 0),
    ];
}

try {
    $db = getDB();

    // Check if visitor exists
    $stmt = $db->prepare('SELECT id, ip FROM visitors WHERE visitor_id = ? LIMIT 1');
    $stmt->execute([$visitorId]);
    $visitor = $stmt->fetch();

    $geo = null;

    if (!$visitor) {
        // New visitor — geo lookup + insert
        $geo = lookupGeo($ip);
        $stmt = $db->prepare('INSERT INTO visitors (visitor_id, ip, country, region, city, lat, lon) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $visitorId, $ip,
            $geo['country'] ?? '', $geo['region'] ?? '', $geo['city'] ?? '',
            $geo['lat'] ?? 0, $geo['lon'] ?? 0,
        ]);
    } elseif (isPrivateIp($visitor['ip']) && !isPrivateIp($ip)) {
        // Previously had a private IP, now we have a real one — update geo
        $geo = lookupGeo($ip);
        $stmt = $db->prepare('UPDATE visitors SET ip = ?, country = ?, region = ?, city = ?, lat = ?, lon = ? WHERE visitor_id = ?');
        $stmt->execute([
            $ip, $geo['country'] ?? '', $geo['region'] ?? '', $geo['city'] ?? '',
            $geo['lat'] ?? 0, $geo['lon'] ?? 0, $visitorId,
        ]);
    } else {
        // Update last_seen
        $stmt = $db->prepare('UPDATE visitors SET last_seen = NOW() WHERE visitor_id = ?');
        $stmt->execute([$visitorId]);
    }

    // Record the visit
    $geoForVisit = $geo ?: ['country' => '', 'region' => '', 'city' => '', 'lat' => 0, 'lon' => 0];
    if (!$geo && $visitor) {
        // Use stored geo from visitor record
        $stmt2 = $db->prepare('SELECT country, region, city, lat, lon FROM visitors WHERE visitor_id = ? LIMIT 1');
        $stmt2->execute([$visitorId]);
        $stored = $stmt2->fetch();
        if ($stored) $geoForVisit = $stored;
    }

    $stmt = $db->prepare('INSERT INTO visits (path, visitor_id, ip, country, region, city, lat, lon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $path, $visitorId, $ip,
        $geoForVisit['country'], $geoForVisit['region'], $geoForVisit['city'],
        $geoForVisit['lat'], $geoForVisit['lon'],
    ]);

    // Get total views
    $stmt = $db->query('SELECT COUNT(*) as total FROM visits');
    $totalViews = (int)$stmt->fetch()['total'];

    echo json_encode(['totalViews' => $totalViews]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Tracking failed']);
}
