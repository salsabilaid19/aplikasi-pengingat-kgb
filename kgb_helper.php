<?php
/**
 * helpers/kgb_helper.php
 * Kumpulan fungsi murni untuk menghitung tanggal & status KGB.
 * Dipisah dari Model/Controller agar mudah di-unit-test.
 */

/**
 * Hitung tanggal KGB berikutnya berdasarkan tanggal KGB terakhir + periode (tahun).
 */
function hitung_tanggal_kgb_berikutnya(string $tmtTerakhir, int $periodeTahun = 2): string
{
    $tanggal = new DateTime($tmtTerakhir);
    $tanggal->modify("+{$periodeTahun} years");
    return $tanggal->format('Y-m-d');
}

/**
 * Hitung sisa hari dari hari ini menuju tanggal KGB.
 * Nilai negatif berarti tanggal KGB sudah lewat.
 */
function hitung_sisa_hari(string $tanggalKgb): int
{
    $hariIni = new DateTime('today');
    $tglKgb = new DateTime($tanggalKgb);
    return (int) $hariIni->diff($tglKgb)->format('%r%a');
}

/**
 * Tentukan status warna kartu berdasarkan sisa hari.
 * Mengembalikan array [kode, label, warna_bootstrap, emoji]
 */
function status_kgb(int $sisaHari): array
{
    $threshold = config('app.kgb_threshold');

    if ($sisaHari < 0) {
        return ['kode' => 'lewat', 'label' => 'Sudah Lewat', 'warna' => 'dark', 'emoji' => '⚫'];
    }
    if ($sisaHari <= $threshold['merah']) {
        return ['kode' => 'merah', 'label' => 'Kurang dari 7 Hari', 'warna' => 'danger', 'emoji' => '🔴'];
    }
    if ($sisaHari <= $threshold['orange']) {
        return ['kode' => 'orange', 'label' => 'Kurang dari 14 Hari', 'warna' => 'warning', 'emoji' => '🟠'];
    }
    if ($sisaHari <= $threshold['kuning']) {
        return ['kode' => 'kuning', 'label' => 'Kurang dari 30 Hari', 'warna' => 'info', 'emoji' => '🟡'];
    }
    return ['kode' => 'hijau', 'label' => 'Lebih dari 30 Hari', 'warna' => 'success', 'emoji' => '🟢'];
}

/**
 * Format tanggal ke format Indonesia, mis. "24 Juli 2026"
 */
function format_tanggal_indo(?string $tanggal): string
{
    if (empty($tanggal)) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $dt = new DateTime($tanggal);
    return $dt->format('d') . ' ' . $bulan[(int) $dt->format('n')] . ' ' . $dt->format('Y');
}

/** Escape output HTML secara singkat (alias htmlspecialchars) */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
