<?php
/**
 * Admin — Site Content Management (Hero, About, Services)
 */
$adminTitle = 'Site Content';
$adminActive = 'site-content';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';
    $content = getSiteContent();

    if ($section === 'hero') {
        $content['hero']['kicker'] = trim($_POST['kicker'] ?? $content['hero']['kicker']);
        $content['hero']['headingLine1'] = trim($_POST['headingLine1'] ?? $content['hero']['headingLine1']);
        $content['hero']['headingLine2'] = trim($_POST['headingLine2'] ?? $content['hero']['headingLine2']);
        $content['hero']['sub'] = trim($_POST['sub'] ?? $content['hero']['sub']);
        $content['hero']['ctaLabel'] = trim($_POST['ctaLabel'] ?? $content['hero']['ctaLabel']);
        $content['hero']['ctaHref'] = trim($_POST['ctaHref'] ?? $content['hero']['ctaHref']);
    }

    if ($section === 'about') {
        $content['about']['kicker'] = trim($_POST['kicker'] ?? $content['about']['kicker']);
        $content['about']['heading'] = trim($_POST['heading'] ?? $content['about']['heading']);
        $content['about']['signature'] = trim($_POST['signature'] ?? $content['about']['signature']);
        $paragraphs = trim($_POST['paragraphs'] ?? '');
        if ($paragraphs) {
            $content['about']['paragraphs'] = array_filter(array_map('trim', explode("\n\n", $paragraphs)));
        }
    }

    if ($section === 'services') {
        $content['services']['kicker'] = trim($_POST['kicker'] ?? $content['services']['kicker']);
        $content['services']['heading'] = trim($_POST['heading'] ?? $content['services']['heading']);

        // Rebuild items from POST
        $items = [];
        if (isset($_POST['item_title']) && is_array($_POST['item_title'])) {
            for ($i = 0; $i < count($_POST['item_title']); $i++) {
                $title = trim($_POST['item_title'][$i] ?? '');
                $desc = trim($_POST['item_desc'][$i] ?? '');
                if ($title) {
                    $items[] = ['title' => $title, 'desc' => $desc];
                }
            }
        }
        if (!empty($items)) $content['services']['items'] = $items;
    }

    // Save
    if (!is_dir(DATA_DIR)) mkdir(DATA_DIR, 0755, true);
    file_put_contents(SITE_CONTENT_FILE, json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    header('Location: ' . BASE_URL . '/admin/site-content?notice=Content updated.#' . $section);
    exit;
}

$adminTitle = 'Site Content';
require_once __DIR__ . '/layout_head.php';

$content = getSiteContent();
$hero = $content['hero'];
$about = $content['about'];
$services = $content['services'];
?>

<!-- Hero Section Editor -->
<section class="panel" id="hero">
    <h2>Hero Section</h2>
    <form method="POST" class="form-stack">
        <input type="hidden" name="section" value="hero">
        <div class="form-group">
            <label>Kicker</label>
            <input type="text" name="kicker" value="<?= e($hero['kicker']) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Heading Line 1</label>
                <input type="text" name="headingLine1" value="<?= e($hero['headingLine1']) ?>">
            </div>
            <div class="form-group">
                <label>Heading Line 2</label>
                <input type="text" name="headingLine2" value="<?= e($hero['headingLine2']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Subtitle</label>
            <input type="text" name="sub" value="<?= e($hero['sub']) ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>CTA Label</label>
                <input type="text" name="ctaLabel" value="<?= e($hero['ctaLabel']) ?>">
            </div>
            <div class="form-group">
                <label>CTA Link</label>
                <input type="text" name="ctaHref" value="<?= e($hero['ctaHref']) ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Save Hero</button>
    </form>
</section>

<!-- About Section Editor -->
<section class="panel" id="about">
    <h2>About Section</h2>
    <form method="POST" class="form-stack">
        <input type="hidden" name="section" value="about">
        <div class="form-row">
            <div class="form-group">
                <label>Kicker</label>
                <input type="text" name="kicker" value="<?= e($about['kicker']) ?>">
            </div>
            <div class="form-group">
                <label>Heading</label>
                <input type="text" name="heading" value="<?= e($about['heading']) ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Paragraphs (separate with blank line)</label>
            <textarea name="paragraphs" rows="10"><?= e(implode("\n\n", $about['paragraphs'])) ?></textarea>
        </div>
        <div class="form-group">
            <label>Signature</label>
            <input type="text" name="signature" value="<?= e($about['signature']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Save About</button>
    </form>
</section>

<!-- Services Section Editor -->
<section class="panel" id="services">
    <h2>Services Section</h2>
    <form method="POST" class="form-stack">
        <input type="hidden" name="section" value="services">
        <div class="form-row">
            <div class="form-group">
                <label>Kicker</label>
                <input type="text" name="kicker" value="<?= e($services['kicker']) ?>">
            </div>
            <div class="form-group">
                <label>Heading</label>
                <input type="text" name="heading" value="<?= e($services['heading']) ?>">
            </div>
        </div>

        <h3>Service Items</h3>
        <div id="services-list">
            <?php foreach ($services['items'] as $i => $item): ?>
            <div class="form-row service-item">
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="item_title[]" value="<?= e($item['title']) ?>">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="item_desc[]" value="<?= e($item['desc']) ?>">
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-secondary" onclick="addServiceItem()">+ Add Item</button>
        <br><br>
        <button type="submit" class="btn btn-primary">Save Services</button>
    </form>
</section>

<script>
function addServiceItem() {
    const list = document.getElementById('services-list');
    const row = document.createElement('div');
    row.className = 'form-row service-item';
    row.innerHTML = `
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="item_title[]" value="">
        </div>
        <div class="form-group">
            <label>Description</label>
            <input type="text" name="item_desc[]" value="">
        </div>
    `;
    list.appendChild(row);
}
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
