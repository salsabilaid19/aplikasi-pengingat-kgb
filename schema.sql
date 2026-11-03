-- ============================================================
-- SKEMA DATABASE APLIKASI PENGINGAT KGB (Kenaikan Gaji Berkala)
-- ============================================================
CREATE DATABASE IF NOT EXISTS kgb_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kgb_app;

-- ------------------------------------------------------------
-- Tabel Admin (pengguna aplikasi)
-- ------------------------------------------------------------
CREATE TABLE admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- disimpan dengan password_hash() (bcrypt/argon2)
    foto_profil VARCHAR(255) DEFAULT NULL,
    remember_token VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel Unit Kerja
-- ------------------------------------------------------------
CREATE TABLE unit_kerja (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_unit VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel Pegawai
-- ------------------------------------------------------------
CREATE TABLE pegawai (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    jabatan VARCHAR(150) NOT NULL,
    unit_kerja_id INT UNSIGNED DEFAULT NULL,
    email VARCHAR(150) NOT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    golongan VARCHAR(20) DEFAULT NULL,
    tmt_kgb_terakhir DATE NOT NULL COMMENT 'Tanggal mulai KGB terakhir',
    periode_kgb_tahun TINYINT UNSIGNED NOT NULL DEFAULT 2 COMMENT 'Interval KGB dalam tahun, umumnya 2',
    tanggal_kgb_berikutnya DATE NOT NULL COMMENT 'Dihitung otomatis: tmt_kgb_terakhir + periode',
    status_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_kerja_id) REFERENCES unit_kerja(id) ON DELETE SET NULL,
    INDEX idx_tanggal_kgb (tanggal_kgb_berikutnya),
    INDEX idx_status_aktif (status_aktif)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel Riwayat KGB (histori tiap kali KGB diproses/diperbarui)
-- ------------------------------------------------------------
CREATE TABLE riwayat_kgb (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT UNSIGNED NOT NULL,
    tanggal_kgb DATE NOT NULL,
    keterangan VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel Log Pengiriman Email
-- ------------------------------------------------------------
CREATE TABLE email_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT UNSIGNED NOT NULL,
    nama_pegawai VARCHAR(150) NOT NULL,
    email_tujuan VARCHAR(150) NOT NULL,
    tanggal_kgb DATE NOT NULL,
    status ENUM('Belum Dikirim','Sudah Dikirim','Gagal') NOT NULL DEFAULT 'Belum Dikirim',
    error_message TEXT DEFAULT NULL,
    waktu_pengiriman DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_tanggal_kgb (tanggal_kgb)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Tabel Notifikasi Dashboard (internal, bukan email)
-- ------------------------------------------------------------
CREATE TABLE notifikasi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pesan VARCHAR(255) NOT NULL,
    tipe ENUM('info','warning','danger','success') NOT NULL DEFAULT 'info',
    sudah_dibaca TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Data awal: Unit Kerja contoh
-- ------------------------------------------------------------
INSERT INTO unit_kerja (nama_unit) VALUES
('Sekretariat'),
('Bidang Statistik Sosial'),
('Bidang Statistik Produksi'),
('Bidang Statistik Distribusi'),
('Bidang Neraca Wilayah dan Analisis Statistik'),
('Bidang Integrasi Pengolahan dan Diseminasi Statistik');

-- ------------------------------------------------------------
-- Admin default (password: admin123 -- WAJIB DIGANTI setelah login pertama)
-- Hash di bawah dibuat dengan password_hash('admin123', PASSWORD_BCRYPT)
-- ------------------------------------------------------------
INSERT INTO admins (nama, email, password) VALUES
('Administrator', 'admin@instansi.go.id', '$2y$10$iRZ4uB0o9/MQx.6RRhpU.OOrIBTuypfRu/Nm6Ugkw4S/DqvTZKkUG');
