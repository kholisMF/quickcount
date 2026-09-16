<?php
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/includes/helpers.php';
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Quick Count — Desa <?= htmlspecialchars($cfg['app']['desa']) ?></title>
<link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main>
        <div class="topbar">
            <div>
                <h1>Dashboard Perolehan Suara</h1>
                <div class="breadcrumb">Pilkades <?= htmlspecialchars($cfg['app']['desa']) ?> &middot; Kec. <?= htmlspecialchars($cfg['app']['kecamatan']) ?>, Kab. <?= htmlspecialchars($cfg['app']['kabupaten']) ?></div>
            </div>
            <div style="text-align:right;">
                <span class="live-pill"><span class="live-dot"></span> Live</span>
                <div id="last-updated">Menunggu data pertama&hellip;</div>
            </div>
        </div>

        <!-- Stat cards -->
        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-label">Suara masuk</div>
                <div class="stat-value" id="stat-suara-masuk">0 <small>/ <?= number_format($cfg['app']['total_dpt'],0,',','.') ?></small></div>
                <div class="stat-bar"><span id="bar-suara-masuk" style="width:0%"></span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Partisipasi pemilih</div>
                <div class="stat-value" id="stat-partisipasi">0%</div>
                <div class="stat-bar"><span id="bar-partisipasi" style="width:0%"></span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">TPS lapor</div>
                <div class="stat-value" id="stat-tps-lapor">0 <small>/ <?= count($cfg['tps']) ?> TPS</small></div>
                <div class="stat-bar"><span id="bar-tps-lapor" style="width:0%"></span></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Sementara unggul</div>
                <div class="stat-value" id="stat-unggul" style="font-size:18px;">&mdash;</div>
                <div class="stat-bar"><span id="bar-unggul" style="width:0%"></span></div>
            </div>
        </div>

        <!-- Pie chart + leaderboard -->
        <div class="panel-grid">
            <div class="panel">
                <div class="panel-head">
                    <h2>Persentase Perolehan Suara</h2>
                    <span class="hint">dari suara masuk</span>
                </div>
                <div class="chart-wrap">
                    <canvas id="pieChart" width="260" height="260"></canvas>
                    <div class="chart-center-label">
                        <div class="big" id="chart-center-pct">0%</div>
                        <div class="small">dari total DPT</div>
                    </div>
                </div>
                <div class="legend" id="legend"></div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Peringkat Perolehan</h2>
                    <span class="hint">real-time</span>
                </div>
                <div class="leader-list" id="leaderboard"></div>
            </div>
        </div>

        <!-- Detail table -->
        <div class="panel">
            <div class="panel-head">
                <h2>Detail Suara per TPS</h2>
                <span class="hint"><?= count($cfg['tps']) ?> TPS</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>TPS</th>
                            <?php foreach ($cfg['candidates'] as $c): ?>
                                <th class="num"><?= htmlspecialchars($c['nama']) ?></th>
                            <?php endforeach; ?>
                            <th class="num"><?= htmlspecialchars($cfg['tidak_sah']['nama']) ?></th>
                            <th class="num">Total</th>
                            <th class="num">DPT</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="tps-table-body">
                        <!-- diisi via JS realtime -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div class="toast" id="toast"><span class="dot"></span><span id="toast-msg"></span></div>

<?php include __DIR__ . '/includes/firebase-config-inline.php'; ?>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-database-compat.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script src="<?= asset_url('assets/js/dashboard.js') ?>"></script>
<script src="<?= asset_url('assets/js/reset.js') ?>"></script>
</body>
</html>
