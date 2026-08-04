<?php
/**
 * Admin — Analytics (MySQL-backed, modern dashboard style)
 */
$adminTitle = 'Analytics';
$adminActive = 'analytics';
require_once __DIR__ . '/layout_head.php';

$db = getDB();

// Total stats
$totalViews = (int)$db->query('SELECT COUNT(*) FROM visits')->fetchColumn();
$totalVisitors = (int)$db->query('SELECT COUNT(*) FROM visitors')->fetchColumn();
$totalPages = (int)$db->query('SELECT COUNT(DISTINCT path) FROM visits')->fetchColumn();
$todayViews = (int)$db->query("SELECT COUNT(*) FROM visits WHERE DATE(visited_at) = CURDATE()")->fetchColumn();

// All daily data (used by JS to filter ranges client-side)
$allDailyRows = $db->query("
    SELECT DATE(visited_at) as day, COUNT(*) as views, COUNT(DISTINCT visitor_id) as uniq
    FROM visits
    GROUP BY DATE(visited_at)
    ORDER BY day ASC
")->fetchAll();

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

<!-- Stat Cards -->
<div class="dash-stats">
    <div class="dash-card dash-card--blue">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5m0 14h16M8 19v-6m4.5 6V9m4.5 10v-4" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= number_format($totalViews) ?></p>
            <p class="dash-card__label">Total Page Views</p>
        </div>
        <p class="dash-card__sub">Today: <?= $todayViews ?></p>
    </div>

    <div class="dash-card dash-card--green">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= number_format($totalVisitors) ?></p>
            <p class="dash-card__label">Unique Visitors</p>
        </div>
        <p class="dash-card__sub"><?= $totalPages ?> pages tracked</p>
    </div>

    <div class="dash-card dash-card--purple">
        <div class="dash-card__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </div>
        <div class="dash-card__body">
            <p class="dash-card__value"><?= $totalViews > 0 ? round($totalViews / max(1, count($allDailyRows))) : 0 ?></p>
            <p class="dash-card__label">Avg. Views/Day</p>
        </div>
        <p class="dash-card__sub"><?= count($allDailyRows) ?> days tracked</p>
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
    <!-- Top Pages -->
    <section class="dash-panel">
        <div class="dash-panel__header">
            <h2>Top Pages</h2>
        </div>
        <?php if (empty($topPages)): ?>
            <p class="empty">No page views recorded yet.</p>
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

    <!-- Visitor Locations -->
    <section class="dash-panel">
        <div class="dash-panel__header">
            <h2>Visitor Locations</h2>
        </div>
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
    <div class="dash-panel__header">
        <h2>Recent Visits</h2>
    </div>
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

<!-- Line Chart Script (Canvas-based, no dependencies) -->
<script>
(function() {
    var allData = <?= json_encode(array_map(function($r) {
        return ['day' => $r['day'], 'views' => (int)$r['views'], 'unique' => (int)$r['uniq']];
    }, $allDailyRows)) ?>;

    var canvas = document.getElementById('traffic-chart');
    var ctx = canvas.getContext('2d');
    var activeDays = 15;

    function getFilteredData(days) {
        if (days === 0) return allData;
        return allData.slice(-days);
    }

    function drawChart(days) {
        var data = getFilteredData(days);
        if (data.length === 0) { ctx.clearRect(0, 0, canvas.width, canvas.height); return; }

        var dpr = window.devicePixelRatio || 1;
        var rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width * dpr;
        canvas.height = 220 * dpr;
        canvas.style.width = rect.width + 'px';
        canvas.style.height = '220px';
        ctx.scale(dpr, dpr);

        var w = rect.width;
        var h = 220;
        var padTop = 20, padBottom = 30, padLeft = 40, padRight = 20;
        var chartW = w - padLeft - padRight;
        var chartH = h - padTop - padBottom;

        ctx.clearRect(0, 0, w, h);

        var maxVal = Math.max(1, Math.max.apply(null, data.map(function(d) { return d.views; })));

        // Grid lines
        ctx.strokeStyle = '#f0f0f0';
        ctx.lineWidth = 1;
        for (var i = 0; i <= 4; i++) {
            var y = padTop + (chartH / 4) * i;
            ctx.beginPath();
            ctx.moveTo(padLeft, y);
            ctx.lineTo(w - padRight, y);
            ctx.stroke();

            // Y-axis labels
            ctx.fillStyle = '#999';
            ctx.font = '10px Helvetica Neue, Arial, sans-serif';
            ctx.textAlign = 'right';
            var val = Math.round(maxVal - (maxVal / 4) * i);
            ctx.fillText(val, padLeft - 8, y + 4);
        }

        // X-axis labels
        ctx.fillStyle = '#999';
        ctx.font = '10px Helvetica Neue, Arial, sans-serif';
        ctx.textAlign = 'center';
        var step = Math.max(1, Math.floor(data.length / 8));
        for (var i = 0; i < data.length; i += step) {
            var x = padLeft + (chartW / (data.length - 1 || 1)) * i;
            var label = data[i].day.slice(5); // MM-DD
            ctx.fillText(label, x, h - 8);
        }

        function drawLine(key, color, fill) {
            var points = [];
            for (var i = 0; i < data.length; i++) {
                var x = padLeft + (chartW / (data.length - 1 || 1)) * i;
                var y = padTop + chartH - (data[i][key] / maxVal) * chartH;
                points.push({ x: x, y: y });
            }

            // Fill area
            ctx.beginPath();
            ctx.moveTo(points[0].x, padTop + chartH);
            points.forEach(function(p) { ctx.lineTo(p.x, p.y); });
            ctx.lineTo(points[points.length - 1].x, padTop + chartH);
            ctx.closePath();
            ctx.fillStyle = fill;
            ctx.fill();

            // Line
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (var i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.strokeStyle = color;
            ctx.lineWidth = 2.5;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            ctx.stroke();

            // Dots
            points.forEach(function(p) {
                ctx.beginPath();
                ctx.arc(p.x, p.y, 3, 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();
            });
        }

        drawLine('views', '#4a90d9', 'rgba(74, 144, 217, 0.08)');
        drawLine('unique', '#43b794', 'rgba(67, 183, 148, 0.08)');
    }

    // Range tab buttons
    var tabs = document.querySelectorAll('.range-tab');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            tabs.forEach(function(t) { t.classList.remove('active'); });
            tab.classList.add('active');
            activeDays = parseInt(tab.dataset.days);
            drawChart(activeDays);
        });
    });

    drawChart(activeDays);
    window.addEventListener('resize', function() { drawChart(activeDays); });
})();
</script>

<?php require_once __DIR__ . '/layout_foot.php'; ?>
