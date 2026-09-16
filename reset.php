<?php
/**
 * Endpoint reset data Quick Count.
 * Menghapus seluruh node 'suara' (dan 'log') di Firebase setelah password
 * dicocokkan. Password sengaja hardcode sesuai permintaan — untuk
 * penggunaan produksi sebaiknya dipindah ke tempat yang lebih aman
 * (mis. environment variable atau hash + config terpisah).
 */

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/includes/FirebaseClient.php';
$cfg = require __DIR__ . '/config.php';

const RESET_PASSWORD = 'resetdata';

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

$password = (string) ($input['password'] ?? '');

if (!hash_equals(RESET_PASSWORD, $password)) {
    respond(401, ['ok' => false, 'message' => 'Password salah.']);
}

try {
    $client = new FirebaseClient($cfg['firebase']['database_url'], $cfg['firebase']['database_secret']);
    $client->delete($cfg['db_path'] . '/suara');
    $client->delete($cfg['db_path'] . '/log');

    respond(200, ['ok' => true, 'message' => 'Seluruh data quick count berhasil direset.']);
} catch (Throwable $e) {
    respond(502, ['ok' => false, 'message' => 'Gagal menghubungi Firebase: ' . $e->getMessage()]);
}
