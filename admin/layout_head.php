<?php
/**
 * Admin Layout — Head + Sidebar + Topbar (open tags)
 * Set $adminTitle and $adminActive before including this file.
 */
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/auth.php';

$adminTitle = $adminTitle ?? 'Dashboard';
$adminActive = $adminActive ?? 'dashboard';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> — Nazarbandi Admin</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css?v=<?= time() ?>">
</head>
<body>
<div class="shell">
    <!-- Sidebar -->
    <aside class="sidebar">
        <a class="brand" href="<?= BASE_URL ?>/admin/">
            <span class="brand-text">Nazarbandi</span>
            <span class="brand-kicker">Admin</span>
        </a>

        <nav class="nav">
            <a href="<?= BASE_URL ?>/admin/" class="<?= $adminActive === 'dashboard' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Dashboard</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/blog" class="<?= $adminActive === 'blog' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M6 4h9l5 5v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm8 0v5h5M9 12h6M9 16h6" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Blog</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/photos" class="<?= $adminActive === 'photos' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm2 12 4.5-5.5 3 3.5 2.5-3L19 17M8.5 9.5a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Photos</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/analytics" class="<?= $adminActive === 'analytics' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 19V5m0 14h16M8 19v-6m4.5 6V9m4.5 10v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Analytics</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/site-content" class="<?= $adminActive === 'site-content' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Site Content</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/hero-photos" class="<?= $adminActive === 'hero-photos' ? 'active' : '' ?>" style="padding-left:2.5rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" width="15" height="15"><path d="M4 5h16a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm2 12 4.5-5.5 3 3.5 2.5-3L19 17" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Hero Photos</span>
            </a>
            <a href="<?= BASE_URL ?>/admin/settings" class="<?= $adminActive === 'settings' ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M12 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span>Settings</span>
            </a>
        </nav>

        <a href="<?= BASE_URL ?>/admin/logout" class="logout">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M15 17v1a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v1M9 12h11m0 0-3-3m3 3-3 3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <span>Log out</span>
        </a>
    </aside>

    <!-- Main Content Area -->
    <div class="main">
        <header class="topbar">
            <h1><?= e($adminTitle) ?></h1>
            <div class="user">
                <span class="avatar"><?= strtoupper($adminUser[0]) ?></span>
                <span class="name"><?= e($adminUser) ?></span>
            </div>
        </header>

        <main class="content">
