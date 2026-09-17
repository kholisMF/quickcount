<?php
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/includes/helpers.php';

const SETTINGS_PASSWORD = 'settingdata';

$settingsError = '';
$settingsUnlocked = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['settings_password'])) {
    if (hash_equals(SETTINGS_PASSWORD, (string) $_POST['settings_password'])) {
        $settingsUnlocked = true;
    } else {
        $settingsError = 'Password salah. Silakan coba lagi.';
    }
}

$settings = load_settings($cfg);
$cfg = apply_settings($cfg, $settings);
$activePage = 'settings';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pengaturan — Quick Count Desa <?= htmlspecialchars($cfg['app']['desa']) ?></title>
<link rel="stylesheet" href="<?= asset_url('assets/css/style.css') ?>">
</head>
<body>
<div class="shell">
    <?php include __DIR__ . '/includes/navbar.php'; ?>

    <main>
        <?php if (!$settingsUnlocked): ?>
            <div class="form-card settings-lock">
                <div class="modal-icon" style="margin:0 auto 16px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                </div>
                <h3 style="text-align:center;margin-bottom:8px;">Restricted Page!</h3>
                <p class="modal-desc" style="text-align:center;">Masukkan password untuk mendapatkan akses!.</p>
                <form method="post" style="text-align:left;">
                    <div class="field">
                        <label for="settings_password">Password</label>
                        <input type="password" name="settings_password" id="settings_password" placeholder="Masukkan password" autocomplete="off" autofocus>
                    </div>
                    <?php if ($settingsError): ?>
                        <p class="modal-error" style="text-align:left;"><?= htmlspecialchars($settingsError) ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn" style="margin-top:6px;">Buka Pengaturan</button>
                </form>
            </div>
        <?php else: ?>
            <input type="hidden" id="settings-token" value="<?= htmlspecialchars($_POST['settings_password']) ?>">
            <div class="topbar">
                <div>
                    <h1>Pengaturan Quick Count</h1>
                    <div class="breadcrumb">Atur calon, warna, jumlah TPS, hak pilih, dan akun petugas TPS. Perubahan di sini tidak ikut terhapus saat "Reset Data".</div>
                </div>
            </div>

            <div class="settings-warn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="18" height="18"><path d="M12 9v4M12 17h.01M10.29 3.86 1.82 18a1 1 0 0 0 .86 1.5h18.64a1 1 0 0 0 .86-1.5L13.71 3.86a1 1 0 0 0-1.72 0Z"/></svg>
                <span>Sebaiknya atur halaman ini <b>sebelum</b> penghitungan suara dimulai. Setiap TPS punya <b>username &amp; password</b> sendiri untuk login ke halaman Input Transaksi. Bagikan kredensial ke petugas masing-masing TPS.</span>
            </div>

            <!-- ===== Daftar Calon ===== -->
            <div class="form-card" style="max-width:100%;">
                <div class="panel-head" style="margin-bottom:16px;">
                    <h2>Daftar Calon Kepala Desa</h2>
                    <span class="hint">warna dipakai di pie chart &amp; seluruh dashboard</span>
                </div>

                <div id="candidate-rows" class="cand-rows">
                    <?php foreach ($cfg['candidates'] as $c): ?>
                        <div class="cand-row" data-id="<?= htmlspecialchars($c['id']) ?>">
                            <input type="color" class="cand-color" value="<?= htmlspecialchars($c['warna']) ?>" title="Warna calon">
                            <input type="text" class="cand-name" value="<?= htmlspecialchars($c['nama']) ?>" placeholder="Nama calon">
                            <button type="button" class="row-remove" title="Hapus calon">&times;</button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="btn-add" id="btn-add-candidate">+ Tambah Calon</button>

                <div class="cand-row" style="margin-top:22px;border-top:1px dashed var(--line);padding-top:20px;">
                    <input type="color" id="tidaksah-color" value="<?= htmlspecialchars($cfg['tidak_sah']['warna']) ?>" title="Warna suara tidak sah">
                    <input type="text" id="tidaksah-name" value="<?= htmlspecialchars($cfg['tidak_sah']['nama']) ?>" placeholder="Label suara tidak sah">
                    <span class="cf-name" style="width:90px;text-align:right;">tetap ada</span>
                </div>
            </div>

            <!-- ===== TPS & Hak Pilih ===== -->
            <!-- ===== TPS, Saksi, Akun Petugas & Hak Pilih ===== -->
            <div class="form-card" style="max-width:100%;margin-top:18px;">
                <div class="panel-head" style="margin-bottom:16px;">
                    <h2>Daftar TPS, Saksi &amp; Akun Petugas</h2>
                    <span class="hint">Setiap TPS punya nama, saksi, dan akun login sendiri</span>
                </div>

                <div class="field">
                    <label>Jumlah TPS</label>
                    <div class="stepper" style="max-width:160px;">
                        <button type="button" class="step-btn minus" id="tps-count-minus">&minus;</button>
                        <div class="cf-value" id="tps-count-display"><?= count($cfg['tps']) ?></div>
                        <button type="button" class="step-btn plus" id="tps-count-plus">+</button>
                    </div>
                </div>

                <div class="field">
                    <label>Detail per TPS</label>
                    <div id="tps-dpt-rows" class="tps-list">
                        <?php foreach ($cfg['tps'] as $i => $tps): ?>
                            <div class="tps-card" data-id="<?= htmlspecialchars($tps['id'] ?? 'tps'.($i+1)) ?>">
                                <div class="tps-card-head">
                                    <span class="tps-card-num">TPS <?= $i + 1 ?></span>
                                    <button type="button" class="tps-card-remove" title="Hapus TPS ini">&times;</button>
                                </div>

                                <div class="tps-card-grid">
                                    <div class="tps-field">
                                        <label>Nama TPS</label>
                                        <input type="text" class="tps-nama-input" value="<?= htmlspecialchars($tps['nama'] ?? ('TPS '.($i+1))) ?>" placeholder="Contoh: TPS 1 Dusun Krajan" autocomplete="off">
                                    </div>

                                    <div class="tps-field">
                                        <label>Jumlah DPT</label>
                                        <input type="number" min="0" step="1" class="tps-dpt-input" value="<?= (int)($tps['dpt'] ?? 0) ?>" placeholder="0">
                                    </div>

                                    <div class="tps-field">
                                        <label>Nama Saksi / Petugas</label>
                                        <input type="text" class="tps-saksi-input" value="<?= htmlspecialchars($tps['saksi'] ?? '') ?>" placeholder="Contoh: Budi Santoso" autocomplete="off">
                                    </div>

                                    <div class="tps-field">
                                        <label>Username Login</label>
                                        <input type="text" class="tps-user-input" value="<?= htmlspecialchars($tps['username'] ?? '') ?>" placeholder="username" autocomplete="off">
                                    </div>

                                    <div class="tps-field">
                                        <label>Password Login</label>
                                        <input type="text" class="tps-pass-input" value="<?= htmlspecialchars($tps['password'] ?? '') ?>" placeholder="password" autocomplete="off">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sum-row" id="tps-sum-row">
                    Jumlah DPT dari semua TPS: <b id="tps-sum-total">0</b>
                    <button type="button" class="btn-mini" id="btn-copy-sum">Pakai angka ini &rarr;</button>
                </div>

                <div class="field">
                    <label for="total-dpt">Total Hak Suara (DPT keseluruhan)</label>
                    <input type="number" min="0" step="1" id="total-dpt" value="<?= (int)$cfg['app']['total_dpt'] ?>">
                </div>
            </div>

            <div class="settings-actions">
                <div class="sync-status" id="settings-status">
                    <span class="sync-dot" id="settings-sync-dot"></span>
                    <span id="settings-sync-text">Belum ada perubahan</span>
                </div>
                <button type="button" class="btn" id="btn-save-settings" style="width:auto;padding:12px 28px;">Simpan Pengaturan</button>
            </div>
        <?php endif; ?>
    </main>
</div>

<div class="toast" id="toast"><span class="dot"></span><span id="toast-msg"></span></div>

<?php include __DIR__ . '/includes/firebase-config-inline.php'; ?>
<?php if ($settingsUnlocked): ?>
<script src="<?= asset_url('assets/js/settings.js') ?>"></script>
<?php endif; ?>
<script src="<?= asset_url('assets/js/reset.js') ?>"></script>
</body>
</html>