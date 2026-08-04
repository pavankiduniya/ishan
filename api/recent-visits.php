<?php
/**
 * Nazarbandi — Recent Visits API (paginated)
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = max(1, min(100, (int)($_GET['per_page'] ?? 20)));

try {
    $db = getDB();

    $total = (int)$db->query('SELECT COUNT(*) FROM visits')->fetchColumn();
    $totalPages = max(1, ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;

    $stmt = $db->prepare("
        SELECT path, visited_at, ip, city, region, country
        FROM visits ORDER BY visited_at DESC LIMIT ? OFFSET ?
    ");
    $stmt->execute([$perPage, $offset]);
    $rows = $stmt->fetchAll();

    $formatted = array_map(function($v) {
        $parts = array_filter([$v['city'], $v['region'], $v['country']]);
        $location = $parts ? implode(', ', $parts) : 'Local';
        return [
            'path' => $v['path'],
            'time' => date('M j, g:ia', strtotime($v['visited_at'])),
            'ip' => $v['ip'],
            'location' => $location,
        ];
    }, $rows);

    echo json_encode([
        'rows' => $formatted,
        'total' => $total,
        'totalPages' => $totalPages,
        'page' => $page,
    ]);
} catch (Exception $e) {
    echo json_encode(['rows' => [], 'total' => 0, 'totalPages' => 1, 'page' => 1]);
}
