<?php
/**
 * Admin — Blog Management
 */
$adminTitle = 'Blog';
$adminActive = 'blog';
require_once __DIR__ . '/layout_head.php';

// Handle delete
if (isset($_GET['delete'])) {
    $slug = $_GET['delete'];
    $file = BLOG_DIR . '/' . basename($slug) . '.md';
    if (file_exists($file)) {
        unlink($file);
    }
    header('Location: ' . BASE_URL . '/admin/blog.php?notice=Post deleted.');
    exit;
}

$posts = getBlogPosts();
?>

<div class="toolbar">
    <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/blog-edit.php">+ New Post</a>
</div>

<?php if (empty($posts)): ?>
    <p class="empty">No blog posts yet.</p>
<?php else: ?>
<table class="data-table">
    <thead>
        <tr>
            <th>Title</th>
            <th>Date</th>
            <th>Description</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($posts as $post): ?>
        <tr>
            <td><strong><?= e($post['title']) ?></strong></td>
            <td><?= e($post['pubDate']) ?></td>
            <td><?= e(substr($post['description'] ?? '', 0, 60)) ?></td>
            <td class="actions">
                <a href="<?= BASE_URL ?>/admin/blog-edit.php?slug=<?= urlencode($post['slug']) ?>" class="action">Edit</a>
                <a href="<?= BASE_URL ?>/admin/blog.php?delete=<?= urlencode($post['slug']) ?>" class="action danger" onclick="return confirm('Delete this post?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
