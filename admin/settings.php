<?php
/**
 * Admin — Account Settings (Change Password)
 */
$adminTitle = 'Settings';
$adminActive = 'settings';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

$db = getDB();

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validate
    if (!$current || !$new || !$confirm) {
        header('Location: ' . BASE_URL . '/admin/settings.php?error=All fields are required.');
        exit;
    }

    if ($new !== $confirm) {
        header('Location: ' . BASE_URL . '/admin/settings.php?error=New passwords do not match.');
        exit;
    }

    if (strlen($new) < 6) {
        header('Location: ' . BASE_URL . '/admin/settings.php?error=Password must be at least 6 characters.');
        exit;
    }

    // Verify current password
    $stmt = $db->prepare('SELECT password_hash FROM admin_users WHERE username = ? LIMIT 1');
    $stmt->execute([$adminUser]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
        header('Location: ' . BASE_URL . '/admin/settings.php?error=Current password is incorrect.');
        exit;
    }

    // Update password
    $newHash = password_hash($new, PASSWORD_DEFAULT);
    $stmt = $db->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?');
    $stmt->execute([$newHash, $adminUser]);

    header('Location: ' . BASE_URL . '/admin/settings.php?notice=Password changed successfully.');
    exit;
}

require_once __DIR__ . '/layout_head.php';
?>

<section class="dash-panel" style="max-width:500px;">
    <div class="dash-panel__header"><h2>Change Password</h2></div>
    <form method="POST" class="form-stack">
        <input type="hidden" name="action" value="change_password">
        <div class="form-group">
            <label>Current Password</label>
            <input type="password" name="current_password" required>
        </div>
        <div class="form-group">
            <label>New Password</label>
            <input type="password" name="new_password" required minlength="6">
        </div>
        <div class="form-group">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" class="btn btn-primary">Update Password</button>
    </form>
</section>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
