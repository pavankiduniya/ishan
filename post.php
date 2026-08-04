<?php
/**
 * Nazarbandi — Single Blog Post
 */
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    header('Location: ' . BASE_URL . '/blog');
    exit;
}

$posts = getBlogPosts();
$post = null;
foreach ($posts as $p) {
    if ($p['slug'] === $slug) {
        $post = $p;
        break;
    }
}

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Not Found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="not-found"><h1>Post not found</h1><p><a href="' . BASE_URL . '/blog">&larr; Back to blog</a></p></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = $post['title'];

// Build archive for sidebar
$archive = [];
foreach ($posts as $p2) {
    $date = strtotime($p2['pubDate']);
    $year = (int) date('Y', $date);
    $month = (int) date('n', $date) - 1;
    $monthLabel = date('F', $date);

    if (!isset($archive[$year])) {
        $archive[$year] = ['year' => $year, 'count' => 0, 'months' => []];
    }
    $archive[$year]['count']++;

    if (!isset($archive[$year]['months'][$month])) {
        $archive[$year]['months'][$month] = ['month' => $month, 'label' => $monthLabel, 'count' => 0];
    }
    $archive[$year]['months'][$month]['count']++;
}
krsort($archive);

require_once __DIR__ . '/includes/header.php';
?>

<div class="blog-layout">
    <!-- Blog Sidebar -->
    <aside class="sidebar">
        <p class="kicker">Archive</p>
        <p class="total"><?= count($posts) ?> posts</p>

        <nav class="archive-nav">
            <?php foreach ($archive as $g): ?>
            <div class="year-group">
                <a href="<?= BASE_URL ?>/blog#y-<?= $g['year'] ?>" class="year-link">
                    <?= $g['year'] ?> <span>(<?= $g['count'] ?>)</span>
                </a>
                <ul>
                    <?php foreach ($g['months'] as $m): ?>
                    <li>
                        <a href="<?= BASE_URL ?>/blog#y-<?= $g['year'] ?>-m-<?= $m['month'] ?>">
                            <?= e($m['label']) ?> <span>(<?= $m['count'] ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Post Content -->
    <main class="post">
        <a class="back" href="<?= BASE_URL ?>/blog">&larr; Back to blog</a>

        <?php if (!empty($post['coverImage'])): ?>
        <div class="cover">
            <img src="<?= e($post['coverImage']) ?>" alt="<?= e($post['title']) ?>">
        </div>
        <?php endif; ?>

        <header class="post-heading">
            <p class="date"><?= date('F j, Y', strtotime($post['pubDate'])) ?></p>
            <h1><?= e($post['title']) ?></h1>
            <?php if (!empty($post['description'])): ?>
            <p class="deck"><?= e($post['description']) ?></p>
            <?php endif; ?>
        </header>

        <article>
            <?= markdownToHtml($post['body']) ?>
        </article>
    </main>

    <!-- Right Rail -->
    <aside class="right-rail">
        <div class="widget author">
            <div class="avatar"></div>
            <h4>Ishan Kothari</h4>
            <p>Photographer and videographer covering people, places, cultures and food.</p>
            <a href="<?= BASE_URL ?>/#about">More about me &rarr;</a>
        </div>

        <div class="widget">
            <p class="widget-title">Get in touch</p>
            <a href="mailto:ishankothari1999@gmail.com">ishankothari1999@gmail.com</a>
        </div>

        <div class="widget">
            <p class="widget-title">Follow</p>
            <div class="follow-links">
                <a href="https://www.instagram.com/ishan_kothari/" target="_blank" rel="noopener noreferrer">Instagram</a>
                <a href="https://vsco.co" target="_blank" rel="noopener noreferrer">VSCO</a>
            </div>
        </div>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
