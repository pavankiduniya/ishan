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
// Hero marquee — truly seamless infinite scroll
(function() {
    var track = document.querySelector('.hero-marquee__track');
    if (!track) return;

    var items = track.children;
    var totalItems = items.length;
    var oneSetCount = Math.round(totalItems / 3); // We tripled the set in HTML

    // Wait for images to load to get correct widths
    var images = track.querySelectorAll('img');
    var loaded = 0;
    var gap = 12;

    function startScroll() {
        // Calculate width of one set
        var oneSetWidth = 0;
        for (var i = 0; i < oneSetCount; i++) {
            oneSetWidth += items[i].offsetWidth + gap;
        }

        var speed = 40; // pixels per second
        var offset = 0;
        var lastTime = performance.now();

        function animate(now) {
            var delta = (now - lastTime) / 1000;
            lastTime = now;
            offset -= speed * delta;

            // Reset seamlessly after scrolling one full set
            if (Math.abs(offset) >= oneSetWidth) {
                offset += oneSetWidth;
            }

            track.style.transform = 'translateX(' + offset + 'px)';
            requestAnimationFrame(animate);
        }

        requestAnimationFrame(animate);
    }

    if (images.length === 0) { startScroll(); return; }

    function checkLoaded() {
        loaded++;
        if (loaded >= images.length) startScroll();
    }
    for (var i = 0; i < images.length; i++) {
        if (images[i].complete) { checkLoaded(); }
        else { images[i].addEventListener('load', checkLoaded); images[i].addEventListener('error', checkLoaded); }
    }
})();
</script>
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
            body: JSON.stringify({ path: location.pathname + location.search, clientIp: clientIp }),
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
