<?php
/**
 * Admin — Blog Management (DB-backed)
 */
$adminTitle = 'Blog';
$adminActive = 'blog';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$db = getDB();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id) {
        $stmt = $db->prepare('DELETE FROM blog_posts WHERE id = ?');
        $stmt->execute([$id]);
    }
    header('Location: ' . BASE_URL . '/admin/blog.php?notice=Post deleted.');
    exit;
}

$posts = getBlogPosts();

require_once __DIR__ . '/layout_head.php';
?>

<div class="toolbar">
    <a class="btn btn-primary" href="<?= BASE_URL ?>/admin/blog-edit">+ New Post</a>
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
                <a href="<?= BASE_URL ?>/admin/blog-edit?id=<?= $post['id'] ?>" class="action">Edit</a>
                <a href="<?= BASE_URL ?>/admin/blog.php?delete=<?= $post['id'] ?>" class="action danger" onclick="return confirm('Delete this post?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
