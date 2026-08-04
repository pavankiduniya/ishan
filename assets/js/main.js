/**
 * Nazarbandi — Main JS
 * Mobile nav toggle, lightbox, homepage gallery shuffle
 */

// Mobile menu toggle
const toggle = document.getElementById('menu-toggle');
const links = document.getElementById('nav-links');
if (toggle && links) {
    toggle.addEventListener('click', () => {
        links.classList.toggle('open');
    });
}

// Lightbox
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightbox-img');
const closeBtn = lightbox ? lightbox.querySelector('.lightbox-close') : null;

function openLightbox(src, alt) {
    if (!lightbox || !lightboxImg) return;
    lightboxImg.src = src;
    lightboxImg.alt = alt;
    lightbox.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    if (!lightbox || !lightboxImg) return;
    lightbox.hidden = true;
    lightboxImg.src = '';
    document.body.style.overflow = '';
}

if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
if (lightbox) {
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) closeLightbox();
    });
}

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeLightbox();
});

// Right-click prevention on photos
document.addEventListener('contextmenu', function (e) {
    if (e.target.closest('.photo-grid figure img, #gallery-grid figure img')) {
        e.preventDefault();
    }
});
if (lightboxImg) {
    lightboxImg.addEventListener('contextmenu', function (e) { e.preventDefault(); });
}

// Delegated click for photo grids → lightbox
document.addEventListener('click', function (e) {
    var img = e.target.closest('.photo-grid figure img, #gallery-grid figure img');
    if (img) {
        openLightbox(img.dataset.full || img.src, img.alt);
    }
});

// Homepage gallery grid (shuffle + render)
function shuffle(arr) {
    var a = arr.slice();
    for (var i = a.length - 1; i > 0; i--) {
        var j = Math.floor(Math.random() * (i + 1));
        var tmp = a[i]; a[i] = a[j]; a[j] = tmp;
    }
    return a;
}

function renderGallery() {
    var grid = document.getElementById('gallery-grid');
    if (!grid) return;

    grid.innerHTML = '';
    var pool = [];
    try { pool = JSON.parse(grid.dataset.pool || '[]'); } catch (e) { return; }

    var selected = shuffle(pool).slice(0, 7);

    selected.forEach(function (photo) {
        var figure = document.createElement('figure');
        var img = document.createElement('img');
        img.src = photo.gallery;
        img.dataset.full = photo.src;
        img.alt = photo.filename;
        img.loading = 'lazy';
        figure.appendChild(img);

        var watermark = document.createElement('span');
        watermark.className = 'watermark';
        watermark.textContent = 'ik';
        figure.appendChild(watermark);

        grid.appendChild(figure);
    });

    var seeMore = document.createElement('a');
    seeMore.href = 'gallery.php';
    seeMore.className = 'see-more';
    seeMore.innerHTML = '<span>See more</span><span class="arrow">&rarr;</span>';
    grid.appendChild(seeMore);
}

renderGallery();
window.addEventListener('pageshow', renderGallery);
