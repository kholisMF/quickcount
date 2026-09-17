<?php
function asset_url($relativePath)
{
    $fullPath = __DIR__ . '/../' . $relativePath;
    $version  = is_file($fullPath) ? filemtime($fullPath) : time();
    return $relativePath . '?v=' . $version;
}

require_once __DIR__ . '/FirebaseClient.php';

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

        $settings['candidates'] = array_values($settings['candidates'] ?? $defaults['candidates']);
        $settings['tps']        = array_values($settings['tps'] ?? $defaults['tps']);
        $settings['tidak_sah']  = $settings['tidak_sah'] ?? $defaults['tidak_sah'];
        $settings['total_dpt']  = (int) ($settings['total_dpt'] ?? $defaults['total_dpt']);

        // Pastikan tiap TPS punya id, username, password
        foreach ($settings['tps'] as $i => &$t) {
            $t['id']       = $t['id']       ?? ('tps' . ($i + 1));
            $t['nama']     = $t['nama']     ?? ('TPS ' . ($i + 1));
            $t['dpt']      = (int)($t['dpt'] ?? 0);
            $t['saksi']    = $t['saksi']    ?? '';  
            $t['username'] = $t['username'] ?? ('tps' . ($i + 1));
            $t['password'] = $t['password'] ?? ('tps' . ($i + 1));
        }
        unset($t);

        return $settings;
    } catch (Throwable $e) {
        return $defaults;
    }
}

function apply_settings(array $cfg, array $settings)
{
    $cfg['candidates']       = $settings['candidates'];
    $cfg['tidak_sah']        = $settings['tidak_sah'];
    $cfg['tps']              = $settings['tps'];
    $cfg['app']['total_dpt'] = $settings['total_dpt'];
    return $cfg;
}