<?php
/**
 * Endpoint penyimpanan pengaturan (calon, warna, jumlah TPS, DPT).
 * Menulis ke node 'quickcount/settings' — terpisah total dari
 * 'quickcount/suara' & 'quickcount/log', sehingga tombol Reset Data
 * tidak pernah menyentuh pengaturan ini.
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/includes/helpers.php';
$cfg = require __DIR__ . '/config.php';

const SETTINGS_PASSWORD = 'settingdata';

function respond($httpCode, array $body)
{
    http_response_code($httpCode);
    echo json_encode($body);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(405, ['ok' => false, 'message' => 'Metode tidak diizinkan.']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    respond(400, ['ok' => false, 'message' => 'Data tidak valid.']);
}

if (!hash_equals(SETTINGS_PASSWORD, (string) ($input['password'] ?? ''))) {
    respond(401, ['ok' => false, 'message' => 'Password tidak valid. Buka ulang halaman Pengaturan.']);
}

$hexPattern = '/^#[0-9A-Fa-f]{6}$/';
$idPattern  = '/^[A-Za-z0-9_-]{1,40}$/';

// --- Validasi calon ---
$rawCandidates = $input['candidates'] ?? [];
if (!is_array($rawCandidates) || count($rawCandidates) < 2 || count($rawCandidates) > 10) {
    respond(422, ['ok' => false, 'message' => 'Jumlah calon harus antara 2 sampai 10.']);
}

$candidates = [];
$seenIds = [];
foreach (array_values($rawCandidates) as $i => $c) {
    $id    = (string) ($c['id'] ?? '');
    $nama  = trim((string) ($c['nama'] ?? ''));
    $warna = (string) ($c['warna'] ?? '');

    if (!preg_match($idPattern, $id) || $id === 'tidak_sah') {
        respond(422, ['ok' => false, 'message' => 'ID calon tidak valid.']);
    }
    if (isset($seenIds[$id])) {
        respond(422, ['ok' => false, 'message' => 'ID calon duplikat terdeteksi.']);
    }
    $seenIds[$id] = true;

    if ($nama === '' || mb_strlen($nama) > 60) {
        respond(422, ['ok' => false, 'message' => 'Nama calon tidak boleh kosong (maks 60 karakter).']);
    }
    if (!preg_match($hexPattern, $warna)) {
        respond(422, ['ok' => false, 'message' => 'Format warna calon "' . $nama . '" tidak valid.']);
    }

    $candidates[] = ['id' => $id, 'no' => $i + 1, 'nama' => $nama, 'warna' => strtoupper($warna)];
}

// --- Validasi suara tidak sah ---
$tsInput = $input['tidak_sah'] ?? [];
$tsNama  = trim((string) ($tsInput['nama'] ?? ''));
$tsWarna = (string) ($tsInput['warna'] ?? '');

if ($tsNama === '' || mb_strlen($tsNama) > 60) {
    respond(422, ['ok' => false, 'message' => 'Label suara tidak sah tidak boleh kosong.']);
}
if (!preg_match($hexPattern, $tsWarna)) {
    respond(422, ['ok' => false, 'message' => 'Format warna suara tidak sah tidak valid.']);
}
$tidakSah = ['id' => 'tidak_sah', 'nama' => $tsNama, 'warna' => strtoupper($tsWarna)];

// --- Validasi TPS (id & nama diregenerasi otomatis berdasar urutan) ---
$rawTps = $input['tps'] ?? [];
if (!is_array($rawTps) || count($rawTps) < 1 || count($rawTps) > 50) {
    respond(422, ['ok' => false, 'message' => 'Jumlah TPS harus antara 1 sampai 50.']);
}

$tps = [];
foreach (array_values($rawTps) as $i => $t) {
    $dpt = $t['dpt'] ?? 0;
    if (!is_numeric($dpt) || $dpt < 0) {
        respond(422, ['ok' => false, 'message' => 'DPT TPS ' . ($i + 1) . ' harus berupa angka positif.']);
    }
    $tps[] = ['id' => 'tps' . ($i + 1), 'nama' => 'TPS ' . ($i + 1), 'dpt' => (int) $dpt];
}

// --- Validasi total DPT ---
$totalDpt = $input['total_dpt'] ?? 0;
if (!is_numeric($totalDpt) || $totalDpt < 0) {
    respond(422, ['ok' => false, 'message' => 'Total hak suara harus berupa angka positif.']);
}

$settings = [
    'candidates' => $candidates,
    'tidak_sah'  => $tidakSah,
    'tps'        => $tps,
    'total_dpt'  => (int) $totalDpt,
];

try {
    $client = new FirebaseClient($cfg['firebase']['database_url'], $cfg['firebase']['database_secret']);
    $client->put($cfg['db_path'] . '/settings', $settings);

    respond(200, ['ok' => true, 'message' => 'Pengaturan berhasil disimpan.']);
} catch (Throwable $e) {
    respond(502, ['ok' => false, 'message' => 'Gagal menghubungi Firebase: ' . $e->getMessage()]);
}
