<?php

return [
    'app' => [
        'nama_pemilihan' => 'Pemilihan Kepala Desa',
        'desa'           => 'Sukadaya',
        'kecamatan'      => 'Sukawangi',
        'kabupaten'      => 'Bekasi',
        'total_dpt'      => 5672,
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
        'id' => 'tidak_sah', 'nama' => 'Suara Tidak Sah / Rusak', 'warna' => '#5B6472',
    ],
    'tps' => [
        ['id' => 'tps1',  'nama' => 'TPS 1',  'dpt' => 525, 'username' => 'tps1', 'password' => 'tps1'],
        ['id' => 'tps2',  'nama' => 'TPS 2',  'dpt' => 522, 'username' => 'tps2', 'password' => 'tps2'],
        ['id' => 'tps3',  'nama' => 'TPS 3',  'dpt' => 528, 'username' => 'tps3', 'password' => 'tps3'],
        ['id' => 'tps4',  'nama' => 'TPS 4',  'dpt' => 528, 'username' => 'tps4', 'password' => 'tps4'],
        ['id' => 'tps5',  'nama' => 'TPS 5',  'dpt' => 537, 'username' => 'tps5', 'password' => 'tps5'],
        ['id' => 'tps6',  'nama' => 'TPS 6',  'dpt' => 500, 'username' => 'tps6', 'password' => 'tps6'],
        ['id' => 'tps7',  'nama' => 'TPS 7',  'dpt' => 543, 'username' => 'tps7', 'password' => 'tps7'],
        ['id' => 'tps8',  'nama' => 'TPS 8',  'dpt' => 531, 'username' => 'tps8', 'password' => 'tps8'],
        ['id' => 'tps9',  'nama' => 'TPS 9',  'dpt' => 492, 'username' => 'tps9', 'password' => 'tps9'],
        ['id' => 'tps10', 'nama' => 'TPS 10', 'dpt' => 487, 'username' => 'tps10', 'password' => 'tps10'],
        ['id' => 'tps11', 'nama' => 'TPS 11', 'dpt' => 479, 'username' => 'tps11', 'password' => 'tps11'],
    ],
];
