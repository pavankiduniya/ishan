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

$post = getBlogPostBySlug($slug);

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
$posts = getBlogPosts();
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

<div class="blog-layout" style="align-items:start;">
    <!-- Blog Sidebar -->
    <aside class="sidebar" style="position:sticky;top:96px;align-self:start;">
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
            <?= $post['body'] ?>
        </article>
    </main>

    <!-- Right Rail -->
    <?php
    $siteContent = getSiteContent();
    $aboutInfo = $siteContent['about'];
    $contactInfo = $siteContent['contact'];
    ?>
    <aside class="right-rail" id="right-rail">
        <div class="widget author">
            <?php if (!empty($aboutInfo['photo'])): ?>
            <img class="avatar-img" src="<?= e($aboutInfo['photo']) ?>" alt="<?= e($aboutInfo['heading']) ?>" style="width:56px;height:56px;border-radius:50%;object-fit:cover;display:block;margin-bottom:1rem;">
            <?php else: ?>
            <div class="avatar"></div>
            <?php endif; ?>
            <h4><?= e(str_replace("Hello, I'm ", '', $aboutInfo['heading'])) ?></h4>
            <p><?= e(mb_substr($aboutInfo['paragraphs'][0] ?? '', 0, 120)) ?>...</p>
            <a href="<?= BASE_URL ?>/#about">More about me &rarr;</a>
        </div>

        <div class="widget">
            <p class="widget-title">Get in touch</p>
            <a href="mailto:<?= e($contactInfo['email']) ?>"><?= e($contactInfo['email']) ?></a>
        </div>

        <?php if (!empty($contactInfo['links'])): ?>
        <div class="widget">
            <p class="widget-title">Follow</p>
            <div class="follow-links">
                <?php foreach ($contactInfo['links'] as $link): ?>
                <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= e($link['name']) ?>">
                    <?= getSocialIcon($link['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </aside>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
