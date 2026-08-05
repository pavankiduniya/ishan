<?php
/**
 * Nazarbandi — Blog Page
 */
$pageTitle = 'Blog';

require_once __DIR__ . '/includes/header.php';

$posts = getBlogPosts();

// Build archive grouping
$archive = [];
foreach ($posts as $post) {
    $date = strtotime($post['pubDate']);
    $year = (int) date('Y', $date);
    $month = (int) date('n', $date) - 1; // 0-indexed
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
?>

<div class="blog-layout" style="align-items:start;">
    <!-- Blog Sidebar -->
    <aside class="sidebar" style="position:sticky;top:96px;align-self:start;">
        <p class="kicker">Archive</p>
        <p class="total"><?= count($posts) ?> posts</p>

        <nav class="archive-nav">
            <?php foreach ($archive as $g): ?>
            <div class="year-group">
                <a href="#y-<?= $g['year'] ?>" class="year-link">
                    <?= $g['year'] ?> <span>(<?= $g['count'] ?>)</span>
                </a>
                <ul>
                    <?php foreach ($g['months'] as $m): ?>
                    <li>
                        <a href="#y-<?= $g['year'] ?>-m-<?= $m['month'] ?>">
                            <?= e($m['label']) ?> <span>(<?= $m['count'] ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Blog Content -->
    <main class="content">
        <header class="heading">
            <p class="kicker">From the field</p>
            <h1>Blog</h1>
            <p class="sub">Notes, stories and stills from behind the camera.</p>
        </header>

        <?php if (empty($posts)): ?>
            <p class="empty">Nothing published yet — check back soon.</p>
        <?php else: ?>
            <?php foreach ($archive as $g): ?>
            <section id="y-<?= $g['year'] ?>" class="year-section">
                <h2 class="year-heading"><?= $g['year'] ?></h2>

                <?php foreach ($g['months'] as $m): ?>
                <div id="y-<?= $g['year'] ?>-m-<?= $m['month'] ?>" class="month-section">
                    <h3 class="month-heading"><?= e($m['label']) ?></h3>

                    <ul class="posts">
                        <?php
                        $monthPosts = array_filter($posts, function ($p) use ($g, $m) {
                            $d = strtotime($p['pubDate']);
                            return (int) date('Y', $d) === $g['year'] && ((int) date('n', $d) - 1) === $m['month'];
                        });
                        foreach ($monthPosts as $post):
                        ?>
                        <li>
                            <a class="post-card" href="<?= BASE_URL ?>/post?slug=<?= urlencode($post['slug']) ?>">
                                <div class="thumb">
                                    <?php if (!empty($post['coverImage'])): ?>
                                    <img src="<?= e($post['coverImage']) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
                                    <?php endif; ?>
                                </div>
                                <div class="meta">
                                    <p class="date"><?= date('F j', strtotime($post['pubDate'])) ?></p>
                                    <h4><?= e($post['title']) ?></h4>
                                    <?php if (!empty($post['description'])): ?>
                                    <p class="desc"><?= e($post['description']) ?></p>
                                    <?php endif; ?>
                                    <span class="read">Read the story &rarr;</span>
                                </div>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </section>
            <?php endforeach; ?>
        <?php endif; ?>
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
            <img class="avatar-img" src="<?= e($aboutInfo['photo']) ?>" alt="<?= e($aboutInfo['heading']) ?>">
            <?php else: ?>
            <div class="avatar"></div>
            <?php endif; ?>
            <h4><?= e(str_replace("Hello, I'm ", '', $aboutInfo['heading'])) ?></h4>
            <p><?= e($aboutInfo['paragraphs'][0] ?? '') ?></p>
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
