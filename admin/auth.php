<?php
/**
 * Admin authentication guard — include at the top of every admin page.
 * Redirects to login if no valid session exists.
 */
session_start();

if (empty($_SESSION['admin'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit;
}

$adminUser = $_SESSION['admin'];
