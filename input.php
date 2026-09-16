<?php
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/includes/helpers.php';
$activePage = 'input';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Input Transaksi TPS — Quick Count Desa <?= htmlspecialchars($cfg['app']['desa']) ?></title>
<link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main>
        <div class="topbar">
            <div>
                <h1>Input Transaksi Suara per TPS</h1>
                <div class="breadcrumb">Pilih TPS lalu ketuk + / − untuk menghitung suara — setiap perubahan otomatis tersimpan ke database</div>
            </div>
            <span class="live-pill"><span class="live-dot"></span> Connected</span>
        </div>

        <div class="panel-grid panel-grid--input">
            <div class="form-card">
                <div class="field">
                    <label for="tps-select">Pilih TPS</label>
                    <select id="tps-select">
                        <option value="">— Pilih TPS —</option>
                        <?php foreach ($cfg['tps'] as $tps): ?>
                            <option value="<?= htmlspecialchars($tps['id']) ?>" data-dpt="<?= (int)$tps['dpt'] ?>">
                                <?= htmlspecialchars($tps['nama']) ?> (DPT <?= number_format($tps['dpt'],0,',','.') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Jumlah suara sah per calon</label>
                    <div class="candidate-grid">
                        <?php foreach ($cfg['candidates'] as $c): ?>
                            <div class="candidate-field" data-id="<?= htmlspecialchars($c['id']) ?>">
                                <div class="cf-head">
                                    <span class="cf-swatch" style="background:<?= htmlspecialchars($c['warna']) ?>"></span>
                                    <span class="cf-name">No. <?= (int)$c['no'] ?> &middot; <?= htmlspecialchars($c['nama']) ?></span>
                                </div>
                                <div class="stepper">
                                    <button type="button" class="step-btn minus" data-dir="-1" aria-label="Kurangi suara">&minus;</button>
                                    <div class="cf-value" data-value="0">0</div>
                                    <button type="button" class="step-btn plus" data-dir="1" aria-label="Tambah suara">+</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="candidate-field" data-id="<?= htmlspecialchars($cfg['tidak_sah']['id']) ?>">
                            <div class="cf-head">
                                <span class="cf-swatch" style="background:<?= htmlspecialchars($cfg['tidak_sah']['warna']) ?>"></span>
                                <span class="cf-name"><?= htmlspecialchars($cfg['tidak_sah']['nama']) ?></span>
                            </div>
                            <div class="stepper">
                                <button type="button" class="step-btn minus" data-dir="-1" aria-label="Kurangi suara">&minus;</button>
                                <div class="cf-value" data-value="0">0</div>
                                <button type="button" class="step-btn plus" data-dir="1" aria-label="Tambah suara">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sum-row" id="sum-row">
                    Total suara diinput: <b id="sum-total">0</b>&nbsp;/&nbsp;<span id="sum-dpt">DPT —</span>
                </div>

                <div class="sync-status" id="sync-status">
                    <span class="sync-dot" id="sync-dot"></span>
                    <span id="sync-text">Pilih TPS untuk mulai input — setiap tambah/kurang otomatis tersimpan</span>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <h2>Status Pelaporan TPS</h2>
                </div>
                <div class="status-list" id="status-list">
                    <div class="empty-hint">Memuat status TPS&hellip;</div>
                </div>
            </div>
        </div>
    </main>
</div>

<div class="toast" id="toast"><span class="dot"></span><span id="toast-msg"></span></div>

<?php include __DIR__ . '/includes/firebase-config-inline.php'; ?>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-database-compat.js"></script>
<script src="<?= asset_url('assets/js/input.js') ?>"></script>
<script src="<?= asset_url('assets/js/reset.js') ?>"></script>
</body>
</html>
