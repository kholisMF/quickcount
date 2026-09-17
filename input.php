<?php
session_start();
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/includes/helpers.php';

$settings = load_settings($cfg);
$cfg = apply_settings($cfg, $settings);
$activePage = 'input';

$loginError = '';
$loggedTps = null;

// Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['tps_id'], $_SESSION['tps_nama']);
    header('Location: input.php');
    exit;
}

// Cek session dulu
if (!empty($_SESSION['tps_id'])) {
    foreach ($cfg['tps'] as $t) {
        if ($t['id'] === $_SESSION['tps_id']) {
            $loggedTps = $t;
            break;
        }
    }
    if (!$loggedTps) {
        unset($_SESSION['tps_id'], $_SESSION['tps_nama']);
    }
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_username'])) {
    $u = trim($_POST['login_username'] ?? '');
    $p = trim($_POST['login_password'] ?? '');
    $found = null;
    foreach ($cfg['tps'] as $t) {
        if (strcasecmp($t['username'] ?? '', $u) === 0 && hash_equals($t['password'] ?? '', $p)) {
            $found = $t;
            break;
        }
    }
    if ($found) {
        $_SESSION['tps_id']   = $found['id'];
        $_SESSION['tps_nama'] = $found['nama'];
        header('Location: input.php');
        exit;
    } else {
        $loginError = 'Username atau password salah.';
    }
}
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
        <?php if (!$loggedTps): ?>
            <div class="form-card settings-lock" style="margin-top:40px;">
                <div class="modal-icon" style="margin:0 auto 16px;background:rgba(37,99,235,.1);border-color:rgba(37,99,235,.3);color:#2563EB;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="24" height="24"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                </div>
                <h3 style="text-align:center;margin-bottom:8px;">Login Petugas TPS</h3>
                <p class="modal-desc" style="text-align:center;">Masukkan username &amp; password TPS Anda untuk mulai input suara.</p>
                <form method="post" style="text-align:left;">
                    <div class="field">
                        <label for="login_username">Username</label>
                        <input type="text" name="login_username" id="login_username" placeholder="username TPS" autocomplete="off" autofocus>
                    </div>
                    <div class="field">
                        <label for="login_password">Password</label>
                        <input type="password" name="login_password" id="login_password" placeholder="password TPS" autocomplete="off">
                    </div>
                    <?php if ($loginError): ?>
                        <p class="modal-error" style="text-align:left;"><?= htmlspecialchars($loginError) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn" style="margin-top:6px;">Masuk</button>
                </form>
            </div>
        <?php else: ?>
            <div class="topbar">
                <div>
                    <h1>Input Transaksi Suara — <?= htmlspecialchars($loggedTps['nama']) ?></h1>
                    <div class="breadcrumb">
                        Saksi/Petugas: <b><?= htmlspecialchars($loggedTps['saksi'] ?: $loggedTps['username']) ?></b>
                        &middot; DPT <?= number_format($loggedTps['dpt'], 0, ',', '.') ?> pemilih
                    </div>
                </div>
                <a href="input.php?logout=1" class="live-pill" style="text-decoration:none;">
                    <span class="live-dot" style="background:var(--green);"></span> Keluar
                </a>
            </div>

            <div class="form-card" style="max-width:100%;">
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
                    Total suara diinput: <b id="sum-total">0</b>&nbsp;/&nbsp;<span id="sum-dpt">DPT <?= number_format($loggedTps['dpt'],0,',','.') ?></span>
                </div>

                <div class="sync-status" id="sync-status">
                    <span class="sync-dot" id="sync-dot"></span>
                    <span id="sync-text">Setiap tambah/kurang otomatis tersimpan ke server</span>
                </div>
            </div>

            <!-- config untuk JS -->
            <script>
                window.TPS_ID       = <?= json_encode($loggedTps['id']) ?>;
                window.TPS_DPT      = <?= (int)$loggedTps['dpt'] ?>;
                window.CANDIDATES   = <?= json_encode(array_values($cfg['candidates'])) ?>;
                window.TIDAK_SAH    = <?= json_encode($cfg['tidak_sah']) ?>;
            </script>
        <?php endif; ?>
    </main>
</div>

<div class="toast" id="toast"><span class="dot"></span><span id="toast-msg"></span></div>

<?php include __DIR__ . '/includes/firebase-config-inline.php'; ?>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-database-compat.js"></script>
<script>
    window.TPS_ID       = <?= json_encode($loggedTps['id']) ?>;
    window.TPS_DPT      = <?= (int)$loggedTps['dpt'] ?>;
    window.CANDIDATES   = <?= json_encode(array_values($cfg['candidates'])) ?>;
    window.TIDAK_SAH    = <?= json_encode($cfg['tidak_sah']) ?>;
</script>
<?php if ($loggedTps): ?>
<script src="<?= asset_url('assets/js/input.js') ?>"></script>
<?php endif; ?>
<script src="<?= asset_url('assets/js/reset.js') ?>"></script>
</body>
</html>