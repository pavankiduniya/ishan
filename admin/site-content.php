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
        saveSetting('about_paragraphs', $paragraphs);

        // Handle photo upload
        if (isset($_FILES['about_photo']) && $_FILES['about_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['about_photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, IMAGE_EXTENSIONS)) {
                $uploadDir = SITE_ROOT . '/uploads/about';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $filename = 'about-photo.' . $ext;
                move_uploaded_file($_FILES['about_photo']['tmp_name'], $uploadDir . '/' . $filename);
                saveSetting('about_photo', '/uploads/about/' . $filename . '?v=' . time());
            }
        }

        // Remove photo if requested
        if (isset($_POST['remove_photo']) && $_POST['remove_photo'] === '1') {
            $uploadDir = SITE_ROOT . '/uploads/about';
            if (is_dir($uploadDir)) {
                foreach (glob($uploadDir . '/*') as $f) unlink($f);
            }
            saveSetting('about_photo', '');
        }
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

    // Watermark
    if ($section === 'watermark') {
        $text = trim($_POST['watermark_text'] ?? '');
        saveSetting('watermark_text', $text);
    }

    // Contact
    if ($section === 'contact') {
        saveSetting('contact_email', trim($_POST['contact_email'] ?? ''));

        // Rebuild links from POST
        $links = [];
        if (isset($_POST['link_name']) && is_array($_POST['link_name'])) {
            for ($i = 0; $i < count($_POST['link_name']); $i++) {
                $name = trim($_POST['link_name'][$i] ?? '');
                $url = trim($_POST['link_url'][$i] ?? '');
                if ($name && $url) {
                    $links[] = ['name' => $name, 'url' => $url];
                }
            }
        }
        saveSetting('contact_links', json_encode($links, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    header('Location: ' . BASE_URL . '/admin/site-content.php?notice=Content updated.#' . $section);
    exit;
}

require_once __DIR__ . '/layout_head.php';

$content = getSiteContent();
$hero = $content['hero'];
$about = $content['about'];
$services = $content['services'];
$watermark = $content['watermark'] ?? 'ik';
$contact = $content['contact'];
?>

<!-- Watermark Setting -->
<section class="dash-panel" id="watermark">
    <div class="dash-panel__header"><h2>Photo Watermark</h2></div>
    <p style="color:#666;font-size:0.85rem;margin:0 0 1rem;">Leave empty to remove watermark from all photos. Uses cursive font (Great Vibes).</p>
    <form method="POST" class="form-inline">
        <input type="hidden" name="section" value="watermark">
        <input type="text" name="watermark_text" value="<?= e($watermark) ?>" placeholder="e.g. ik" style="width:120px;">
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</section>

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
    <form method="POST" enctype="multipart/form-data" class="form-stack">
        <input type="hidden" name="section" value="about">

        <!-- About Photo -->
        <div class="form-group">
            <label>Photo</label>
            <?php if (!empty($about['photo'])): ?>
            <div style="display:flex; align-items:center; gap:1rem; margin-bottom:0.75rem;">
                <img src="<?= e($about['photo']) ?>" alt="About photo" style="width:80px; height:100px; object-fit:cover; border-radius:6px;">
                <label style="font-size:0.8rem; color:#666; display:flex; align-items:center; gap:0.4rem; text-transform:none; letter-spacing:0;">
                    <input type="checkbox" name="remove_photo" value="1"> Remove photo
                </label>
            </div>
            <?php endif; ?>
            <input type="file" name="about_photo" accept="image/*">
        </div>

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
                <button type="button" class="icon-btn icon-btn--danger" title="Remove" onclick="this.closest('.service-item').remove()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-secondary" onclick="addServiceItem()">+ Add Item</button>
        <br><br>
        <button type="submit" class="btn btn-primary">Save Services</button>
    </form>
</section>

<!-- Contact Section Editor -->
<section class="dash-panel" id="contact">
    <div class="dash-panel__header"><h2>Contact / Social Links</h2></div>
    <form method="POST" class="form-stack">
        <input type="hidden" name="section" value="contact">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="contact_email" value="<?= e($contact['email']) ?>" placeholder="your@email.com">
        </div>

        <h3 style="margin: 1.5rem 0 1rem; font-size: 0.85rem; letter-spacing: 1px; text-transform: uppercase; color: #666;">Social / Custom Links</h3>
        <p style="color:#888; font-size:0.8rem; margin:0 0 1rem;">Add any links — Instagram, WhatsApp, Twitter, portfolio, etc. Name is the label shown on the site.</p>
        <div id="contact-links-list">
            <?php foreach ($contact['links'] as $link): ?>
            <div class="form-row contact-link-item">
                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="link_name[]" value="<?= e($link['name']) ?>" placeholder="e.g. Instagram">
                </div>
                <div class="form-group">
                    <label>URL</label>
                    <input type="url" name="link_url[]" value="<?= e($link['url']) ?>" placeholder="https://...">
                </div>
                <button type="button" class="icon-btn icon-btn--danger" title="Remove" onclick="this.closest('.contact-link-item').remove()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg>
                </button>
            </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-secondary" onclick="addContactLink()">+ Add Link</button>
        <br><br>
        <button type="submit" class="btn btn-primary">Save Contact</button>
    </form>
</section>

<script>
function addServiceItem() {
    var list = document.getElementById('services-list');
    var row = document.createElement('div');
    row.className = 'form-row service-item';
    row.innerHTML = '<div class="form-group"><label>Title</label><input type="text" name="item_title[]" value=""></div><div class="form-group"><label>Description</label><input type="text" name="item_desc[]" value=""></div><button type="button" class="icon-btn icon-btn--danger" title="Remove" onclick="this.closest(\'.service-item\').remove()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg></button>';
    list.appendChild(row);
}
function addContactLink() {
    var list = document.getElementById('contact-links-list');
    var row = document.createElement('div');
    row.className = 'form-row contact-link-item';
    row.innerHTML = '<div class="form-group"><label>Name</label><input type="text" name="link_name[]" value="" placeholder="e.g. WhatsApp"></div><div class="form-group"><label>URL</label><input type="url" name="link_url[]" value="" placeholder="https://..."></div><button type="button" class="icon-btn icon-btn--danger" title="Remove" onclick="this.closest(\'.contact-link-item\').remove()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><path d="M18 6L6 18M6 6l12 12" stroke-linecap="round"/></svg></button>';
    list.appendChild(row);
}
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
