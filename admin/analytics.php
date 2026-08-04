<?php
/**
 * Admin — Analytics
 */
$adminTitle = 'Analytics';
$adminActive = 'analytics';
require_once __DIR__ . '/layout_head.php';

$db = getDB();

// Top pages
$topPages = $db->query('
    SELECT path, COUNT(*) as views, COUNT(DISTINCT visitor_id) as unique_visitors
    FROM visits GROUP BY path ORDER BY views DESC LIMIT 15
')->fetchAll();

// Visitor locations
$locations = $db->query("
    SELECT city, region, country, lat, lon, COUNT(*) as visitors
    FROM visitors WHERE country != '' AND (lat != 0 OR lon != 0)
    GROUP BY city, region, country, lat, lon ORDER BY visitors DESC LIMIT 30
")->fetchAll();

// Recent visits
$recentVisits = $db->query("
    SELECT path, visited_at, ip, city, region, country
    FROM visits ORDER BY visited_at DESC LIMIT 20
")->fetchAll();
?>

<!-- Stat Cards (live updated) -->
<div class="dash-stats">
    <div class="dash-card dash-card--blue">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16M8 19v-6m4.5 6V9m4.5 10v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value" id="stat-views">—</p>
            <p class="dash-card__label">Total Page Views</p>
        </div>
        <p class="dash-card__sub">Today: <span id="stat-today-views">—</span></p>
    </div>

    <div class="dash-card dash-card--green">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value" id="stat-visitors">—</p>
            <p class="dash-card__label">Unique Visitors</p>
        </div>
        <p class="dash-card__sub"><span id="stat-pages">—</span> pages tracked</p>
    </div>

    <div class="dash-card dash-card--purple">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M22 12h-4l-3 9L9 3l-3 9H2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value" id="stat-avg">—</p>
            <p class="dash-card__label">Avg. Views/Day</p>
        </div>
        <p class="dash-card__sub"><span id="stat-days">—</span> days tracked</p>
    </div>

    <div class="dash-card dash-card--orange">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0Z M12 13a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= count($locations) ?></p>
            <p class="dash-card__label">Locations</p>
        </div>
        <p class="dash-card__sub"><?= !empty($locations) ? e($locations[0]['country']) . ' top' : 'No data' ?></p>
    </div>
</div>

<!-- Traffic Line Chart -->
<section class="dash-panel">
    <div class="dash-panel__header">
        <h2>Traffic Overview</h2>
        <div class="range-tabs" id="range-tabs">
            <button class="range-tab" data-days="2">2D</button>
            <button class="range-tab" data-days="5">5D</button>
            <button class="range-tab" data-days="10">10D</button>
            <button class="range-tab active" data-days="15">15D</button>
            <button class="range-tab" data-days="30">30D</button>
            <button class="range-tab" data-days="0">All</button>
        </div>
    </div>
    <div class="dash-legend">
        <span class="dash-legend__item"><span class="dot dot--blue"></span> Views</span>
        <span class="dash-legend__item"><span class="dot dot--green"></span> Unique Visitors</span>
    </div>
    <div class="line-chart-wrap">
        <canvas id="traffic-chart" height="220"></canvas>
    </div>
</section>

<!-- Tables Row -->
<div class="dash-row">
    <section class="dash-panel">
        <div class="dash-panel__header"><h2>Top Pages</h2></div>
        <?php if (empty($topPages)): ?>
            <p class="empty">No data yet.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Page</th><th>Views</th><th>Unique</th></tr></thead>
            <tbody>
                <?php foreach ($topPages as $page): ?>
                <tr>
                    <td><code><?= e($page['path']) ?></code></td>
                    <td><?= number_format($page['views']) ?></td>
                    <td><?= number_format($page['unique_visitors']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>

    <section class="dash-panel">
        <div class="dash-panel__header"><h2>Visitor Locations</h2></div>
        <?php if (empty($locations)): ?>
            <p class="empty">No location data yet.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>City</th><th>Country</th><th>Visitors</th></tr></thead>
            <tbody>
                <?php foreach (array_slice($locations, 0, 10) as $loc): ?>
                <tr>
                    <td><?= e($loc['city'] ?: '—') ?></td>
                    <td><?= e($loc['country']) ?></td>
                    <td><?= $loc['visitors'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>
</div>

<!-- Recent Visits -->
<section class="dash-panel">
    <div class="dash-panel__header"><h2>Recent Visits</h2></div>
    <?php if (empty($recentVisits)): ?>
        <p class="empty">No visits recorded yet.</p>
    <?php else: ?>
    <table class="data-table">
        <thead><tr><th>Page</th><th>Time</th><th>IP</th><th>Location</th></tr></thead>
        <tbody>
            <?php foreach ($recentVisits as $v):
                $parts = array_filter([$v['city'], $v['region'], $v['country']]);
                $location = $parts ? implode(', ', $parts) : 'Local';
            ?>
            <tr>
                <td><code><?= e($v['path']) ?></code></td>
                <td><?= date('M j, g:ia', strtotime($v['visited_at'])) ?></td>
                <td><code><?= e($v['ip']) ?></code></td>
                <td><?= e($location) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</section>

<script>
(function() {
    var canvas = document.getElementById('traffic-chart');
    var ctx = canvas.getContext('2d');
    var activeDays = 15;
    var chartData = [];

    // Fill missing days between start and end with 0
    function fillDays(data, days) {
        var filled = [];
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        var startDays = days === 0 ? (data.length > 0 ? Math.ceil((today - new Date(data[0].day)) / 86400000) + 1 : 1) : days;

        var dataMap = {};
        data.forEach(function(d) { dataMap[d.day] = d; });

        for (var i = startDays - 1; i >= 0; i--) {
            var d = new Date(today);
            d.setDate(d.getDate() - i);
            var key = d.toISOString().slice(0, 10);
            filled.push(dataMap[key] || { day: key, views: 0, unique: 0 });
        }
        return filled;
    }

    function drawChart() {
        var data = fillDays(chartData, activeDays);
        if (data.length === 0) return;

        var dpr = window.devicePixelRatio || 1;
        var rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = 220 * dpr;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = '220px';
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);

        var w = rect.width, h = 220;
        var padTop = 20, padBottom = 30, padLeft = 45, padRight = 20;
        var chartW = w - padLeft - padRight;
        var chartH = h - padTop - padBottom;

        ctx.clearRect(0, 0, w, h);

        var maxVal = Math.max(1, Math.max.apply(null, data.map(function(d) { return Math.max(d.views, d.unique); })));

        // Grid
        ctx.strokeStyle = '#f0f0f0'; ctx.lineWidth = 1;
        for (var i = 0; i <= 4; i++) {
            var y = padTop + (chartH / 4) * i;
            ctx.beginPath(); ctx.moveTo(padLeft, y); ctx.lineTo(w - padRight, y); ctx.stroke();
            ctx.fillStyle = '#999'; ctx.font = '10px Helvetica Neue, Arial, sans-serif'; ctx.textAlign = 'right';
            ctx.fillText(Math.round(maxVal - (maxVal / 4) * i), padLeft - 8, y + 4);
        }

        // X labels
        ctx.fillStyle = '#999'; ctx.font = '10px Helvetica Neue, Arial, sans-serif'; ctx.textAlign = 'center';
        var labelStep = Math.max(1, Math.floor(data.length / 10));
        for (var i = 0; i < data.length; i += labelStep) {
            var x = padLeft + (data.length > 1 ? (chartW / (data.length - 1)) * i : chartW / 2);
            ctx.fillText(data[i].day.slice(5), x, h - 8);
        }
        // Always show last label
        if (data.length > 1) {
            var lastX = padLeft + chartW;
            ctx.fillText(data[data.length - 1].day.slice(5), lastX, h - 8);
        }

        function drawLine(key, color, fillColor) {
            if (data.length < 2) {
                // Single point — draw a dot
                var px = padLeft + chartW / 2;
                var py = padTop + chartH - (data[0][key] / maxVal) * chartH;
                ctx.beginPath(); ctx.arc(px, py, 5, 0, Math.PI * 2);
                ctx.fillStyle = color; ctx.fill();
                return;
            }

            var points = [];
            for (var i = 0; i < data.length; i++) {
                var x = padLeft + (chartW / (data.length - 1)) * i;
                var y = padTop + chartH - (data[i][key] / maxVal) * chartH;
                points.push({ x: x, y: y });
            }

            // Smooth curve (bezier)
            ctx.beginPath();
            ctx.moveTo(points[0].x, padTop + chartH);
            ctx.lineTo(points[0].x, points[0].y);
            for (var i = 1; i < points.length; i++) {
                var cpx = (points[i - 1].x + points[i].x) / 2;
                ctx.bezierCurveTo(cpx, points[i - 1].y, cpx, points[i].y, points[i].x, points[i].y);
            }
            ctx.lineTo(points[points.length - 1].x, padTop + chartH);
            ctx.closePath();
            ctx.fillStyle = fillColor; ctx.fill();

            // Line
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (var i = 1; i < points.length; i++) {
                var cpx = (points[i - 1].x + points[i].x) / 2;
                ctx.bezierCurveTo(cpx, points[i - 1].y, cpx, points[i].y, points[i].x, points[i].y);
            }
            ctx.strokeStyle = color; ctx.lineWidth = 2.5;
            ctx.lineJoin = 'round'; ctx.lineCap = 'round'; ctx.stroke();

            // Dots
            points.forEach(function(p) {
                ctx.beginPath(); ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
                ctx.fillStyle = '#fff'; ctx.fill();
                ctx.beginPath(); ctx.arc(p.x, p.y, 3.5, 0, Math.PI * 2);
                ctx.strokeStyle = color; ctx.lineWidth = 2; ctx.stroke();
            });
        }

        drawLine('views', '#4a90d9', 'rgba(74, 144, 217, 0.1)');
        drawLine('unique', '#43b794', 'rgba(67, 183, 148, 0.1)');
    }

    function updateStats(data) {
        var el;
        el = document.getElementById('stat-views'); if (el) el.textContent = data.totalViews.toLocaleString();
        el = document.getElementById('stat-today-views'); if (el) el.textContent = data.todayViews;
        el = document.getElementById('stat-visitors'); if (el) el.textContent = data.totalVisitors.toLocaleString();
        el = document.getElementById('stat-pages'); if (el) el.textContent = data.totalPages;
        el = document.getElementById('stat-avg'); if (el) el.textContent = data.avgPerDay;
        el = document.getElementById('stat-days'); if (el) el.textContent = data.daysTracked;
    }

    function fetchAndRender() {
        fetch('<?= BASE_URL ?>/api/stats', { cache: 'no-store' })
            .then(function(r) { return r.ok ? r.json() : null; })
            .then(function(data) {
                if (!data || data.error) return;
                chartData = data.chartData || [];
                updateStats(data);
                drawChart();
            })
            .catch(function() {});
    }

    // Range tabs
    document.querySelectorAll('.range-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.range-tab').forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');
            activeDays = parseInt(tab.dataset.days);
            drawChart();
        });
    });

    // Initial load + auto-refresh every 10s
    fetchAndRender();
    setInterval(fetchAndRender, 10000);
    window.addEventListener('resize', drawChart);
})();
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
