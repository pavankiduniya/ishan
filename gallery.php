<?php
/**
 * Nazarbandi — Full Gallery Page
 */
$pageTitle = 'Gallery';

require_once __DIR__ . '/includes/header.php';

$categories = getAllCategoryTrees();
$total = 0;
foreach ($categories as $c) {
    $total += $c['total'];
}
?>

<div class="gallery-layout">
    <!-- Sidebar -->
    <aside class="sidebar">
        <p class="kicker">Categories</p>
        <p class="total"><?= $total ?> photos</p>

        <nav class="category-nav">
            <?php foreach ($categories as $c): ?>
            <div class="category-group">
                <a href="#cat-<?= e($c['slug']) ?>" class="category-link">
                    <?= e($c['label']) ?> <span>(<?= $c['total'] ?>)</span>
                </a>
                <?php if (!empty($c['subcategories'])): ?>
                <ul>
                    <?php foreach ($c['subcategories'] as $sub): ?>
                    <li>
                        <a href="#cat-<?= e($c['slug']) ?>-sub-<?= e($sub['slug']) ?>">
                            <?= e($sub['label']) ?> <span>(<?= count($sub['photos']) ?>)</span>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </nav>
    </aside>

    <!-- Main Gallery Content -->
    <main class="gallery-page">
        <header class="heading">
            <p class="kicker">Full gallery</p>
            <h1>All the frames</h1>
            <p class="sub">Everything so far, sorted by category.</p>
        </header>

        <?php if (empty($categories)): ?>
            <p class="empty">No categories yet — add a folder under <code>public/photos/</code> and drop some images in.</p>
        <?php else: ?>
            <?php foreach ($categories as $c): ?>
            <section id="cat-<?= e($c['slug']) ?>" class="category">
                <h2><?= e($c['label']) ?></h2>

                <?php if ($c['total'] === 0): ?>
                    <p class="empty">Nothing added to this folder yet.</p>
                <?php else: ?>
                    <?php if (!empty($c['direct'])): ?>
                    <div class="photo-grid">
                        <?php foreach ($c['direct'] as $p): ?>
                        <figure>
                            <img src="<?= e($p['gallery']) ?>" data-full="<?= e($p['src']) ?>" alt="<?= e($p['filename']) ?>" loading="lazy">
                            <span class="watermark">ik</span>
                        </figure>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <?php foreach ($c['subcategories'] as $sub): ?>
                    <div id="cat-<?= e($c['slug']) ?>-sub-<?= e($sub['slug']) ?>" class="subcategory">
                        <h3><?= e($sub['label']) ?></h3>
                        <div class="photo-grid">
                            <?php foreach ($sub['photos'] as $p): ?>
                            <figure>
                                <img src="<?= e($p['gallery']) ?>" data-full="<?= e($p['src']) ?>" alt="<?= e($p['filename']) ?>" loading="lazy">
                                <span class="watermark">ik</span>
                            </figure>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
