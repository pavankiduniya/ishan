<!-- Footer -->
<footer>
    <p class="signature">ik</p>
    <p class="copyright">&copy; <?= date('Y') ?> Nazarbandi. All rights reserved.</p>
</footer>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="Close">&times;</button>
    <div class="lightbox-frame">
        <img id="lightbox-img" src="" alt="" draggable="false">
        <span class="watermark">ik</span>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if ($showSplash ?? false): ?>
<script src="<?= BASE_URL ?>/assets/js/splash.js"></script>
<?php endif; ?>
</body>
</html>
