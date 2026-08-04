<?php
/**
 * Admin — Create/Edit Blog Post
 */
$adminTitle = 'Edit Post';
$adminActive = 'blog';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$slug = $_GET['slug'] ?? '';
$post = null;

if ($slug) {
    $file = BLOG_DIR . '/' . basename($slug) . '.md';
    if (file_exists($file)) {
        $post = parseBlogPost($file);
    }
    $adminTitle = $post ? 'Edit: ' . $post['title'] : 'New Post';
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

    // Generate slug for new posts
    $saveSlug = $slug ?: slugify($title);
    if (!$saveSlug) $saveSlug = 'post-' . time();

    // Ensure unique slug for new posts
    if (!$slug) {
        $n = 2;
        $base = $saveSlug;
        while (file_exists(BLOG_DIR . '/' . $saveSlug . '.md')) {
            $saveSlug = $base . '-' . $n;
            $n++;
        }
    }

    // Build frontmatter
    $lines = ['---'];
    $lines[] = 'title: "' . str_replace('"', '\\"', $title) . '"';
    if ($description) $lines[] = 'description: "' . str_replace('"', '\\"', $description) . '"';
    $lines[] = 'pubDate: ' . $pubDate;
    if ($coverImage) $lines[] = 'coverImage: "' . str_replace('"', '\\"', $coverImage) . '"';
    $lines[] = '---';
    $lines[] = '';
    $lines[] = trim($body);
    $lines[] = '';

    if (!is_dir(BLOG_DIR)) mkdir(BLOG_DIR, 0755, true);
    file_put_contents(BLOG_DIR . '/' . $saveSlug . '.md', implode("\n", $lines));

    header('Location: ' . BASE_URL . '/admin/blog?notice=Post saved.');
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
            <input type="date" id="pubDate" name="pubDate" value="<?= e($post['pubDate'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="form-group">
            <label for="coverImage">Cover Image URL (optional)</label>
            <input type="text" id="coverImage" name="coverImage" value="<?= e($post['coverImage'] ?? '') ?>" placeholder="/photos-gallery/...">
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
