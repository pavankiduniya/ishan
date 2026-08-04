<?php
/**
 * Admin — Photos Management
 */
$adminTitle = 'Photos';
$adminActive = 'photos';
require_once __DIR__ . '/layout_head.php';

// Handle create category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_category') {
        $name = trim($_POST['name'] ?? '');
        if ($name) {
            $slug = slugify($name);
            if ($slug && PHOTOS_DIR) {
                $dir = PHOTOS_DIR . '/' . $slug;
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                header('Location: ' . BASE_URL . '/admin/photos?notice=Category "' . urlencode($name) . '" created.');
                exit;
            }
        }
        header('Location: ' . BASE_URL . '/admin/photos?error=Invalid category name.');
        exit;
    }

    if ($_POST['action'] === 'create_subcategory') {
        $category = $_POST['category'] ?? '';
        $name = trim($_POST['name'] ?? '');
        if ($category && $name) {
            $slug = slugify($name);
            if ($slug && PHOTOS_DIR) {
                $dir = PHOTOS_DIR . '/' . basename($category) . '/' . $slug;
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                header('Location: ' . BASE_URL . '/admin/photos?notice=Subcategory "' . urlencode($name) . '" created.');
                exit;
            }
        }
        header('Location: ' . BASE_URL . '/admin/photos?error=Invalid subcategory name.');
        exit;
    }

    if ($_POST['action'] === 'upload') {
        $category = $_POST['category'] ?? '';
        $subcategory = $_POST['subcategory'] ?? '';

        if (!$category || !isset($_FILES['photos'])) {
            header('Location: ' . BASE_URL . '/admin/photos?error=Select a category and photos.');
            exit;
        }

        $targetDir = PHOTOS_DIR . '/' . basename($category);
        if ($subcategory) $targetDir .= '/' . basename($subcategory);

        if (!is_dir($targetDir)) {
            header('Location: ' . BASE_URL . '/admin/photos?error=Target folder does not exist.');
            exit;
        }

        $uploaded = 0;
        $files = $_FILES['photos'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, IMAGE_EXTENSIONS)) continue;

            $safeName = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $files['name'][$i]);
            $dest = $targetDir . '/' . $safeName;

            // Avoid overwrite
            $n = 2;
            $base = pathinfo($safeName, PATHINFO_FILENAME);
            while (file_exists($dest)) {
                $dest = $targetDir . '/' . $base . '-' . $n . '.' . $ext;
                $n++;
            }

            move_uploaded_file($files['tmp_name'][$i], $dest);
            $uploaded++;
        }

        header('Location: ' . BASE_URL . '/admin/photos?notice=' . $uploaded . ' photo(s) uploaded.');
        exit;
    }
}

$categories = getAllCategoryTrees();
$totalPhotos = 0;
foreach ($categories as $c) $totalPhotos += $c['total'];
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
</div>

<!-- Upload Photos -->
<section class="panel">
    <h2>Upload Photos</h2>
    <form method="POST" enctype="multipart/form-data" class="form-stack">
        <input type="hidden" name="action" value="upload">
        <div class="form-row">
            <div class="form-group">
                <label for="upload-cat">Category</label>
                <select id="upload-cat" name="category" required>
                    <option value="">Select category...</option>
                    <?php foreach ($categories as $c): ?>
                    <option value="<?= e($c['slug']) ?>"><?= e($c['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="upload-sub">Subcategory (optional)</label>
                <select id="upload-sub" name="subcategory">
                    <option value="">None (direct)</option>
                    <?php foreach ($categories as $c): ?>
                        <?php foreach ($c['subcategories'] as $sub): ?>
                        <option value="<?= e($sub['slug']) ?>" data-category="<?= e($c['slug']) ?>">
                            <?= e($c['label']) ?> / <?= e($sub['label']) ?>
                        </option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="photos">Select Images</label>
            <input type="file" id="photos" name="photos[]" multiple accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</section>

<!-- Create Category -->
<section class="panel">
    <h2>Create Category</h2>
    <form method="POST" class="form-inline">
        <input type="hidden" name="action" value="create_category">
        <input type="text" name="name" placeholder="Category name..." required>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</section>

<!-- Create Subcategory -->
<section class="panel">
    <h2>Create Subcategory</h2>
    <form method="POST" class="form-inline">
        <input type="hidden" name="action" value="create_subcategory">
        <select name="category" required>
            <option value="">Parent category...</option>
            <?php foreach ($categories as $c): ?>
            <option value="<?= e($c['slug']) ?>"><?= e($c['label']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="text" name="name" placeholder="Subcategory name..." required>
        <button type="submit" class="btn btn-primary">Create</button>
    </form>
</section>

<!-- Category Listing -->
<section class="panel">
    <h2>All Categories</h2>
    <?php if (empty($categories)): ?>
        <p class="empty">No categories yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead>
            <tr><th>Category</th><th>Subcategories</th><th>Photos</th></tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
            <tr>
                <td><strong><?= e($c['label']) ?></strong></td>
                <td>
                    <?php if (!empty($c['subcategories'])): ?>
                        <?php foreach ($c['subcategories'] as $sub): ?>
                            <span class="tag"><?= e($sub['label']) ?> (<?= count($sub['photos']) ?>)</span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </td>
                <td><?= $c['total'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
