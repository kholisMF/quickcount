<?php
/**
 * Endpoint penyimpanan transaksi quick count.
 * Menerima JSON dari input.php, memvalidasi, lalu menulis ke Firebase
 * Realtime Database lewat REST API (memakai Database Secret di server).
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/includes/helpers.php';
$cfg = require __DIR__ . '/config.php';
$settings = load_settings($cfg);
$cfg = apply_settings($cfg, $settings);

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

// --- Validasi TPS ---
$tpsId = $input['tps_id'] ?? '';
$validTps = null;
foreach ($cfg['tps'] as $tps) {
    if ($tps['id'] === $tpsId) {
        $validTps = $tps;
        break;
    }
}
if (!$validTps) {
    respond(422, ['ok' => false, 'message' => 'TPS tidak dikenali.']);
}

// --- Validasi & susun data suara ---
$allowedIds = array_column($cfg['candidates'], 'id');
$allowedIds[] = $cfg['tidak_sah']['id'];

$suaraData = [];
$total = 0;
foreach ($allowedIds as $id) {
    $val = $input[$id] ?? 0;
    if (!is_numeric($val) || $val < 0) {
        respond(422, ['ok' => false, 'message' => 'Jumlah suara harus berupa angka positif.']);
    }
    $val = (int) $val;
    $suaraData[$id] = $val;
    $total += $val;
}

if ($total > $validTps['dpt']) {
    respond(422, ['ok' => false, 'message' => 'Total suara (' . $total . ') melebihi DPT TPS ini (' . $validTps['dpt'] . ').']);
}

$petugas = trim((string) ($input['petugas'] ?? ''));
$timestamp = date('c');

try {
    $client = new FirebaseClient($cfg['firebase']['database_url'], $cfg['firebase']['database_secret']);

    // Simpan / timpa hasil TPS (data resmi per TPS bisa dikoreksi ulang)
    $client->put($cfg['db_path'] . '/suara/' . $tpsId, $suaraData);

    // Catat jejak transaksi untuk audit trail
    $client->push($cfg['db_path'] . '/log', [
        'tps_id'    => $tpsId,
        'tps_nama'  => $validTps['nama'],
        'data'      => $suaraData,
        'total'     => $total,
        'petugas'   => $petugas !== '' ? $petugas : 'Tidak diisi',
        'timestamp' => $timestamp,
    ]);

    respond(200, ['ok' => true, 'message' => 'Data tersimpan.', 'total' => $total]);
} catch (Throwable $e) {
    respond(502, ['ok' => false, 'message' => 'Gagal menghubungi Firebase: ' . $e->getMessage()]);
}
