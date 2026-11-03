<?php
/**
 * cron/kirim_pengingat_otomatis.php
 *
 * Dijalankan otomatis setiap hari lewat cron job / Task Scheduler.
 * Logika utamanya ada di services/PengingatOtomatisService.php (dipakai bersama
 * dengan CronController untuk hosting yang tidak menyediakan cron job).
 *
 * Cara menjadwalkan (Linux, jalankan sekali sehari jam 07:00):
 *   0 7 * * * /usr/bin/php /path/ke/proyek/cron/kirim_pengingat_otomatis.php >> /path/ke/proyek/storage/logs/cron.log 2>&1
 *
 * Cara menjadwalkan di Windows: gunakan Task Scheduler (lihat README.md).
 */

chdir(__DIR__ . '/..');
require __DIR__ . '/../helpers/config_helper.php';
require __DIR__ . '/../config/env.php';
load_env(__DIR__ . '/../.env');
date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
require __DIR__ . '/../helpers/kgb_helper.php';

spl_autoload_register(function (string $class) {
    foreach (['core', 'models', 'controllers', 'services'] as $dir) {
        $file = __DIR__ . "/../$dir/$class.php";
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});

echo '[' . date('Y-m-d H:i:s') . "] Mulai proses pengingat KGB otomatis...\n";

$hasil = (new PengingatOtomatisService())->jalankan();

foreach ($hasil['detail'] as $baris) {
    echo "  - {$baris}\n";
}

echo '[' . date('Y-m-d H:i:s') . "] Selesai. Terkirim: {$hasil['terkirim']}, Gagal: {$hasil['gagal']}, Dilewati (sudah pernah dikirim): {$hasil['dilewati']}\n";
