# Quick Count — Pilkades Desa Sukadaya

Aplikasi web Quick Count untuk Pemilihan Kepala Desa Sukadaya, Kecamatan
Sukawangi, Kabupaten Bekasi. Dibangun dengan **PHP native** (tanpa framework)
dan **Firebase Realtime Database** sebagai database, dengan dashboard yang
**auto-update secara realtime** setiap ada data baru masuk (tanpa reload
halaman).

## Arsitektur singkat

Agar aman sekaligus tetap "PHP native + Firebase", pembagian tugasnya:

- **Tulis data (input transaksi TPS)** → dilakukan oleh `save.php` (PHP murni)
  memakai `includes/FirebaseClient.php`, sebuah client REST kecil berbasis
  cURL yang otentikasi ke Firebase pakai **Database Secret**. Semua validasi
  (TPS valid, angka tidak negatif, total tidak melebihi DPT) terjadi di PHP,
  di server, bukan hanya di browser.
- **Baca / dengar perubahan data realtime** → dashboard (`index.php`) dan
  halaman input (`input.php`) memakai **Firebase JS SDK** langsung dari
  browser dengan `onValue`/`.on('value', ...)`. Inilah "event handler" yang
  membuat pie chart, tabel, dan status TPS ter-update otomatis begitu ada
  transaksi baru tersimpan — tanpa perlu refresh manual.

Web API Key yang dipakai di sisi client hanya untuk **membaca**, dan aman
untuk diekspos ke browser selama aturan keamanan Firebase (lihat di bawah)
dikonfigurasi dengan benar. Database Secret hanya pernah dipakai di
`save.php`, tidak pernah dikirim ke browser.

## Struktur folder

```
quickcount-sukadaya/
├── config.php                     # data calon, TPS, DPT, kredensial Firebase
├── save.php                       # endpoint POST untuk menyimpan hasil TPS
├── index.php                      # Dashboard (pie chart + detail per TPS)
├── input.php                      # Form input transaksi suara per TPS
├── includes/
│   ├── FirebaseClient.php         # client REST Firebase (server-side, cURL)
│   ├── navbar.php                 # sidebar navigasi
│   └── firebase-config-inline.php # inject config Firebase ke JS (read-only)
└── assets/
    ├── css/style.css
    └── js/
        ├── dashboard.js           # listener realtime + Chart.js + tabel
        └── input.js               # listener status TPS + submit ke save.php
```

## 1. Siapkan project Firebase

1. Buka https://console.firebase.google.com → **Add project** → beri nama
   bebas, misalnya `quickcount-sukadaya`.
2. Di sidebar, buka **Build → Realtime Database → Create Database**. Pilih
   lokasi server terdekat (mis. Singapore / asia-southeast1), mulai dalam
   **locked mode**.
3. Atur **Rules** Realtime Database menjadi seperti ini (baca publik untuk
   dashboard, tulis hanya lewat Database Secret / admin — request dari PHP
   dengan `?auth=<secret>` otomatis melewati rules ini):

   ```json
   {
     "rules": {
       "quickcount": {
         ".read": true,
         ".write": false
       }
     }
   }
   ```

4. Ambil kredensial:
   - **Web API Key** & **Database URL**: Project Settings ⚙ → General → di
     bagian "Your apps", buat Web App baru (ikon `</>`) bila belum ada, lalu
     salin `apiKey`, `authDomain`, `projectId`. Database URL bisa dilihat di
     halaman Realtime Database (contoh:
     `https://quickcount-sukadaya-default-rtdb.asia-southeast1.firebasedatabase.app`).
   - **Database Secret**: Project Settings ⚙ → Service accounts → tab
     **Database secrets** → klik **Show** untuk secret legacy, atau generate
     baru.

5. Isi semua nilai tersebut ke `config.php` pada bagian `'firebase' => [...]`.

## 2. Jalankan secara lokal

Butuh PHP 8+ dengan ekstensi **cURL** aktif (default sudah aktif di hampir
semua instalasi PHP / XAMPP / Laragon).

```bash
cd quickcount-sukadaya
php -S localhost:8000
```

Buka:
- `http://localhost:8000/index.php` — Dashboard
- `http://localhost:8000/input.php` — Input transaksi TPS

## 3. Deploy ke hosting

Upload seluruh folder ke hosting PHP (shared hosting, VPS, dsb). Pastikan:
- Ekstensi `curl` PHP aktif.
- `config.php` tidak bisa diakses publik dengan menampilkan isi mentahnya
  (PHP akan otomatis mengeksekusinya, bukan menampilkan teks, jadi ini aman
  selama file diproses oleh PHP — hindari menaruhnya di server non-PHP).
- Gunakan HTTPS agar data terenkripsi saat pengiriman.

## 4. Cara pakai

- **Input Transaksi TPS**: pilih TPS, isi jumlah suara sah tiap calon +
  suara tidak sah/rusak, sistem otomatis menghitung total dan memberi
  peringatan bila total melebihi DPT TPS tersebut. Klik **Simpan hasil TPS**.
  Data yang sama bisa dibuka & dikoreksi ulang kapan saja (form otomatis
  terisi dengan angka terakhir yang tersimpan).
- **Dashboard**: begitu `save.php` berhasil menulis ke Firebase, semua
  browser yang sedang membuka dashboard akan menerima event `value` dari
  Firebase dan otomatis menghitung ulang persentase, memperbarui pie chart,
  papan peringkat, dan tabel detail per TPS — **tanpa reload halaman**.

## Catatan data

- Total DPT: **5.600 pemilih** tersebar di **11 TPS** (TPS 1 s.d. TPS 11).
- 4 pasangan calon + 1 kategori suara tidak sah/rusak.
- TPS hanya diberi nomor urut (tanpa nama lokasi). Angka DPT per TPS di
  `config.php` bersifat contoh — silakan sesuaikan dengan data riil dari
  PPS/Panitia Pilkades Desa Sukadaya (total harus tetap 5.600).
