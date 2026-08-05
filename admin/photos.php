<?php
/**
 * Admin — Photos Management (DB-backed)
 */
$adminTitle = 'Photos';
$adminActive = 'photos';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$db = getDB();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Create category
    if ($_POST['action'] === 'create_category') {
        $name = trim($_POST['name'] ?? '');
        $parentId = ($_POST['parent_id'] ?? '') !== '' ? (int)$_POST['parent_id'] : null;
        if ($name) {
            $slug = slugify($name);
            if ($slug) {
                // Ensure unique slug
                $existing = $db->prepare('SELECT id FROM categories WHERE slug = ?');
                $existing->execute([$slug]);
                if ($existing->fetch()) {
                    $slug .= '-' . time();
                }
                $stmt = $db->prepare('INSERT INTO categories (name, slug, parent_id) VALUES (?, ?, ?)');
                $stmt->execute([$name, $slug, $parentId]);
                header('Location: ' . BASE_URL . '/admin/photos.php?notice=Category "' . urlencode($name) . '" created.');
                exit;
            }
        }
        header('Location: ' . BASE_URL . '/admin/photos.php?error=Invalid category name.');
        exit;
    }

    // Delete category
    if ($_POST['action'] === 'delete_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        if ($id) {
            $stmt = $db->prepare('DELETE FROM categories WHERE id = ?');
            $stmt->execute([$id]);
            header('Location: ' . BASE_URL . '/admin/photos.php?notice=Category deleted.');
            exit;
        }
    }

    // Rename category
    if ($_POST['action'] === 'rename_category') {
        $id = (int)($_POST['category_id'] ?? 0);
        $newName = trim($_POST['new_name'] ?? '');
        if ($id && $newName) {
            $newSlug = slugify($newName);
            $stmt = $db->prepare('UPDATE categories SET name = ?, slug = ? WHERE id = ?');
            $stmt->execute([$newName, $newSlug, $id]);
            header('Location: ' . BASE_URL . '/admin/photos.php?notice=Category renamed.');
            exit;
        }
        header('Location: ' . BASE_URL . '/admin/photos.php?error=Invalid name.');
        exit;
    }

    // Upload photos
    if ($_POST['action'] === 'upload') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        if (!$categoryId || !isset($_FILES['photos'])) {
            header('Location: ' . BASE_URL . '/admin/photos.php?error=Select a category and photos.');
            exit;
        }

        // Verify category exists
        $stmt = $db->prepare('SELECT id, slug FROM categories WHERE id = ?');
        $stmt->execute([$categoryId]);
        $cat = $stmt->fetch();
        if (!$cat) {
            header('Location: ' . BASE_URL . '/admin/photos.php?error=Category not found.');
            exit;
        }

        // Upload directory
        $uploadDir = SITE_ROOT . '/uploads/' . $cat['slug'];
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $uploaded = 0;
        $files = $_FILES['photos'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, IMAGE_EXTENSIONS)) continue;

            $originalName = $files['name'][$i];
            $safeName = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $originalName);
            $filename = time() . '_' . $safeName;
            $dest = $uploadDir . '/' . $filename;

            if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
                $filePath = '/uploads/' . $cat['slug'] . '/' . $filename;
                $fileSize = (int)$files['size'][$i];
                $stmt = $db->prepare('INSERT INTO photos (category_id, filename, original_name, file_path, file_size) VALUES (?, ?, ?, ?, ?)');
                $stmt->execute([$categoryId, $filename, $originalName, $filePath, $fileSize]);
                $uploaded++;
            }
        }

        header('Location: ' . BASE_URL . '/admin/photos.php?notice=' . $uploaded . ' photo(s) uploaded.');
        exit;
    }

    // Delete photo
    if ($_POST['action'] === 'delete_photo') {
        $photoId = (int)($_POST['photo_id'] ?? 0);
        if ($photoId) {
            $stmt = $db->prepare('SELECT file_path FROM photos WHERE id = ?');
            $stmt->execute([$photoId]);
            $photo = $stmt->fetch();
            if ($photo) {
                $fullPath = SITE_ROOT . $photo['file_path'];
                if (file_exists($fullPath)) unlink($fullPath);
                $stmt = $db->prepare('DELETE FROM photos WHERE id = ?');
                $stmt->execute([$photoId]);
            }
            header('Location: ' . BASE_URL . '/admin/photos.php?notice=Photo deleted.');
            exit;
        }
    }
}

// Get categories (parent + children)
$allCategories = $db->query('SELECT * FROM categories ORDER BY parent_id IS NULL DESC, parent_id, sort_order, name')->fetchAll();
$parentCategories = array_filter($allCategories, function($c) { return $c['parent_id'] === null; });
$childCategories = array_filter($allCategories, function($c) { return $c['parent_id'] !== null; });

// Total photos
$totalPhotos = (int)$db->query('SELECT COUNT(*) FROM photos')->fetchColumn();

// Photos per category
$photoCounts = [];
$rows = $db->query('SELECT category_id, COUNT(*) as cnt FROM photos GROUP BY category_id')->fetchAll();
foreach ($rows as $r) $photoCounts[$r['category_id']] = (int)$r['cnt'];

require_once __DIR__ . '/layout_head.php';
?>

<!-- Stats -->
<div class="dash-stats">
    <div class="dash-card dash-card--purple">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm2 12 4.5-5.5 3 3.5 2.5-3L19 17" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= $totalPhotos ?></p>
            <p class="dash-card__label">Total Photos</p>
        </div>
    </div>
    <div class="dash-card dash-card--blue">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 7h18M3 12h18M3 17h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= count($parentCategories) ?></p>
            <p class="dash-card__label">Categories</p>
        </div>
    </div>
    <div class="dash-card dash-card--green">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= count($childCategories) ?></p>
            <p class="dash-card__label">Subcategories</p>
        </div>
    </div>
</div>

<!-- Create Category -->
<section class="dash-panel">
    <div class="dash-panel__header"><h2>Create Category</h2></div>
    <form method="POST" class="form-inline">
        <input type="hidden" name="action" value="create_category">
        <input type="text" name="name" placeholder="Category name..." required>
        <select name="parent_id">
            <option value="">— Top level —</option>
            <?php foreach ($parentCategories as $c): ?>
            <option value="<?= $c['id'] ?>">↳ <?= e($c['name']) ?> (subcategory)</option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</section>

<!-- Upload Photos (only shown if categories exist) -->
<?php if (!empty($parentCategories)): ?>
<section class="dash-panel">
    <div class="dash-panel__header"><h2>Upload Photos</h2></div>
    <form method="POST" enctype="multipart/form-data" class="form-stack">
        <input type="hidden" name="action" value="upload">
        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" required>
                    <option value="">Select category...</option>
                    <?php foreach ($allCategories as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= $c['parent_id'] ? '↳ ' : '' ?><?= e($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Select Images</label>
                <input type="file" name="photos[]" multiple accept="image/*" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</section>
<?php endif; ?>

<!-- All Categories -->
<section class="dash-panel">
    <div class="dash-panel__header"><h2>All Categories</h2></div>
    <?php if (empty($allCategories)): ?>
        <p class="empty">No categories yet. Create one above to start uploading photos.</p>
    <?php else: ?>
    <table class="data-table">
        <thead><tr><th>Name</th><th>Slug</th><th>Type</th><th>Photos</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach ($allCategories as $c):
                $parentName = '';
                if ($c['parent_id']) {
                    foreach ($parentCategories as $p) {
                        if ($p['id'] == $c['parent_id']) { $parentName = $p['name']; break; }
                    }
                }
            ?>
            <tr>
                <td><strong><?= e($c['name']) ?></strong></td>
                <td><code><?= e($c['slug']) ?></code></td>
                <td><?= $c['parent_id'] ? '<span class="tag">Sub of ' . e($parentName) . '</span>' : 'Category' ?></td>
                <td><?= $photoCounts[$c['id']] ?? 0 ?></td>
                <td class="actions">
                    <button type="button" class="action" onclick="renameCategory(<?= $c['id'] ?>, '<?= e(addslashes($c['name'])) ?>')">Edit</button>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Delete this category and all its photos?')">
                        <input type="hidden" name="action" value="delete_category">
                        <input type="hidden" name="category_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="action danger" style="background:none;border:none;cursor:pointer;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<!-- Recent Photos -->
<?php
$recentPhotos = $db->query('SELECT p.*, c.name as category_name FROM photos p JOIN categories c ON p.category_id = c.id ORDER BY p.uploaded_at DESC LIMIT 12')->fetchAll();
if (!empty($recentPhotos)):
?>
<section class="dash-panel">
    <div class="dash-panel__header"><h2>Recent Uploads</h2></div>
    <div class="photo-admin-grid">
        <?php foreach ($recentPhotos as $photo): ?>
        <div class="photo-admin-item">
            <img src="<?= e($photo['file_path']) ?>" alt="<?= e($photo['original_name']) ?>" loading="lazy">
            <div class="photo-admin-meta">
                <span class="tag"><?= e($photo['category_name']) ?></span>
                <form method="POST" onsubmit="return confirm('Delete this photo?')">
                    <input type="hidden" name="action" value="delete_photo">
                    <input type="hidden" name="photo_id" value="<?= $photo['id'] ?>">
                    <button type="submit" class="action danger" style="background:none;border:none;cursor:pointer;font-size:0.7rem;">✕</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Rename Form (hidden, used by JS) -->
<form id="rename-form" method="POST" style="display:none">
    <input type="hidden" name="action" value="rename_category">
    <input type="hidden" name="category_id" id="rename-id">
    <input type="hidden" name="new_name" id="rename-name">
</form>

<script>
function renameCategory(id, currentName) {
    var newName = prompt('Rename category:', currentName);
    if (newName && newName.trim() && newName.trim() !== currentName) {
        document.getElementById('rename-id').value = id;
        document.getElementById('rename-name').value = newName.trim();
        document.getElementById('rename-form').submit();
    }
}
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
