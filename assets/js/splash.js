/**
 * Nazarbandi — Splash Animation
 */
(function () {
    var splash = document.getElementById('splash');
    if (!splash) return;

    if (window.location.hash) {
        splash.style.display = 'none';
        return;
    }

    var word = 'NAZARBANDI';
    var wordEl = document.getElementById('word');
    var byline = document.getElementById('byline');
    var lineTop = document.getElementById('line-top');
    var lineBottom = document.getElementById('line-bottom');

    if (!wordEl || !byline || !lineTop || !lineBottom) return;

    word.split('').forEach(function (ch) {
        var span = document.createElement('span');
        span.textContent = ch;
        wordEl.appendChild(span);
    });

    requestAnimationFrame(function () {
        setTimeout(function () {
            lineTop.style.width = '200px';
            lineBottom.style.width = '200px';
        }, 50);

        var letters = wordEl.children;
        for (var i = 0; i < letters.length; i++) {
            (function (idx) {
                setTimeout(function () {
                    letters[idx].style.opacity = '1';
                    letters[idx].style.transform = 'translateY(0)';
                    letters[idx].style.filter = 'blur(0px)';
                }, 300 + idx * 80);
            })(i);
        }

        var bylineDelay = 300 + letters.length * 80 + 250;
        setTimeout(function () {
            byline.style.opacity = '1';
            byline.style.transform = 'translateY(0)';
            byline.style.filter = 'blur(0px)';
        }, bylineDelay);
    });

    setTimeout(function () {
        splash.style.opacity = '0';
        setTimeout(function () {
            splash.style.display = 'none';
        }, 800);
    }, 3000);
})();
