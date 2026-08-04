<?php
/**
 * Admin Dashboard
 */
$adminTitle = 'Dashboard';
$adminActive = 'dashboard';
require_once __DIR__ . '/layout_head.php';

$posts = getBlogPosts();
$categories = getAllCategoryTrees();
$totalPhotos = 0;
foreach ($categories as $c) {
    $totalPhotos += $c['total'];
}

// Visit stats
$visitsFile = DATA_DIR . '/visits.json';
$totalViews = 0;
$totalVisitors = 0;
if (file_exists($visitsFile)) {
    $data = json_decode(file_get_contents($visitsFile), true);
    $totalViews = $data['totalViews'] ?? 0;
    $totalVisitors = count($data['visitors'] ?? []);
}
?>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Photos</p>
        <p class="stat-value"><?= $totalPhotos ?></p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Categories</p>
        <p class="stat-value"><?= count($categories) ?></p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Blog Posts</p>
        <p class="stat-value"><?= count($posts) ?></p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Views</p>
        <p class="stat-value"><?= number_format($totalViews) ?></p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Unique Visitors</p>
        <p class="stat-value"><?= number_format($totalVisitors) ?></p>
    </div>
</div>

<section class="panel">
    <h2>Photo Categories</h2>
    <table class="data-table">
        <thead>
            <tr><th>Category</th><th>Subcategories</th><th>Photos</th></tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td><?= e($c['label']) ?></td>
                <td><?= count($c['subcategories']) ?></td>
                <td><?= $c['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section class="panel">
    <h2>Recent Blog Posts</h2>
    <?php if (empty($posts)): ?>
        <p class="empty">No posts yet. <a href="<?= BASE_URL ?>/admin/blog-edit.php">Create one</a></p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Title</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
            <?php foreach (array_slice($posts, 0, 5) as $post): ?>
            <tr>
                <td><?= e($post['title']) ?></td>
                <td><?= e($post['pubDate']) ?></td>
                <td><a href="<?= BASE_URL ?>/admin/blog-edit.php?slug=<?= urlencode($post['slug']) ?>">Edit</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
