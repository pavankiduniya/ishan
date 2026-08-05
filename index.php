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
                    <?php for ($loop = 0; $loop < 3; $loop++): ?>
                        <?php foreach ($heroPhotos as $hp): ?>
                        <div class="hero-marquee__item">
                            <img src="<?= e($hp['file_path']) ?>" alt="<?= e($hp['original_name']) ?>">
                        </div>
                        <?php endforeach; ?>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

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
<?php $contact = $content['contact']; ?>
<section class="contact" id="contact">
    <p class="kicker">Get in touch</p>
    <h2>Let's make something.</h2>
    <p class="sub">For bookings, collaborations or just to say hello.</p>
    <a class="email" href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>

    <div class="social">
        <?php foreach ($contact['links'] as $link): ?>
        <a href="<?= e($link['url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= e($link['name']) ?>">
            <?= getSocialIcon($link['name']) ?>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
