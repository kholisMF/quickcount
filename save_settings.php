<?php
$cfg = require __DIR__ . '/config.php';
require __DIR__ . '/includes/helpers.php';

header('Content-Type: application/json');

const SETTINGS_PASSWORD = 'settingdata';

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    echo json_encode(['ok' => false, 'message' => 'Payload tidak valid.']);
    exit;
}

if (($body['password'] ?? '') !== SETTINGS_PASSWORD) {
    echo json_encode(['ok' => false, 'message' => 'Sesi pengaturan tidak valid. Silakan buka ulang halaman.']);
    exit;
}

$candidates = $body['candidates'] ?? [];
$tps        = $body['tps'] ?? [];
$tidakSah   = $body['tidak_sah'] ?? [];
$totalDpt   = (int)($body['total_dpt'] ?? 0);

if (count($candidates) < 1) {
    echo json_encode(['ok' => false, 'message' => 'Minimal 1 calon.']);
    exit;
}
foreach ($candidates as $c) {
    if (empty($c['nama']) || empty($c['id'])) {
        echo json_encode(['ok' => false, 'message' => 'Data calon tidak lengkap.']);
        exit;
    }
}

if (count($tps) < 1) {
    echo json_encode(['ok' => false, 'message' => 'Minimal 1 TPS.']);
    exit;
}

$usernames = [];
foreach ($tps as $i => $t) {
    if (empty($t['username']) || empty($t['password'])) {
        echo json_encode(['ok' => false, 'message' => 'Username & password TPS ' . ($i+1) . ' wajib diisi.']);
        exit;
    }
    $u = strtolower($t['username']);
    if (isset($usernames[$u])) {
        echo json_encode(['ok' => false, 'message' => 'Username "' . $t['username'] . '" dipakai lebih dari satu TPS.']);
        exit;
    }
    $usernames[$u] = true;
}

$settings = [
    'candidates' => array_values(array_map(function ($c) {
        return [
            'id'    => (string)$c['id'],
            'no'    => (int)($c['no'] ?? 0),
            'nama'  => trim($c['nama']),
            'warna' => $c['warna'] ?? '#2563EB',
        ];
    }, $candidates)),
    'tidak_sah' => [
        'id'    => $tidakSah['id'] ?? 'tidaksah',
        'nama'  => trim($tidakSah['nama'] ?? 'Tidak Sah'),
        'warna' => $tidakSah['warna'] ?? '#94A3B8',
    ],
    'tps' => array_values(array_map(function ($t, $i) {
        return [
            'id'       => (string)($t['id'] ?? uniqid('tps')),
            'nama'     => trim($t['nama'] ?? ('TPS ' . ($i + 1))),
            'dpt'      => (int)($t['dpt'] ?? 0),
            'saksi'    => trim($t['saksi'] ?? ''),
            'username' => trim($t['username']),
            'password' => trim($t['password']),
        ];
    }, $tps, array_keys($tps))),
    'total_dpt' => $totalDpt,
];

try {
    $client = new FirebaseClient($cfg['firebase']['database_url'], $cfg['firebase']['database_secret']);
    $client->put($cfg['db_path'] . '/settings', $settings);
    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'message' => 'Gagal menyimpan ke server: ' . $e->getMessage()]);
}