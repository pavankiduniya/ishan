<!-- Footer -->
<footer>
    <p class="signature">ik</p>
    <p class="visits">Total visits: <span id="visit-count">&ndash;</span></p>
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
<script>
// Visit tracking beacon — fires on every page load, shows total in footer
(function() {
    var countEl = document.getElementById('visit-count');

    function getPublicIp() {
        return fetch('https://api.ipify.org?format=json')
            .then(function(res) { return res.ok ? res.json() : null; })
            .then(function(data) { return (data && data.ip) ? data.ip : ''; })
            .catch(function() { return ''; });
    }

    getPublicIp().then(function(clientIp) {
        fetch('<?= BASE_URL ?>/api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: location.pathname, clientIp: clientIp }),
            keepalive: true
        })
        .then(function(res) { return res.ok ? res.json() : null; })
        .then(function(data) {
            if (countEl && data && typeof data.totalViews === 'number') {
                countEl.textContent = data.totalViews.toLocaleString();
            }
        })
        .catch(function() {});
    });
})();
</script>
</body>
</html>
