<?php
/**
 * Nazarbandi — Admin Login Page
 */
require_once __DIR__ . '/includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Authenticate against the admin_users table in MySQL
    $authenticated = false;
    try {
        $db = getDB();
        $stmt = $db->prepare('SELECT password_hash FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if ($row && password_verify($password, $row['password_hash'])) {
            $authenticated = true;
        }
    } catch (Exception $e) {
        // DB connection failed — fall back to hardcoded credentials
        $fallbackUser = 'admin';
        $fallbackHash = '$2y$12$sNTzbAzErh5JQcLxUetwmO9.j9Pa/PRInnk0ZUP71Qn./AcTty7q6';
        if ($username === $fallbackUser && password_verify($password, $fallbackHash)) {
            $authenticated = true;
        }
    }

    if ($authenticated) {
        session_start();
        $_SESSION['admin'] = $username;
        header('Location: ' . BASE_URL . '/admin/');
        exit;
    } else {
        $error = 'Incorrect username or password.';
    }
}

$allPhotos = getAllPhotos();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Log in — Nazarbandi Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/login.css?v=<?= time() ?>">
</head>
<body>
    <div class="split">
        <div class="showcase">
            <?php if (count($allPhotos) > 0): ?>
            <div class="mosaic" id="login-mosaic" data-pool='<?= htmlspecialchars(json_encode($allPhotos), ENT_QUOTES) ?>'></div>
            <?php endif; ?>
            <div class="overlay"></div>

            <div class="showcase-content">
                <p class="sig">ik</p>
                <h2>Ishan Kothari</h2>
                <p class="bio">
                    Photographer &amp; videographer capturing the essence of people, places,
                    cultures and food — one honest frame at a time.
                </p>
                <a class="handle" href="https://www.instagram.com/ishan_kothari/" target="_blank" rel="noopener">
                    @ishan_kothari
                </a>
            </div>
        </div>

        <div class="form-side">
            <main class="login-form">
                <a class="brand" href="<?= BASE_URL ?>/">Nazarbandi</a>
                <p class="kicker">Admin login</p>
                <?php if ($error): ?>
                <p class="error"><?= e($error) ?></p>
                <?php endif; ?>
                <form method="POST">
                    <label>
                        Username
                        <input type="text" name="username" required autofocus>
                    </label>
                    <label>
                        Password
                        <input type="password" name="password" required>
                    </label>
                    <button type="submit">Log in</button>
                </form>
                <a class="back" href="<?= BASE_URL ?>/">&larr; Back to site</a>
            </main>
        </div>
    </div>
</body>
</html>
