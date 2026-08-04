<?php
/**
 * Admin — Site Content Management (Hero, About, Services)
 * Now saves to MySQL site_settings table.
 */
$adminTitle = 'Site Content';
$adminActive = 'site-content';

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/auth.php';

/**
 * Upsert a setting into the database.
 */
function saveSetting(string $key, string $value): void {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?');
    $stmt->execute([$key, $value, $value]);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $section = $_POST['section'] ?? '';

    if ($section === 'hero') {
        saveSetting('hero_kicker', trim($_POST['kicker'] ?? ''));
        saveSetting('hero_heading_line1', trim($_POST['headingLine1'] ?? ''));
        saveSetting('hero_heading_line2', trim($_POST['headingLine2'] ?? ''));
        saveSetting('hero_sub', trim($_POST['sub'] ?? ''));
        saveSetting('hero_cta_label', trim($_POST['ctaLabel'] ?? ''));
        saveSetting('hero_cta_href', trim($_POST['ctaHref'] ?? ''));
    }

    if ($section === 'about') {
        saveSetting('about_kicker', trim($_POST['kicker'] ?? ''));
        saveSetting('about_heading', trim($_POST['heading'] ?? ''));
        saveSetting('about_signature', trim($_POST['signature'] ?? ''));
        $paragraphs = trim($_POST['paragraphs'] ?? '');
        // Store with \n\n as separator
        saveSetting('about_paragraphs', $paragraphs);
    }

    if ($section === 'services') {
        saveSetting('services_kicker', trim($_POST['kicker'] ?? ''));
        saveSetting('services_heading', trim($_POST['heading'] ?? ''));

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
        saveSetting('services_items', json_encode($items, JSON_UNESCAPED_UNICODE));
    }

    header('Location: ' . BASE_URL . '/admin/site-content.php?notice=Content updated.#' . $section);
    exit;
}

require_once __DIR__ . '/layout_head.php';

$content = getSiteContent();
$hero = $content['hero'];
$about = $content['about'];
$services = $content['services'];
?>

<!-- Hero Section Editor -->
<section class="dash-panel" id="hero">
    <div class="dash-panel__header"><h2>Hero Section</h2></div>
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
<section class="dash-panel" id="about">
    <div class="dash-panel__header"><h2>About Section</h2></div>
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
<section class="dash-panel" id="services">
    <div class="dash-panel__header"><h2>Services Section</h2></div>
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

        <h3 style="margin: 1.5rem 0 1rem; font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; color: #666;">Service Items</h3>
        <div id="services-list">
            <?php foreach ($services['items'] as $item): ?>
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
    var list = document.getElementById('services-list');
    var row = document.createElement('div');
    row.className = 'form-row service-item';
    row.innerHTML = '<div class="form-group"><label>Title</label><input type="text" name="item_title[]" value=""></div><div class="form-group"><label>Description</label><input type="text" name="item_desc[]" value=""></div>';
    list.appendChild(row);
}
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
