<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'Home';
$showSplash = $showSplash ?? false;
$categories = getAllCategoryTrees();
$hasPhotos = false;
foreach ($categories as $c) {
    if ($c['total'] > 0) { $hasPhotos = true; break; }
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
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
        <a href="<?= BASE_URL ?>/#work">Work</a>

        <?php if ($hasPhotos): ?>
        <div class="dropdown">
            <a href="<?= BASE_URL ?>/gallery">Gallery</a>
            <div class="dropdown-menu">
                <?php foreach ($categories as $c): ?>
                <div class="dropdown-group">
                    <a href="<?= BASE_URL ?>/gallery#cat-<?= e($c['slug']) ?>"><?= e($c['label']) ?></a>
                    <?php if (!empty($c['subcategories'])): ?>
                    <div class="dropdown-sub">
                        <?php foreach ($c['subcategories'] as $s): ?>
                        <a href="<?= BASE_URL ?>/gallery#cat-<?= e($c['slug']) ?>-sub-<?= e($s['slug']) ?>"><?= e($s['label']) ?></a>
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
