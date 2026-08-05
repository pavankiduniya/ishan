<?php
/**
 * Admin Dashboard
 */
$adminTitle = 'Dashboard';
$adminActive = 'dashboard';
require_once __DIR__ . '/layout_head.php';

$db = getDB();

$posts = getBlogPosts();

// Photo stats from DB
$totalPhotos = (int)$db->query('SELECT COUNT(*) FROM photos')->fetchColumn();
$dbCategories = $db->query('SELECT c.*, (SELECT COUNT(*) FROM photos WHERE category_id = c.id) as photo_count, (SELECT COUNT(*) FROM categories WHERE parent_id = c.id) as sub_count FROM categories c WHERE c.parent_id IS NULL ORDER BY c.name')->fetchAll();

// Visit stats from MySQL
$totalViews = (int)$db->query('SELECT COUNT(*) FROM visits')->fetchColumn();
$totalVisitors = (int)$db->query('SELECT COUNT(*) FROM visitors')->fetchColumn();
$todayViews = (int)$db->query("SELECT COUNT(*) FROM visits WHERE DATE(visited_at) = CURDATE()")->fetchColumn();
$todayUnique = (int)$db->query("SELECT COUNT(DISTINCT visitor_id) FROM visits WHERE DATE(visited_at) = CURDATE()")->fetchColumn();

// Last 14 days for chart
$chartData = [];
for ($i = 13; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $chartData[$day] = ['views' => 0, 'unique' => 0];
}
$rows = $db->query("
    SELECT DATE(visited_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_id) as uniq
    FROM visits WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(visited_at) ORDER BY day
")->fetchAll();
foreach ($rows as $r) {
    if (isset($chartData[$r['day']])) {
        $chartData[$r['day']] = ['views' => (int)$r['views'], 'unique' => (int)$r['uniq']];
    }
}
$maxChart = max(1, max(array_column($chartData, 'views')));

// Recent visits
$recentVisits = $db->query("
    SELECT path, visited_at, city, country FROM visits ORDER BY visited_at DESC LIMIT 8
")->fetchAll();
?>

<!-- Stat Cards -->
<div class="dash-stats">
    <div class="dash-card dash-card--blue">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16M8 19v-6m4.5 6V9m4.5 10v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value" id="dash-views"><?= number_format($totalViews) ?></p>
            <p class="dash-card__label">Total Page Views</p>
        </div>
        <p class="dash-card__sub">Today: <span id="dash-today-views"><?= $todayViews ?></span></p>
    </div>

    <div class="dash-card dash-card--green">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm11 0v-2m0 6v-2m0 6v-2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value" id="dash-visitors"><?= number_format($totalVisitors) ?></p>
            <p class="dash-card__label">Unique Visitors</p>
        </div>
        <p class="dash-card__sub">Today: <span id="dash-today-unique"><?= $todayUnique ?></span></p>
    </div>

    <div class="dash-card dash-card--purple">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm2 12 4.5-5.5 3 3.5 2.5-3L19 17" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= $totalPhotos ?></p>
            <p class="dash-card__label">Total Photos</p>
        </div>
        <p class="dash-card__sub"><?= count($dbCategories) ?> categories</p>
    </div>

    <div class="dash-card dash-card--orange">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 4h9l5 5v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm8 0v5h5M9 12h6M9 16h6" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= count($posts) ?></p>
            <p class="dash-card__label">Blog Posts</p>
        </div>
        <p class="dash-card__sub"><a href="<?= BASE_URL ?>/admin/blog">Manage</a></p>
    </div>
</div>

<!-- Traffic Chart -->
<div class="dash-row">
    <section class="dash-panel dash-panel--wide">
        <div class="dash-panel__header">
            <h2>Traffic Overview</h2>
            <div class="dash-legend">
                <span class="dash-legend__item"><span class="dot dot--blue"></span> Views</span>
                <span class="dash-legend__item"><span class="dot dot--green"></span> Unique</span>
            </div>
        </div>
        <div class="dash-chart">
            <?php foreach ($chartData as $day => $d): ?>
            <div class="dash-chart__col">
                <div class="dash-chart__bars">
                    <div class="dash-chart__bar dash-chart__bar--blue" style="height: <?= round(($d['views'] / $maxChart) * 100) ?>%" title="<?= $d['views'] ?> views"></div>
                    <div class="dash-chart__bar dash-chart__bar--green" style="height: <?= $d['unique'] ? round(($d['unique'] / $maxChart) * 100) : 0 ?>%" title="<?= $d['unique'] ?> unique"></div>
                </div>
                <span class="dash-chart__label"><?= date('d', strtotime($day)) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Quick Stats Side Panel -->
    <section class="dash-panel dash-panel--side">
        <div class="dash-panel__header">
            <h2>Quick Stats</h2>
        </div>
        <div class="quick-stats">
            <div class="quick-stat">
                <span class="quick-stat__label">Avg. Views/Day</span>
                <span class="quick-stat__value"><?= $totalViews > 0 ? round($totalViews / 14) : 0 ?></span>
            </div>
            <div class="quick-stat">
                <span class="quick-stat__label">Categories</span>
                <span class="quick-stat__value"><?= count($categories) ?></span>
            </div>
            <div class="quick-stat">
                <span class="quick-stat__label">Subcategories</span>
                <span class="quick-stat__value"><?php
                    $subCount = 0;
                    foreach ($categories as $c) $subCount += count($c['subcategories']);
                    echo $subCount;
                ?></span>
            </div>
            <div class="quick-stat">
                <span class="quick-stat__label">Photos/Category</span>
                <span class="quick-stat__value"><?= count($categories) > 0 ? round($totalPhotos / count($categories)) : 0 ?></span>
            </div>
        </div>
    </section>
</div>

<!-- Tables Row -->
<div class="dash-row">
    <section class="dash-panel">
        <div class="dash-panel__header">
            <h2>Recent Visits</h2>
            <a href="<?= BASE_URL ?>/admin/analytics" class="dash-panel__link">View all →</a>
        </div>
        <?php if (empty($recentVisits)): ?>
            <p class="empty">No visits yet.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Page</th><th>Time</th><th>Location</th></tr>
            </thead>
            <tbody>
                <?php foreach ($recentVisits as $v):
                    $loc = array_filter([$v['city'], $v['country']]);
                    $location = $loc ? implode(', ', $loc) : 'Local';
                ?>
                <tr>
                    <td><code><?= e($v['path']) ?></code></td>
                    <td><?= date('M j, g:ia', strtotime($v['visited_at'])) ?></td>
                    <td><?= e($location) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <section class="dash-panel">
        <div class="dash-panel__header">
            <h2>Photo Categories</h2>
            <a href="<?= BASE_URL ?>/admin/photos" class="dash-panel__link">Manage →</a>
        </div>
        <?php if (empty($dbCategories)): ?>
            <p class="empty">No categories yet. <a href="<?= BASE_URL ?>/admin/photos">Create one</a></p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr><th>Category</th><th>Subcategories</th><th>Photos</th></tr>
            </thead>
            <tbody>
                <?php foreach ($dbCategories as $c): ?>
                <tr>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td><?= $c['sub_count'] ?></td>
                    <td><?= $c['photo_count'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>
</div>

<!-- Recent Posts -->
<section class="dash-panel">
    <div class="dash-panel__header">
        <h2>Recent Blog Posts</h2>
        <a href="<?= BASE_URL ?>/admin/blog-edit" class="btn btn-primary btn-sm">+ New Post</a>
    </div>
    <?php if (empty($posts)): ?>
        <p class="empty">No posts yet. <a href="<?= BASE_URL ?>/admin/blog-edit">Create one</a></p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Date</th><th>Description</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($posts, 0, 5) as $post): ?>
            <tr>
                <td><strong><?= e($post['title']) ?></strong></td>
                <td><?= e($post['pubDate']) ?></td>
                <td><?= e(substr($post['description'] ?? '', 0, 50)) ?></td>
                <td><a href="<?= BASE_URL ?>/admin/blog-edit?slug=<?= urlencode($post['slug']) ?>" class="action">Edit</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<!-- Disk Usage -->
<?php
// Get disk space
$diskTotal = @disk_total_space(SITE_ROOT);
$diskFree = @disk_free_space(SITE_ROOT);
$diskUsed = $diskTotal ? $diskTotal - $diskFree : 0;
$diskPercent = $diskTotal ? round(($diskUsed / $diskTotal) * 100, 1) : 0;

function formatBytes($bytes): string {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
    if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
    if ($bytes >= 1024) return round($bytes / 1024, 2) . ' KB';
    return $bytes . ' B';
}

// Get top 5 folders by size
function getDirSize(string $dir): int {
    $size = 0;
    if (!is_dir($dir)) return 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
        $size += $file->getSize();
    }
    return $size;
}

$topFolders = [];
$scanDir = SITE_ROOT;
foreach (scandir($scanDir) as $entry) {
    if ($entry === '.' || $entry === '..' || $entry === '.git') continue;
    $path = $scanDir . '/' . $entry;
    if (is_dir($path)) {
        $topFolders[] = ['name' => $entry, 'size' => getDirSize($path)];
    }
}
usort($topFolders, function($a, $b) { return $b['size'] - $a['size']; });
$topFolders = array_slice($topFolders, 0, 5);
?>

<div class="dash-row">
    <section class="dash-panel">
        <div class="dash-panel__header"><h2>Disk Usage</h2></div>
        <div class="disk-bar-wrap">
            <div class="disk-bar">
                <div class="disk-bar__fill" style="width: <?= $diskPercent ?>%"></div>
            </div>
            <div class="disk-bar-info">
                <span><?= formatBytes($diskUsed) ?> used</span>
                <span><?= formatBytes($diskFree) ?> free</span>
                <span><?= formatBytes($diskTotal) ?> total</span>
            </div>
        </div>
    </section>

    <section class="dash-panel">
        <div class="dash-panel__header"><h2>Top 5 Folders</h2></div>
        <table class="data-table">
            <thead><tr><th>Folder</th><th>Size</th></tr></thead>
            <tbody>
                <?php foreach ($topFolders as $f): ?>
                <tr>
                    <td><code><?= e($f['name']) ?>/</code></td>
                    <td><?= formatBytes($f['size']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($topFolders)): ?>
                <tr><td colspan="2" class="empty">No folders found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
// Dashboard auto-refresh every 10s
(function() {
    function refresh() {
        fetch('<?= BASE_URL ?>/api/stats.php', { cache: 'no-store' })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (!data || data.error) return;
                var el;
                el = document.getElementById('dash-views'); if (el) el.textContent = data.totalViews.toLocaleString();
                el = document.getElementById('dash-visitors'); if (el) el.textContent = data.totalVisitors.toLocaleString();
                el = document.getElementById('dash-today-views'); if (el) el.textContent = data.todayViews;
                el = document.getElementById('dash-today-unique'); if (el) el.textContent = data.todayUnique;
            })
            .catch(function() {});
    }
    setInterval(refresh, 10000);
})();
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
