<?php
/**
 * Nazarbandi — Home Page
 */
$pageTitle = 'Home';
$showSplash = true;

require_once __DIR__ . '/includes/header.php';

$content = getSiteContent();
$db = getDB();
$hero = $content['hero'];
$about = $content['about'];
$services = $content['services'];

// Hero photos from DB
$heroPhotos = $db->query('
    SELECT p.file_path, p.original_name
    FROM hero_photos hp
    JOIN photos p ON hp.photo_id = p.id
    ORDER BY hp.sort_order, hp.id
')->fetchAll();
?>

<!-- Hero Section -->
<section class="hero" id="home">
    <div class="hero-split">
        <div class="hero-text">
            <p class="kicker"><?= e($hero['kicker']) ?></p>
            <h1><?= e($hero['headingLine1']) ?><br><?= e($hero['headingLine2']) ?></h1>
            <p class="sub"><?= e($hero['sub']) ?></p>
            <a class="cta" href="<?= e($hero['ctaHref']) ?>"><?= e($hero['ctaLabel']) ?></a>
        </div>

        <?php if (!empty($heroPhotos)): ?>
        <div class="hero-photos">
            <div class="hero-marquee">
                <div class="hero-marquee__track">
                    <?php foreach ($heroPhotos as $hp): ?>
                    <div class="hero-marquee__item">
                        <img src="<?= e($hp['file_path']) ?>" alt="<?= e($hp['original_name']) ?>" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                    <!-- Duplicate for seamless loop -->
                    <?php foreach ($heroPhotos as $hp): ?>
                    <div class="hero-marquee__item">
                        <img src="<?= e($hp['file_path']) ?>" alt="<?= e($hp['original_name']) ?>" loading="lazy">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <a class="scroll-cue" href="#work" aria-label="Scroll to work">&darr;</a>
</section>

<!-- Gallery Preview Section -->
<?php
$previewPhotos = $db->query('SELECT file_path, original_name FROM photos ORDER BY RAND() LIMIT 7')->fetchAll();
if (!empty($previewPhotos)):
?>
<section class="gallery" id="work">
    <div class="heading">
        <p class="kicker">Selected work</p>
        <h2>A few frames</h2>
    </div>

    <div class="grid" id="gallery-grid" data-pool='<?= htmlspecialchars(json_encode(array_map(function($p) { return ['src' => $p['file_path'], 'gallery' => $p['file_path'], 'filename' => $p['original_name']]; }, $previewPhotos)), ENT_QUOTES) ?>'></div>
</section>
<?php endif; ?>

<!-- About Section -->
<section class="about" id="about">
    <?php if (!empty($about['photo'])): ?>
        <img class="portrait" src="<?= e($about['photo']) ?>" alt="<?= e($about['heading']) ?>">
    <?php else: ?>
        <div class="portrait"></div>
    <?php endif; ?>

    <div class="copy">
        <p class="kicker"><?= e($about['kicker']) ?></p>
        <h2><?= e($about['heading']) ?></h2>
        <?php foreach ($about['paragraphs'] as $p): ?>
            <p><?= e($p) ?></p>
        <?php endforeach; ?>
        <p class="signature-text"><?= e($about['signature']) ?></p>
    </div>
</section>

<!-- Services Section -->
<section class="services" id="services">
    <div class="heading">
        <p class="kicker"><?= e($services['kicker']) ?></p>
        <h2><?= e($services['heading']) ?></h2>
    </div>

    <div class="services-grid">
        <?php foreach ($services['items'] as $s): ?>
        <div class="card">
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['desc']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Contact Section -->
<section class="contact" id="contact">
    <p class="kicker">Get in touch</p>
    <h2>Let's make something.</h2>
    <p class="sub">For bookings, collaborations or just to say hello.</p>
    <a class="email" href="mailto:ishankothari1999@gmail.com">ishankothari1999@gmail.com</a>

    <div class="social">
        <a href="https://www.instagram.com/ishan_kothari/" target="_blank" rel="noopener noreferrer">Instagram</a>
        <a href="https://vsco.co" target="_blank" rel="noopener noreferrer">VSCO</a>
        <a href="<?= BASE_URL ?>/blog">Blog</a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
