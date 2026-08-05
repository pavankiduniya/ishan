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
                <a href="<?= BASE_URL ?>/admin/blog-edit?id=<?= $post['id'] ?>" class="icon-btn" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke-linecap="round" stroke-linejoin="round"/><path d="M18.5 2.5a2.12 2.12 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
                <a href="<?= BASE_URL ?>/admin/blog.php?delete=<?= $post['id'] ?>" class="icon-btn icon-btn--danger" title="Delete" onclick="return confirm('Delete this post?')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
