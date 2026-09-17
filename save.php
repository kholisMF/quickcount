<?php
session_start();

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
    respond(400, ['ok' => false, 'message' => 'Payload tidak valid.']);
}

// --- Ambil & validasi TPS ---
$tpsId = (string)($input['tps_id'] ?? '');
if ($tpsId === '') {
    respond(422, ['ok' => false, 'message' => 'TPS tidak dikenali.']);
}

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

if (empty($_SESSION['tps_id']) || $_SESSION['tps_id'] !== $tpsId) {
    respond(403, ['ok' => false, 'message' => 'Anda tidak berwenang menyimpan data TPS ini. Silakan login ulang.']);
}

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

if ($validTps['dpt'] > 0 && $total > $validTps['dpt']) {
    respond(422, ['ok' => false, 'message' => 'Total suara (' . $total . ') melebihi DPT TPS ini (' . $validTps['dpt'] . ').']);
}

try {
    $client = new FirebaseClient($cfg['firebase']['database_url'], $cfg['firebase']['database_secret']);

    $client->put($cfg['db_path'] . '/suara/' . $tpsId, $suaraData);

    // Audit trail
    $client->push($cfg['db_path'] . '/log', [
        'tps_id'    => $tpsId,
        'tps_nama'  => $validTps['nama'],
        'petugas'   => $_SESSION['tps_nama'] ?? ($validTps['nama'] ?? 'Tidak diisi'),
        'data'      => $suaraData,
        'total'     => $total,
        'timestamp' => date('c'),
    ]);

    respond(200, ['ok' => true, 'message' => 'Data tersimpan.', 'total' => $total]);
} catch (Throwable $e) {
    respond(502, ['ok' => false, 'message' => 'Gagal menghubungi Firebase: ' . $e->getMessage()]);
}