<?php
/**
 * Nazarbandi — Gallery Page (DB-backed, filterable by category)
 */
$pageTitle = 'Gallery';

require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Get all categories
$allCats = $db->query('SELECT id, name, slug, parent_id FROM categories ORDER BY sort_order, name')->fetchAll();
$parentCats = array_values(array_filter($allCats, function($c) { return $c['parent_id'] === null || $c['parent_id'] === ''; }));

// Check if filtering by category
$activeCatSlug = $_GET['cat'] ?? '';
$activeCat = null;
if ($activeCatSlug) {
    foreach ($allCats as $c) {
        if ($c['slug'] === $activeCatSlug) { $activeCat = $c; break; }
    }
}

// If no category selected, default to first parent category
if (!$activeCat && !empty($parentCats)) {
    $first = reset($parentCats);
    $activeCat = $first;
    $activeCatSlug = $first['slug'];
}

// Fetch photos based on filter
if ($activeCat) {
    // If it's a parent category, get all photos in it + its subcategories
    if ($activeCat['parent_id'] === null || $activeCat['parent_id'] === '' || $activeCat['parent_id'] === '0') {
        $childIds = [$activeCat['id']];
        foreach ($allCats as $c) {
            if ((int)$c['parent_id'] === (int)$activeCat['id']) $childIds[] = $c['id'];
        }
        $placeholders = implode(',', array_fill(0, count($childIds), '?'));
        $stmt = $db->prepare("SELECT * FROM photos WHERE category_id IN ($placeholders) ORDER BY sort_order, uploaded_at DESC");
        $stmt->execute($childIds);
    } else {
        // Subcategory — just its photos
        $stmt = $db->prepare("SELECT * FROM photos WHERE category_id = ? ORDER BY sort_order, uploaded_at DESC");
        $stmt->execute([$activeCat['id']]);
    }
    $photos = $stmt->fetchAll();
    $pageTitle = $activeCat['name'] . ' — Gallery';
} else {
    $photos = [];
}

$totalPhotos = (int)$db->query('SELECT COUNT(*) FROM photos')->fetchColumn();

// Build sidebar counts
$photoCounts = [];
$rows = $db->query('SELECT category_id, COUNT(*) as cnt FROM photos GROUP BY category_id')->fetchAll();
foreach ($rows as $r) $photoCounts[$r['category_id']] = (int)$r['cnt'];
?>

<div class="gallery-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <p class="kicker">Categories</p>
        <p class="total"><?= $totalPhotos ?> photos</p>

        <nav class="category-nav">
            <?php foreach ($parentCats as $p):
                $parentCount = $photoCounts[$p['id']] ?? 0;
                $children = array_filter($allCats, function($c) use ($p) { return (int)$c['parent_id'] === (int)$p['id']; });
                foreach ($children as $ch) $parentCount += ($photoCounts[$ch['id']] ?? 0);
            ?>
            <div class="category-group">
                <a href="<?= BASE_URL ?>/gallery?cat=<?= e($p['slug']) ?>" class="category-link <?= $activeCatSlug === $p['slug'] ? 'active' : '' ?>">
                    <?= e($p['name']) ?> <span>(<?= $parentCount ?>)</span>
                </a>
                <?php if (!empty($children)): ?>
                <ul>
                    <?php foreach ($children as $sub): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/gallery?cat=<?= e($sub['slug']) ?>" class="<?= $activeCatSlug === $sub['slug'] ? 'active' : '' ?>">
                            <?= e($sub['name']) ?> <span>(<?= $photoCounts[$sub['id']] ?? 0 ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Gallery Content -->
    <main class="gallery-page">
        <header class="heading">
            <p class="kicker"><?= $activeCat ? e($activeCat['name']) : 'Full gallery' ?></p>
            <h1><?= $activeCat ? e($activeCat['name']) : 'All the frames' ?></h1>
            <p class="sub"><?= $activeCat ? 'Showing ' . count($photos) . ' photos' : 'Everything so far, sorted by category.' ?></p>
        </header>

        <?php if (empty($photos)): ?>
            <p class="empty">No photos yet.</p>
        <?php else: ?>
            <?php $watermark = getSiteContent()['watermark'] ?? ''; ?>
            <div class="photo-grid">
                <?php foreach ($photos as $p): ?>
                <figure>
                    <img src="<?= e($p['file_path']) ?>" data-full="<?= e($p['file_path']) ?>" alt="<?= e($p['original_name']) ?>" loading="lazy">
                    <?php if ($watermark): ?>
                    <span class="watermark"><?= e($watermark) ?></span>
                    <?php endif; ?>
                </figure>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
