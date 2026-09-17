<?php
/** @var string $activePage */
$activePage = $activePage ?? '';
$cfg = $cfg ?? [
    'app' => [
        'desa' => '',
        'kecamatan' => '',
        'kabupaten' => '',
        'total_dpt' => 0,
    ],
    'tps' => [],
];
?>
<aside class="sidebar">
    <div class="brand">
        <div class="brand-mark">QC</div>
        <div>
            <div class="brand-name">Quick Count<br>Desa <?= htmlspecialchars($cfg['app']['desa']) ?></div>
            <div class="brand-sub"><?= htmlspecialchars($cfg['app']['kecamatan']) ?>, <?= htmlspecialchars($cfg['app']['kabupaten']) ?></div>
        </div>
    </div>
    <nav>
        <a href="index.php" class="nav-link <?= $activePage === 'dashboard' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="8" height="10" rx="1.5"/><rect x="13" y="3" width="8" height="6" rx="1.5"/><rect x="13" y="11" width="8" height="10" rx="1.5"/><rect x="3" y="15" width="8" height="6" rx="1.5"/></svg>
            Dashboard
        </a>
        <a href="input.php" class="nav-link <?= $activePage === 'input' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v18M5 8l7-5 7 5M4 13h16v7a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1z"/></svg>
            Input Transaksi TPS
        </a>
        <a href="settings.php" class="nav-link <?= $activePage === 'settings' ? 'active' : '' ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.32 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
            Pengaturan
        </a>
        <button type="button" id="btn-open-reset" class="nav-link nav-link--danger">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/><path d="M10 11v6M14 11v6"/></svg>
            Reset Data
        </button>
    </nav>
    <div class="sidebar-foot">
        Total DPT terdaftar<br>
        <b style="color:var(--ink);font-family:var(--font-display);font-size:14px;"><?= number_format($cfg['app']['total_dpt'], 0, ',', '.') ?></b> pemilih &middot; <?= count($cfg['tps']) ?> TPS
    </div>
</aside>

<!-- ===== Modal: Reset Data ===== -->
<div class="modal-overlay" id="reset-overlay">
    <div class="modal-card">
        <div class="modal-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/><path d="M10 11v6M14 11v6"/></svg>
        </div>
        <h3>Reset Seluruh Data Quick Count?</h3>
        <p class="modal-desc">
            Tindakan ini akan menghapus <b>semua suara masuk</b> dari 11 TPS beserta
            riwayat transaksinya secara permanen dan tidak dapat dibatalkan.
            Masukkan password untuk melanjutkan.
        </p>
        <div class="field" style="text-align:left;">
            <label for="reset-password">Password reset</label>
            <input type="password" id="reset-password" placeholder="Masukkan password" autocomplete="off">
        </div>
        <p class="modal-error" id="reset-error"></p>
        <div class="modal-actions">
            <button type="button" class="btn-ghost" id="btn-cancel-reset">Batal</button>
            <button type="button" class="btn-danger" id="btn-confirm-reset">Ya, Reset Data</button>
        </div>
    </div>
</div>
