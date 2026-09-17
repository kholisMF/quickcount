<?php
/**
 * Menghasilkan URL aset dengan query ?v=<waktu file terakhir diubah>,
 * supaya browser otomatis mengambil versi terbaru setiap kali file
 * CSS/JS diedit, tanpa perlu hard refresh manual.
 */
function asset_url($relativePath)
{
    $fullPath = __DIR__ . '/../' . $relativePath;
    $version  = is_file($fullPath) ? filemtime($fullPath) : time();
    return $relativePath . '?v=' . $version;
}

require_once __DIR__ . '/FirebaseClient.php';

/**
 * Mengambil pengaturan (daftar calon, TPS, total DPT) dari Firebase
 * node 'quickcount/settings'. Kalau belum pernah diisi (project baru),
 * node ini otomatis diisi dari nilai default di config.php sekali saja.
 *
 * Catatan: node ini TERPISAH dari 'quickcount/suara' & 'quickcount/log',
 * jadi tombol "Reset Data" tidak pernah menghapus pengaturan ini.
 */
function load_settings(array $cfg)
{
    $defaults = [
        'candidates' => $cfg['candidates'],
        'tidak_sah'  => $cfg['tidak_sah'],
        'tps'        => $cfg['tps'],
        'total_dpt'  => $cfg['app']['total_dpt'],
    ];

    try {
        $client = new FirebaseClient($cfg['firebase']['database_url'], $cfg['firebase']['database_secret']);
        $settings = $client->get($cfg['db_path'] . '/settings');

        if (empty($settings)) {
            $client->put($cfg['db_path'] . '/settings', $defaults);
            return $defaults;
        }

        // Firebase REST bisa mengembalikan array sebagai object jika index tidak berurutan; normalisasi.
        $settings['candidates'] = array_values($settings['candidates'] ?? $defaults['candidates']);
        $settings['tps']        = array_values($settings['tps'] ?? $defaults['tps']);
        $settings['tidak_sah']  = $settings['tidak_sah'] ?? $defaults['tidak_sah'];
        $settings['total_dpt']  = (int) ($settings['total_dpt'] ?? $defaults['total_dpt']);

        return $settings;
    } catch (Throwable $e) {
        // Firebase tidak terjangkau — tetap render halaman memakai nilai default
        // dari config.php supaya situs tidak mati total.
        return $defaults;
    }
}

/** Menggabungkan hasil load_settings() ke dalam struktur $cfg supaya seluruh halaman lain tidak perlu diubah. */
function apply_settings(array $cfg, array $settings)
{
    $cfg['candidates']       = $settings['candidates'];
    $cfg['tidak_sah']        = $settings['tidak_sah'];
    $cfg['tps']              = $settings['tps'];
    $cfg['app']['total_dpt'] = $settings['total_dpt'];
    return $cfg;
}
