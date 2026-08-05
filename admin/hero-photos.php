<?php
/**
 * Admin — Hero Photos Management
 * Select which photos appear in the hero marquee.
 */
$adminTitle = 'Hero Photos';
$adminActive = 'site-content';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Add photo to hero
    if ($_POST['action'] === 'add') {
        $photoId = (int)($_POST['photo_id'] ?? 0);
        if ($photoId) {
            // Check not already added
            $check = $db->prepare('SELECT id FROM hero_photos WHERE photo_id = ?');
            $check->execute([$photoId]);
            if (!$check->fetch()) {
                $maxOrder = (int)$db->query('SELECT COALESCE(MAX(sort_order), 0) FROM hero_photos')->fetchColumn();
                $stmt = $db->prepare('INSERT INTO hero_photos (photo_id, sort_order) VALUES (?, ?)');
                $stmt->execute([$photoId, $maxOrder + 1]);
            }
            header('Location: ' . BASE_URL . '/admin/hero-photos.php?notice=Photo added to hero.');
            exit;
        }
    }

    // Remove photo from hero
    if ($_POST['action'] === 'remove') {
        $id = (int)($_POST['hero_photo_id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare('DELETE FROM hero_photos WHERE id = ?');
            $stmt->execute([$id]);
            header('Location: ' . BASE_URL . '/admin/hero-photos.php?notice=Photo removed from hero.');
            exit;
        }
    }
}

// Current hero photos
$heroPhotos = [];
try {
    $heroPhotos = $db->query('
        SELECT hp.id as hero_id, hp.sort_order, p.id as photo_id, p.file_path, p.original_name, c.name as category_name
        FROM hero_photos hp
        JOIN photos p ON hp.photo_id = p.id
        JOIN categories c ON p.category_id = c.id
        ORDER BY hp.sort_order, hp.id
    ')->fetchAll();
} catch (Exception $e) {
    // Table might not exist yet
}

// All available photos (not already in hero)
$heroPhotoIds = array_column($heroPhotos, 'photo_id');
$placeholders = !empty($heroPhotoIds) ? 'AND p.id NOT IN (' . implode(',', array_map('intval', $heroPhotoIds)) . ')' : '';
$availablePhotos = $db->query("
    SELECT p.id, p.file_path, p.original_name, c.name as category_name
    FROM photos p
    JOIN categories c ON p.category_id = c.id
    WHERE 1=1 $placeholders
    ORDER BY p.uploaded_at DESC
    LIMIT 50
")->fetchAll();

require_once __DIR__ . '/layout_head.php';
?>

<p style="color:#666; margin-bottom: 1.5rem;">Select photos to display in the homepage hero section marquee. They will scroll infinitely in the order shown below.</p>

<?php
// Auto-create table if it doesn't exist
try {
    $db->exec('CREATE TABLE IF NOT EXISTS hero_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        photo_id INT NOT NULL,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (photo_id) REFERENCES photos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
} catch (Exception $e) {}
?>

<!-- Current Hero Photos -->
<section class="dash-panel">
    <div class="dash-panel__header"><h2>Hero Photos (<?= count($heroPhotos) ?> selected)</h2></div>
    <?php if (empty($heroPhotos)): ?>
        <p class="empty">No photos selected yet. Add some from below.</p>
    <?php else: ?>
    <div class="photo-admin-grid">
        <?php foreach ($heroPhotos as $hp): ?>
        <div class="photo-admin-item">
            <img src="<?= e($hp['file_path']) ?>" alt="<?= e($hp['original_name']) ?>">
            <div class="photo-admin-meta">
                <span class="tag"><?= e($hp['category_name']) ?></span>
                <form method="POST">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="hero_photo_id" value="<?= $hp['hero_id'] ?>">
                    <button type="submit" class="icon-btn icon-btn--danger" title="Remove from hero" style="width:22px;height:22px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="12" height="12"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<!-- Available Photos to Add -->
<section class="dash-panel">
    <div class="dash-panel__header"><h2>Available Photos</h2></div>
    <?php if (empty($availablePhotos)): ?>
        <p class="empty">All photos are already in the hero, or no photos uploaded yet. <a href="<?= BASE_URL ?>/admin/photos">Upload photos</a></p>
    <?php else: ?>
    <div class="photo-admin-grid">
        <?php foreach ($availablePhotos as $ap): ?>
        <div class="photo-admin-item">
            <img src="<?= e($ap['file_path']) ?>" alt="<?= e($ap['original_name']) ?>">
            <div class="photo-admin-meta">
                <span class="tag"><?= e($ap['category_name']) ?></span>
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="photo_id" value="<?= $ap['id'] ?>">
                    <button type="submit" class="icon-btn" title="Add to hero" style="width:22px;height:22px;background:#43b794;border-color:#43b794;color:#fff;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="12" height="12"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
