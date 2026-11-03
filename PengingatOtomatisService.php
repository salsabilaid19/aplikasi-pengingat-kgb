<?php
/**
 * services/PengingatOtomatisService.php
 * Logika inti "jalankan pengecekan & kirim pengingat KGB otomatis", dipakai bersama oleh:
 *   - cron/kirim_pengingat_otomatis.php (dijalankan lewat crontab/Task Scheduler)
 *   - CronController (dipicu lewat URL, untuk hosting yang tidak menyediakan cron job,
 *     mis. hosting gratis — dipanggil oleh layanan penjadwal eksternal seperti cron-job.org)
 *
 * Disatukan di sini supaya kedua cara pemicu selalu punya perilaku yang identik.
 */
class PengingatOtomatisService
{
    public function jalankan(): array
    {
        $pegawaiModel = new PegawaiModel();
        $emailLogModel = new EmailLogModel();
        $notifikasiModel = new NotifikasiModel();
        $emailService = new EmailService();

        $dalam30Hari = $pegawaiModel->dalamRentangHari(30);

        $terkirim = 0;
        $gagal = 0;
        $dilewati = 0;
        $detail = [];

        foreach ($dalam30Hari as $pegawai) {
            $sudahDikirim = $emailLogModel->sudahDikirimUntukSiklus((int) $pegawai['id'], $pegawai['tanggal_kgb_berikutnya']);

            if ($sudahDikirim) {
                $dilewati++;
                continue;
            }

            $sukses = $emailService->kirimPengingatKgb($pegawai);
            if ($sukses) {
                $terkirim++;
                $detail[] = "Terkirim: {$pegawai['nama']} ({$pegawai['email']})";
            } else {
                $gagal++;
                $detail[] = "GAGAL: {$pegawai['nama']} ({$pegawai['email']})";
            }
        }

        if (count($dalam30Hari) > 0) {
            $notifikasiModel->tambah(
                "Hari ini ada " . count($dalam30Hari) . " pegawai yang memasuki masa pengingat KGB (30 hari ke depan).",
                'info'
            );
        }
        if ($gagal > 0) {
            $notifikasiModel->tambah("Ada {$gagal} email gagal dikirim hari ini.", 'danger');
        }
        if ($terkirim > 0) {
            $notifikasiModel->tambah("Berhasil mengirim {$terkirim} email pengingat KGB baru.", 'success');
        }

        // Heartbeat: dibaca oleh DashboardController untuk indikator "pengiriman otomatis aktif"
        file_put_contents(__DIR__ . '/../storage/logs/cron_heartbeat.txt', date('Y-m-d H:i:s'));

        return [
            'total_dalam_30_hari' => count($dalam30Hari),
            'terkirim' => $terkirim,
            'gagal' => $gagal,
            'dilewati' => $dilewati,
            'detail' => $detail,
        ];
    }
}
