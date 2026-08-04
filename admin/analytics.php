<?php
/**
 * Admin — Analytics (MySQL-backed)
 */
$adminTitle = 'Analytics';
$adminActive = 'analytics';
require_once __DIR__ . '/layout_head.php';

$db = getDB();

// Total views
$totalViews = (int)$db->query('SELECT COUNT(*) FROM visits')->fetchColumn();

// Total unique visitors
$totalVisitors = (int)$db->query('SELECT COUNT(*) FROM visitors')->fetchColumn();

// Total pages tracked
$totalPages = (int)$db->query('SELECT COUNT(DISTINCT path) FROM visits')->fetchColumn();

// Top pages (views + unique visitors per page)
$topPages = $db->query('
    SELECT path,
           COUNT(*) as views,
           COUNT(DISTINCT visitor_id) as unique_visitors
    FROM visits
    GROUP BY path
    ORDER BY views DESC
    LIMIT 20
')->fetchAll();

// Daily views — last 14 days
$chartDays = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $chartDays[$day] = 0;
}
$dailyRows = $db->query("
    SELECT DATE(visited_at) as day, COUNT(*) as cnt
    FROM visits
    WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(visited_at)
    ORDER BY day ASC
")->fetchAll();
foreach ($dailyRows as $row) {
    if (isset($chartDays[$row['day']])) {
        $chartDays[$row['day']] = (int)$row['cnt'];
    }
}
$maxViews = max(1, max($chartDays));

// Daily unique visitors — last 14 days
$dailyUnique = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $dailyUnique[$day] = 0;
}
$dailyUniqueRows = $db->query("
    SELECT DATE(visited_at) as day, COUNT(DISTINCT visitor_id) as cnt
    FROM visits
    WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(visited_at)
    ORDER BY day ASC
")->fetchAll();
foreach ($dailyUniqueRows as $row) {
    if (isset($dailyUnique[$row['day']])) {
        $dailyUnique[$row['day']] = (int)$row['cnt'];
    }
}
$maxUnique = max(1, max($dailyUnique));

// Recent visits (last 30)
$recentVisits = $db->query("
    SELECT v.path, v.visited_at, v.ip, v.city, v.region, v.country
    FROM visits v
    ORDER BY v.visited_at DESC
    LIMIT 30
")->fetchAll();

// Unique locations (for map/summary)
$locations = $db->query("
    SELECT city, region, country, lat, lon, COUNT(*) as visitors
    FROM visitors
    WHERE country != '' AND (lat != 0 OR lon != 0)
    GROUP BY city, region, country, lat, lon
    ORDER BY visitors DESC
    LIMIT 50
")->fetchAll();
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
        <p class="stat-value"><?= $totalPages ?></p>
    </div>
</div>

<!-- Daily Views Chart -->
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

<!-- Daily Unique Visitors Chart -->
<section class="panel">
    <h2>Unique Visitors — Last 14 Days</h2>
    <div class="bar-chart">
        <?php foreach ($dailyUnique as $day => $count): ?>
        <div class="bar-col">
            <div class="bar bar-alt" style="height: <?= round(($count / $maxUnique) * 100) ?>%">
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
    <?php if (empty($topPages)): ?>
        <p class="empty">No page views recorded yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Page</th><th>Views</th><th>Unique</th></tr>
        </thead>
        <tbody>
            <?php foreach ($topPages as $page): ?>
            <tr>
                <td><code><?= e($page['path']) ?></code></td>
                <td><?= number_format($page['views']) ?></td>
                <td><?= number_format($page['unique_visitors']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<!-- Visitor Locations -->
<?php if (!empty($locations)): ?>
<section class="panel">
    <h2>Visitor Locations</h2>
    <table class="data-table">
        <thead>
            <tr><th>City</th><th>Region</th><th>Country</th><th>Visitors</th></tr>
        </thead>
        <tbody>
            <?php foreach ($locations as $loc): ?>
            <tr>
                <td><?= e($loc['city'] ?: '—') ?></td>
                <td><?= e($loc['region'] ?: '—') ?></td>
                <td><?= e($loc['country']) ?></td>
                <td><?= $loc['visitors'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
<?php endif; ?>

<!-- Recent Visits -->
<section class="panel">
    <h2>Recent Visits</h2>
    <?php if (empty($recentVisits)): ?>
        <p class="empty">No visits recorded yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Page</th><th>Time</th><th>IP</th><th>Location</th></tr>
        </thead>
        <tbody>
            <?php foreach ($recentVisits as $v):
                $parts = array_filter([$v['city'], $v['region'], $v['country']]);
                $location = $parts ? implode(', ', $parts) : 'Local';
            ?>
            <tr>
                <td><code><?= e($v['path']) ?></code></td>
                <td><?= date('M j, g:ia', strtotime($v['visited_at'])) ?></td>
                <td><code><?= e($v['ip']) ?></code></td>
                <td><?= e($location) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
