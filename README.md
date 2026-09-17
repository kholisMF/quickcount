# Quick Count — Pilkades Desa Sukadaya

Aplikasi web Quick Count untuk Pemilihan Kepala Desa Sukadaya, Kecamatan
Sukawangi, Kabupaten Bekasi. Dibangun dengan **PHP native** (tanpa framework)
dan **Firebase Realtime Database** sebagai database, dengan dashboard yang
**auto-update secara realtime** setiap ada data baru masuk (tanpa reload
halaman).

## Arsitektur singkat

Agar aman sekaligus tetap "PHP native + Firebase", pembagian tugasnya:

- **Tulis data (input transaksi TPS & pengaturan)** → dilakukan oleh
  `save.php` / `save_settings.php` / `reset.php` (PHP murni) memakai
  `includes/FirebaseClient.php`, sebuah client REST kecil berbasis cURL
  yang otentikasi ke Firebase pakai **Database Secret**. Semua validasi
  terjadi di PHP, di server, bukan hanya di browser.
- **Baca pengaturan saat render halaman** → `index.php`, `input.php`, dan
  `settings.php` memanggil `load_settings()` (di `includes/helpers.php`)
  yang mengambil node `quickcount/settings` lewat REST, lalu digabung ke
  `$cfg`. Kalau Firebase tidak terjangkau, otomatis jatuh ke nilai default
  di `config.php` supaya halaman tetap tampil (tidak fatal error).
- **Baca / dengar perubahan data realtime** → dashboard (`index.php`) dan
  halaman input (`input.php`) memakai **Firebase JS SDK** langsung dari
  browser dengan `onValue`/`.on('value', ...)`. Inilah "event handler" yang
  membuat pie chart, tabel, dan status TPS ter-update otomatis begitu ada
  transaksi baru tersimpan — tanpa perlu refresh manual. Kedua halaman ini
  juga mendengarkan node `quickcount/settings`; kalau berubah (mis. dari
  halaman Pengaturan), halaman otomatis `location.reload()` supaya selalu
  sinkron dengan daftar calon/TPS terbaru.

Web API Key yang dipakai di sisi client hanya untuk **membaca**, dan aman
untuk diekspos ke browser selama aturan keamanan Firebase (lihat di bawah)
dikonfigurasi dengan benar. Database Secret hanya pernah dipakai di sisi
server (`save.php`, `save_settings.php`, `reset.php`, `helpers.php`), tidak
pernah dikirim ke browser.

## Struktur folder

```
quickcount-sukadaya/
├── config.php                     # kredensial Firebase + nilai contoh awal (seed)
├── save.php                       # endpoint POST untuk menyimpan hasil TPS
├── save_settings.php              # endpoint POST untuk menyimpan pengaturan
├── reset.php                      # endpoint reset data (password: resetdata)
├── index.php                      # Dashboard (pie chart + detail per TPS)
├── input.php                      # Form input transaksi suara per TPS (stepper +/-)
├── settings.php                   # Halaman pengaturan calon/TPS/DPT
├── includes/
│   ├── FirebaseClient.php         # client REST Firebase (server-side, cURL)
│   ├── helpers.php                # asset_url() + load_settings()/apply_settings()
│   ├── navbar.php                 # sidebar navigasi + modal Reset Data
│   └── firebase-config-inline.php # inject config Firebase ke JS (read-only)
└── assets/
    ├── css/style.css
    └── js/
        ├── dashboard.js           # listener realtime + Chart.js + tabel
        ├── input.js               # stepper +/- dengan auto-save ke save.php
        ├── settings.js            # form dinamis pengaturan + submit ke save_settings.php
        ├── settings-watch.js      # auto-reload index/input saat pengaturan berubah
        └── reset.js               # modal password + submit ke reset.php
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

- **Pengaturan** (`settings.php`): atur di sini dulu sebelum hari-H —
  nama & warna tiap calon (bisa tambah/hapus calon), label & warna suara
  tidak sah, jumlah TPS (tombol +/-), DPT tiap TPS, dan total hak suara
  keseluruhan. Data ini disimpan di node **`quickcount/settings`** di
  Firebase — terpisah total dari data suara, jadi **tidak pernah ikut
  terhapus oleh tombol Reset Data**. Kalau node ini masih kosong (project
  Firebase baru), sistem otomatis mengisinya dari nilai contoh di
  `config.php` saat pertama kali dibuka.
- **Input Transaksi TPS**: pilih TPS, lalu ketuk tombol **+ / −** pada tiap
  calon untuk menghitung suara. Setiap ketukan langsung tersimpan otomatis
  ke Firebase (tidak ada tombol submit terpisah) — status "Menyimpan…" /
  "Tersimpan" muncul di bawah form. Sistem menolak penambahan suara bila
  totalnya akan melebihi DPT TPS tersebut.
- **Dashboard**: begitu ada suara baru tersimpan, semua browser yang sedang
  membuka dashboard otomatis menerima event `value` dari Firebase dan
  langsung menghitung ulang persentase, pie chart, papan peringkat, dan
  tabel detail per TPS — **tanpa reload halaman**. Dashboard & halaman
  input juga otomatis **reload sendiri** kalau ada perubahan di halaman
  Pengaturan (mis. calon atau jumlah TPS diubah), supaya datanya selalu
  sinkron.
- **Reset Data**: tombol di navbar (butuh password `resetdata`, bisa diubah
  di `reset.php`) hanya menghapus `quickcount/suara` & `quickcount/log`.
  Pengaturan calon/TPS/DPT di atas **tidak ikut ter-reset**.

## Catatan data

- Nilai di `config.php` (4 calon, 11 TPS, total DPT 5.600) sekarang hanya
  dipakai sebagai **nilai contoh awal** saat Firebase belum pernah diisi.
  Setelah itu, sumber data yang sesungguhnya ada di halaman **Pengaturan**
  dan node `quickcount/settings` di Firebase.
- TPS hanya diberi nomor urut otomatis (TPS 1, TPS 2, dst — mengikuti
  urutan/posisi, bukan nama lokasi).
  PPS/Panitia Pilkades Desa Sukadaya (total harus tetap 5.600).
