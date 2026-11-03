# Aplikasi Pengingat KGB (Kenaikan Gaji Berkala)

Aplikasi manajemen & pengingat KGB pegawai — Fase 1 dari 4.

## Status Fase

| Fase | Fitur | Status |
|------|-------|--------|
| 1 | Auth, Dashboard (kartu status, pencarian, notifikasi internal), Pengingat KGB, Detail & Form Pegawai | ✅ Selesai |
| 2 | Pengiriman email (PHPMailer + SMTP Gmail), Log Pengiriman Email, Kirim ulang email, Notifikasi otomatis via cron | ✅ Selesai |
| 3 | Kalender KGB (FullCalendar) | ✅ Selesai |
| 4 | Laporan (cetak/ekspor PDF & Excel), Backup/Restore database | ✅ Selesai |

Seluruh fitur di atas sudah diuji end-to-end (bukan hanya ditulis) memakai server PHP + MySQL/MariaDB sungguhan: login, dashboard, pengingat, kalender, log email, laporan, ekspor Excel, backup, dan **restore penuh** (data dihapus lalu dipulihkan dari file backup, berhasil).

## Kebutuhan Server

- PHP 8.1+ dengan ekstensi: `pdo_mysql`, `mbstring`, `curl`, `fileinfo`
- MySQL / MariaDB 10.x
- Apache dengan `mod_rewrite` (atau Nginx — lihat catatan di bawah)
- **Tidak perlu Composer** — seluruh framework ditulis native agar mudah di-deploy di hosting shared apa pun.

## Instalasi (Localhost)

1. **Import database**
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Ini membuat database `kgb_app`, seluruh tabel, data unit kerja contoh, dan 1 admin default:
   - Email: `admin@instansi.go.id`
   - Password: `admin123` (**segera ganti lewat menu Profil setelah login pertama**)

2. **Salin dan sesuaikan file environment**
   ```bash
   cp .env.example .env
   ```
   Buka `.env` dan sesuaikan:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` — sesuai kredensial MySQL Anda
   - `APP_URL` — sesuaikan dengan alamat aplikasi (mis. `http://localhost/kgb-app/public` atau domain hosting)
   - `MAIL_*` — SMTP Gmail (dipakai mulai Fase 2)

3. **Jalankan dengan PHP built-in server** (untuk pengujian cepat)
   ```bash
   php -S localhost:8080 -t public
   ```
   Buka `http://localhost:8080/login`

4. **Atau jalankan dengan Apache**
   - Arahkan document root (atau virtual host) ke folder `public/`
   - Pastikan `mod_rewrite` aktif — `.htaccess` sudah disiapkan di dalam `public/`

5. **Aktifkan pengiriman email otomatis (cron job)**
   Tambahkan baris berikut ke crontab server (`crontab -e`) agar sistem mengirim email
   pengingat KGB dan membuat notifikasi dashboard setiap hari secara otomatis:
   ```
   0 7 * * * /usr/bin/php /path/ke/proyek/cron/kirim_pengingat_otomatis.php >> /path/ke/proyek/storage/logs/cron.log 2>&1
   ```
   Di cPanel, fitur ini biasanya ada di menu **Cron Jobs**. Tombol **Kirim Ulang Email**
   di halaman Pengingat KGB memakai mesin pengiriman yang sama dan bisa dipakai kapan saja
   secara manual untuk pegawai yang emailnya gagal terkirim.

   PHPMailer sudah disertakan langsung di `vendor/phpmailer/` (tidak perlu Composer).

## Struktur Folder

```
config/       Konfigurasi aplikasi, database, bootstrap, loader .env
core/         Router, Controller dasar, Model dasar, koneksi Database (PDO)
helpers/      Fungsi bantu global (config(), env(), perhitungan KGB, format tanggal)
controllers/  Logika tiap halaman (Auth, Dashboard, Pengingat, Pegawai, Laporan, Backup, Kalender, EmailLog)
models/       Query database per entitas (Admin, Pegawai, EmailLog, Notifikasi)
services/     Logika bisnis lintas-controller (EmailService/PHPMailer, BackupService)
views/        Template halaman (layout, dashboard, pengingat, pegawai, auth, laporan, backup, kalender)
public/       Web root: index.php (front controller), assets/, uploads/
database/     schema.sql — skema + data awal
cron/         Script terjadwal: kirim_pengingat_otomatis.php
storage/      logs/ (log aplikasi) dan backups/ (file .sql hasil backup) — tidak diakses lewat web
vendor/       phpmailer/ — PHPMailer disertakan langsung (tanpa Composer)
```

## Keamanan

- Password admin di-hash dengan `password_hash()` (bcrypt) — **jangan pernah** menyimpan password polos.
- Sesi login memakai `session_regenerate_id()` untuk mencegah session fixation.
- Semua query database memakai prepared statement (PDO) — aman dari SQL Injection.
- Upload foto profil divalidasi tipe MIME & ukuran maksimum sebelum disimpan.
- **Wajib**: ganti password admin default segera setelah instalasi, dan jangan commit file `.env` ke repository publik (sudah masuk `.gitignore`).

## Menjalankan di Hosting Online (bukan localhost)

Aplikasi ini bisa dipindah ke hosting online kapan saja — tidak ada kode yang perlu diubah,
cukup ikuti langkah instalasi yang sama (import `database/schema.sql`, buat `.env`, sesuaikan
`APP_URL` dengan domain Anda).

### Kalau hosting Anda TIDAK menyediakan cron job (mis. hosting gratis)

Banyak hosting gratis (contoh: InfinityFree) tidak menyediakan cron job asli. Untuk kasus ini,
aplikasi menyediakan endpoint web `/cron/jalankan?token=...` yang bisa dipicu oleh layanan
penjadwal eksternal gratis seperti **cron-job.org**:

1. Buka `.env`, isi `CRON_TOKEN` dengan string acak yang panjang (mis. 40+ karakter, jangan tebak-tebakan).
2. Daftar gratis di https://cron-job.org, buat cronjob baru dengan URL:
   `https://domain-anda.com/cron/jalankan?token=ISI_TOKEN_ANDA`
3. Atur jadwal harian (mis. jam 07:00), simpan.
4. Buka Dashboard aplikasi setelah cronjob pertama jalan — banner status harus berubah hijau.

Kalau hosting Anda MENYEDIAKAN cron job asli (kebanyakan hosting berbayar/VPS), tetap pakai
cara `crontab`/Task Scheduler seperti dijelaskan di bagian instalasi di atas — pilih salah satu
cara saja, tidak perlu keduanya.

### Catatan soal hosting gratis untuk KGB (produksi sungguhan)

Hosting gratis cocok untuk **demo/uji coba**, tapi punya keterbatasan yang perlu dipertimbangkan
untuk pemakaian sungguhan oleh instansi: batas jumlah kunjungan harian, akun bisa disuspend
tanpa peringatan, tidak ada jaminan uptime, dan performa lebih lambat. Untuk sistem yang
benar-benar dipakai sehari-hari, hosting berbayar (mulai dari yang murah sekalipun) jauh lebih
andal.

## Menghitung Tanggal KGB

Tanggal KGB berikutnya dihitung otomatis: `tmt_kgb_terakhir + periode_kgb_tahun` (default 2 tahun). Status kartu (🟢🟡🟠🔴) ditentukan oleh selisih hari ke tanggal tersebut, dengan ambang batas yang bisa diatur lewat `.env` (`KGB_THRESHOLD_*`).

## Catatan Teknis

- **Ekspor PDF**: memakai tampilan cetak browser (klik "Cetak / PDF" → pilih "Save as PDF" di dialog cetak). Ini dipilih agar tidak perlu library PDF pihak ketiga yang berat/butuh Composer, dan hasilnya tetap rapi & konsisten. Ekspor **Excel** bersifat file `.xls` asli yang langsung terunduh.
- **Backup/Restore**: dibuat murni dengan PHP + PDO (tanpa `shell_exec`/`mysqldump`) agar tetap berfungsi di hosting shared yang menonaktifkan fungsi exec. File restore idealnya adalah file yang dihasilkan oleh menu Backup aplikasi ini sendiri — proses restore mem-parsing statement SQL berdasarkan pemisah `;` di akhir baris, jadi file dump kompleks dari sumber lain (mis. hasil `mysqldump` versi lain) mungkin perlu disesuaikan formatnya.
- **Kirim email otomatis** hanya mengirim sekali per siklus KGB (tidak mengirim ulang setiap hari selama 30 hari) — kecuali pengiriman sebelumnya gagal, yang akan otomatis dicoba lagi oleh cron di hari berikutnya, atau bisa dipicu manual lewat tombol "Kirim Ulang Email".
