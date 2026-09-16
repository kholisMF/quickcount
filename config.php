<?php
/**
 * Konfigurasi utama Quick Count
 * Desa Sukadaya - Kecamatan Sukawangi - Kabupaten Bekasi
 *
 * UBAH bagian 'firebase' di bawah ini dengan data project Firebase Anda.
 * - database_url   : URL Realtime Database (Build > Realtime Database di Firebase Console)
 * - web_api_key    : Web API Key project (Project Settings > General) - dipakai client (aman untuk read-only)
 * - database_secret: Database Secret (Project Settings > Service Accounts > Database secrets)
 *                     HANYA dipakai di server (save.php) via cURL, JANGAN pernah dikirim ke browser.
 */

return [
    'app' => [
        'nama_pemilihan' => 'Pemilihan Kepala Desa',
        'desa'           => 'Sukadaya',
        'kecamatan'      => 'Sukawangi',
        'kabupaten'      => 'Bekasi',
        'total_dpt'      => 5600,
    ],

    'firebase' => [
        'database_url'    => 'https://quickcount-sukadaya-default-rtdb.asia-southeast1.firebasedatabase.app',
        'web_api_key'     => 'AIzaSyDNqbDf63zY_yFSDQ3ECeMPOwecki1fulc',
        'auth_domain'     => 'quickcount-sukadaya.firebaseapp.com',
        'project_id'      => 'quickcount-sukadaya',
        'database_secret' => 'c1iDb1XlJNT86CmFY5pqwQpNbHgRP52BJ9i32ZJr',
    ],

    // Node root di Realtime Database
    'db_path' => 'quickcount',

    'candidates' => [
        ['id' => 'yarpan',  'no' => 1, 'nama' => 'Yarpan Suharno',   'warna' => '#FFFFFF'],
        ['id' => 'marta',   'no' => 2, 'nama' => 'H. Marta Jaya',    'warna' => '#1F3A8F'],
        ['id' => 'imam',    'no' => 3, 'nama' => 'Imam Tantowi',     'warna' => '#22C55E'],
        ['id' => 'sartija', 'no' => 4, 'nama' => 'Sartija Arizona',  'warna' => '#FFD400'],
    ],

    'tidak_sah' => [
        'id' => 'tidak_sah', 'nama' => 'Tidak Sah / Rusak', 'warna' => '#5B6472',
    ],

    // 11 TPS, hanya diberi nomor urut (tanpa nama lokasi)
    'tps' => [
        ['id' => 'tps1',  'nama' => 'TPS 1',  'dpt' => 510],
        ['id' => 'tps2',  'nama' => 'TPS 2',  'dpt' => 505],
        ['id' => 'tps3',  'nama' => 'TPS 3',  'dpt' => 515],
        ['id' => 'tps4',  'nama' => 'TPS 4',  'dpt' => 500],
        ['id' => 'tps5',  'nama' => 'TPS 5',  'dpt' => 520],
        ['id' => 'tps6',  'nama' => 'TPS 6',  'dpt' => 495],
        ['id' => 'tps7',  'nama' => 'TPS 7',  'dpt' => 510],
        ['id' => 'tps8',  'nama' => 'TPS 8',  'dpt' => 505],
        ['id' => 'tps9',  'nama' => 'TPS 9',  'dpt' => 515],
        ['id' => 'tps10', 'nama' => 'TPS 10', 'dpt' => 500],
        ['id' => 'tps11', 'nama' => 'TPS 11', 'dpt' => 525],
    ],
];
