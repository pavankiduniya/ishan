<?php
/**
 * Nazarbandi — Stats API
 * Returns dashboard/analytics data as JSON for live polling.
 */
require_once __DIR__ . '/../includes/config.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

try {
    $db = getDB();

    // Total stats
    $totalViews = (int)$db->query('SELECT COUNT(*) FROM visits')->fetchColumn();
    $totalVisitors = (int)$db->query('SELECT COUNT(*) FROM visitors')->fetchColumn();
    $todayViews = (int)$db->query("SELECT COUNT(*) FROM visits WHERE DATE(visited_at) = CURDATE()")->fetchColumn();
    $todayUnique = (int)$db->query("SELECT COUNT(DISTINCT visitor_id) FROM visits WHERE DATE(visited_at) = CURDATE()")->fetchColumn();
    $totalPages = (int)$db->query('SELECT COUNT(DISTINCT path) FROM visits')->fetchColumn();

    // All daily data for chart
    $dailyRows = $db->query("
        SELECT DATE(visited_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_id) as uniq
        FROM visits GROUP BY DATE(visited_at) ORDER BY day ASC
    ")->fetchAll();

    $chartData = [];
    foreach ($dailyRows as $r) {
        $chartData[] = ['day' => $r['day'], 'views' => (int)$r['views'], 'unique' => (int)$r['uniq']];
    }

    echo json_encode([
        'totalViews' => $totalViews,
        'totalVisitors' => $totalVisitors,
        'todayViews' => $todayViews,
        'todayUnique' => $todayUnique,
        'totalPages' => $totalPages,
        'avgPerDay' => count($dailyRows) > 0 ? round($totalViews / count($dailyRows)) : 0,
        'daysTracked' => count($dailyRows),
        'chartData' => $chartData,
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to load stats']);
}
