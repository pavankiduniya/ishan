<?php
/**
 * Admin — Create/Edit Blog Post (DB-backed)
 */
$adminTitle = 'New Post';
$adminActive = 'blog';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$db = getDB();

$postId = (int)($_GET['id'] ?? 0);
$post = null;

if ($postId) {
    $stmt = $db->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    if ($post) $adminTitle = 'Edit: ' . $post['title'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $pubDate = $_POST['pubDate'] ?? date('Y-m-d');
    $body = $_POST['body'] ?? '';
    $coverImage = trim($_POST['coverImage'] ?? '');

    if (!$title) {
        header('Location: ' . $_SERVER['REQUEST_URI'] . '&error=Title is required.');
        exit;
    }

    if ($postId && $post) {
        // Update existing
        $stmt = $db->prepare('UPDATE blog_posts SET title = ?, description = ?, body = ?, cover_image = ?, published_at = ? WHERE id = ?');
        $stmt->execute([$title, $description ?: null, $body, $coverImage ?: null, $pubDate, $postId]);
    } else {
        // Create new
        $slug = slugify($title) ?: 'post-' . time();

        // Ensure unique slug
        $n = 2;
        $baseSlug = $slug;
        while (true) {
            $check = $db->prepare('SELECT id FROM blog_posts WHERE slug = ?');
            $check->execute([$slug]);
            if (!$check->fetch()) break;
            $slug = $baseSlug . '-' . $n;
            $n++;
        }

        $stmt = $db->prepare('INSERT INTO blog_posts (slug, title, description, body, cover_image, published_at) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$slug, $title, $description ?: null, $body, $coverImage ?: null, $pubDate]);
    }

    header('Location: ' . BASE_URL . '/admin/blog.php?notice=Post saved.');
    exit;
}

require_once __DIR__ . '/layout_head.php';
?>

<form method="POST" class="form-stack">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" value="<?= e($post['title'] ?? '') ?>" required>
    </div>

    <div class="form-group">
        <label for="description">Description (optional)</label>
        <input type="text" id="description" name="description" value="<?= e($post['description'] ?? '') ?>">
    </div>

    <div class="form-row">
        <div class="form-group">
            <label for="pubDate">Date</label>
            <input type="date" id="pubDate" name="pubDate" value="<?= e($post['published_at'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group">
            <label for="coverImage">Cover Image URL (optional)</label>
            <input type="text" id="coverImage" name="coverImage" value="<?= e($post['cover_image'] ?? '') ?>" placeholder="/uploads/...">
        </div>
    </div>

    <div class="form-group">
        <label for="body">Content (Markdown)</label>
        <textarea id="body" name="body" rows="18"><?= e($post['body'] ?? '') ?></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Save Post</button>
        <a href="<?= BASE_URL ?>/admin/blog" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
