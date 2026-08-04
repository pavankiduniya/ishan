<?php
/**
 * Admin — Analytics
 */
$adminTitle = 'Analytics';
$adminActive = 'analytics';
require_once __DIR__ . '/layout_head.php';

// Load visit data
$visitsFile = DATA_DIR . '/visits.json';
$data = ['totalViews' => 0, 'pages' => [], 'visitors' => [], 'recentVisits' => [], 'dailyViews' => []];
if (file_exists($visitsFile)) {
    $parsed = json_decode(file_get_contents($visitsFile), true);
    if ($parsed) $data = array_merge($data, $parsed);
}

$totalViews = $data['totalViews'];
$totalVisitors = count($data['visitors'] ?? []);
$pages = $data['pages'] ?? [];
$recentVisits = array_slice($data['recentVisits'] ?? [], 0, 30);
$dailyViews = $data['dailyViews'] ?? [];

// Sort pages by views
uasort($pages, function ($a, $b) {
    return ($b['views'] ?? 0) - ($a['views'] ?? 0);
});

// Last 14 days chart data
$chartDays = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $chartDays[$day] = $dailyViews[$day] ?? 0;
}
$maxViews = max(1, max($chartDays));
?>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Page Views</p>
        <p class="stat-value"><?= number_format($totalViews) ?></p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Unique Visitors</p>
        <p class="stat-value"><?= number_format($totalVisitors) ?></p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Pages Tracked</p>
        <p class="stat-value"><?= count($pages) ?></p>
    </div>
</div>

<!-- Daily Views Chart (simple CSS bar chart) -->
<section class="panel">
    <h2>Views — Last 14 Days</h2>
    <div class="bar-chart">
        <?php foreach ($chartDays as $day => $count): ?>
        <div class="bar-col">
            <div class="bar" style="height: <?= round(($count / $maxViews) * 100) ?>%">
                <?php if ($count > 0): ?><span class="bar-val"><?= $count ?></span><?php endif; ?>
            </div>
            <span class="bar-label"><?= date('d', strtotime($day)) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Top Pages -->
<section class="panel">
    <h2>Top Pages</h2>
    <?php if (empty($pages)): ?>
        <p class="empty">No page views recorded yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Page</th><th>Views</th><th>Unique</th></tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($pages, 0, 20, true) as $path => $stats): ?>
            <tr>
                <td><code><?= e($path) ?></code></td>
                <td><?= $stats['views'] ?? 0 ?></td>
                <td><?= count($stats['uniqueVisitors'] ?? []) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<!-- Recent Visits -->
<section class="panel">
    <h2>Recent Visits</h2>
    <?php if (empty($recentVisits)): ?>
        <p class="empty">No visits recorded yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Page</th><th>Time</th><th>Location</th></tr>
        </thead>
        <tbody>
            <?php foreach ($recentVisits as $v):
                $info = $data['visitors'][$v['visitorId']] ?? null;
                $location = 'Unknown';
                if ($info) {
                    $parts = array_filter([$info['city'] ?? '', $info['region'] ?? '', $info['country'] ?? '']);
                    $location = $parts ? implode(', ', $parts) : 'Local';
                }
            ?>
            <tr>
                <td><code><?= e($v['path']) ?></code></td>
                <td><?= date('M j, g:ia', strtotime($v['at'])) ?></td>
                <td><?= e($location) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
