<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Home';
$showSplash = $showSplash ?? false;

// Gallery nav from DB
$db = getDB();
$totalPhotosNav = (int)$db->query('SELECT COUNT(*) FROM photos')->fetchColumn();
$hasPhotos = $totalPhotosNav > 0;

$navCategories = [];
if ($hasPhotos) {
    $allCats = $db->query('SELECT id, name, slug, parent_id FROM categories ORDER BY sort_order, name')->fetchAll();
    $parentCats = array_filter($allCats, function($c) { return $c['parent_id'] === null; });
    foreach ($parentCats as &$p) {
        $p['children'] = array_filter($allCats, function($c) use ($p) { return (int)$c['parent_id'] === (int)$p['id']; });
    }
    $navCategories = $parentCats;
}

$blogPosts = getBlogPosts();
$hasPosts = count($blogPosts) > 0;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — Nazarbandi</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= filemtime(SITE_ROOT . '/assets/css/style.css') ?>">
</head>
<body>
<?php if ($showSplash): ?>
<!-- Splash Screen -->
<div id="splash" class="splash">
    <div class="line" id="line-top"></div>
    <div id="word"></div>
    <div id="byline">by IK</div>
    <div class="line" id="line-bottom"></div>
</div>
<?php endif; ?>

<!-- Navigation -->
<header class="nav">
    <a class="brand" href="<?= BASE_URL ?>/">Nazarbandi</a>

    <nav class="links" id="nav-links">
        <?php if ($hasPhotos): ?>
        <div class="dropdown">
            <a href="<?= BASE_URL ?>/gallery">Gallery</a>
            <div class="dropdown-menu">
                <?php foreach ($navCategories as $c): ?>
                <div class="dropdown-group">
                    <a href="<?= BASE_URL ?>/gallery?cat=<?= e($c['slug']) ?>"><?= e($c['name']) ?></a>
                    <?php if (!empty($c['children'])): ?>
                    <div class="dropdown-sub">
                        <?php foreach ($c['children'] as $s): ?>
                        <a href="<?= BASE_URL ?>/gallery?cat=<?= e($s['slug']) ?>"><?= e($s['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <a href="<?= BASE_URL ?>/#about">About</a>
        <a href="<?= BASE_URL ?>/#services">Services</a>
        <?php if ($hasPosts): ?>
        <a href="<?= BASE_URL ?>/blog">Blog</a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/#contact">Contact</a>
        <a href="<?= BASE_URL ?>/login" class="login-link">Login</a>
    </nav>

    <button class="menu-toggle" id="menu-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
</header>
