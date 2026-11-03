<?php
/**
 * controllers/CronController.php
 * Endpoint HTTP untuk memicu pengiriman pengingat KGB otomatis, khusus untuk hosting
 * yang TIDAK menyediakan cron job asli (mis. paket hosting gratis). Dipanggil secara
 * berkala oleh layanan penjadwal eksternal seperti cron-job.org atau EasyCron.
 *
 * Dilindungi oleh token rahasia (CRON_TOKEN di .env) — TANPA login admin, karena
 * layanan penjadwal eksternal tidak bisa login. Siapa pun yang tahu token bisa
 * memicu endpoint ini, jadi WAJIB pakai token yang panjang & acak (lihat .env.example).
 */
class CronController extends Controller
{
    public function jalankanViaWeb(): void
    {
        $tokenDikirim = $_GET['token'] ?? '';
        $tokenAsli = env('CRON_TOKEN', '');

        if (empty($tokenAsli) || $tokenAsli === 'ubah-dengan-token-acak-yang-panjang') {
            http_response_code(403);
            header('Content-Type: text/plain');
            die("Ditolak: CRON_TOKEN belum diatur di file .env. Atur dulu ke nilai acak yang panjang & rahasia sebelum memakai endpoint ini.\n");
        }

        if (!hash_equals($tokenAsli, $tokenDikirim)) {
            http_response_code(403);
            header('Content-Type: text/plain');
            die("Ditolak: token tidak cocok.\n");
        }

        $hasil = (new PengingatOtomatisService())->jalankan();

        header('Content-Type: text/plain');
        echo '[' . date('Y-m-d H:i:s') . "] Pengingat KGB otomatis dijalankan via web.\n";
        foreach ($hasil['detail'] as $baris) {
            echo "  - {$baris}\n";
        }
        echo "Selesai. Total dalam 30 hari: {$hasil['total_dalam_30_hari']}, Terkirim: {$hasil['terkirim']}, Gagal: {$hasil['gagal']}, Dilewati: {$hasil['dilewati']}\n";
    }
}
