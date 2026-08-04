<!-- Footer -->
<footer>
    <p class="signature">ik</p>
    <p class="copyright">&copy; <?= date('Y') ?> Nazarbandi. All rights reserved. <span class="visit-counter" id="visit-count" title="Total visits"></span></p>
</footer>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" hidden>
    <button type="button" class="lightbox-close" aria-label="Close">&times;</button>
    <div class="lightbox-frame">
        <img id="lightbox-img" src="" alt="" draggable="false">
        <span class="watermark">ik</span>
    </div>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js?v=<?= time() ?>"></script>
<?php if ($showSplash ?? false): ?>
<script src="<?= BASE_URL ?>/assets/js/splash.js?v=<?= time() ?>"></script>
<?php endif; ?>
<script>
// Visit tracking beacon + live counter polling
(function() {
    var countEl = document.getElementById('visit-count');
    var currentCount = 0;

    function renderDigits(num) {
        if (num === currentCount) return;
        currentCount = num;
        var digits = String(num).split('');
        countEl.title = num.toLocaleString() + ' visits';
        countEl.innerHTML = '';
        digits.forEach(function(d) {
            var box = document.createElement('span');
            box.style.display = 'inline-flex';
            box.style.alignItems = 'center';
            box.style.justifyContent = 'center';
            box.style.width = '12px';
            box.style.height = '16px';
            box.style.background = '#111';
            box.style.color = '#fff';
            box.style.fontSize = '8px';
            box.style.fontWeight = '500';
            box.style.borderRadius = '2px';
            box.style.transition = 'transform 0.3s ease';
            box.textContent = d;
            countEl.appendChild(box);
        });
    }

    function getPublicIp() {
        return fetch('https://api.ipify.org?format=json')
            .then(function(res) { return res.ok ? res.json() : null; })
            .then(function(data) { return (data && data.ip) ? data.ip : ''; })
            .catch(function() { return ''; });
    }

    // Initial track (records the visit + gets count)
    getPublicIp().then(function(clientIp) {
        fetch('<?= BASE_URL ?>/api/track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ path: location.pathname, clientIp: clientIp }),
            keepalive: true
        })
        .then(function(res) { return res.ok ? res.json() : null; })
        .then(function(data) {
            if (data && typeof data.totalViews === 'number') {
                renderDigits(data.totalViews);
            }
        })
        .catch(function() {});
    });

    // Silent poll every 10s to keep counter live
    setInterval(function() {
        fetch('<?= BASE_URL ?>/api/track.php?count=1', { cache: 'no-store' })
            .then(function(res) { return res.ok ? res.json() : null; })
            .then(function(data) {
                if (data && typeof data.totalViews === 'number') {
                    renderDigits(data.totalViews);
                }
            })
            .catch(function() {});
    }, 10000);
})();
</script>
</body>
</html>
