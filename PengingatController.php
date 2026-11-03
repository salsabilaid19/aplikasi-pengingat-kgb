<?php
/**
 * controllers/PengingatController.php
 * Halaman "Pengingat KGB": tabel pegawai + status email + aksi.
 */
class PengingatController extends Controller
{
    private PegawaiModel $pegawaiModel;
    private EmailLogModel $emailLogModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
        $this->emailLogModel = new EmailLogModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        $pegawai = $this->pegawaiModel->allWithUnit();

        // Lengkapi tiap baris dengan sisa hari, status warna, dan status email terakhir
        $data = array_map(function ($p) {
            $sisaHari = hitung_sisa_hari($p['tanggal_kgb_berikutnya']);
            $status = status_kgb($sisaHari);
            $p['sisa_hari'] = $sisaHari;
            $p['status_kgb'] = $status;
            $p['status_email'] = $this->emailLogModel->statusTerbaruUntukPegawai((int) $p['id']);
            return $p;
        }, $pegawai);

        $this->view('pengingat/index', [
            'daftar'  => $data,
            'success' => $this->flash('pengingat_success'),
            'error'   => $this->flash('pengingat_error'),
        ]);
    }

    /**
     * Kirim ulang email untuk satu pegawai.
     * Catatan: pengiriman aktual (PHPMailer + SMTP Gmail) ditambahkan di Fase 2.
     * Untuk saat ini fungsi mencatat percobaan ke email_log agar alur & UI sudah utuh.
     */
    public function kirimUlang(int $id): void
    {
        $this->requireAuth();

        $pegawai = $this->pegawaiModel->find($id);
        if (!$pegawai) {
            $this->flash('pengingat_error', 'Pegawai tidak ditemukan.');
            $this->redirect('pengingat');
        }

        if (class_exists('EmailService')) {
            $emailService = new EmailService();
            $emailService->kirimPengingatKgb($pegawai);
            $this->flash('pengingat_success', "Email pengingat untuk {$pegawai['nama']} sedang diproses ulang.");
        } else {
            $this->flash('pengingat_error', 'Modul pengiriman email belum aktif (Fase 2). Data pegawai sudah tersimpan dengan benar.');
        }

        $this->redirect('pengingat');
    }

    /**
     * Kirim email pengingat KGB ke SEMUA pegawai yang belum berhasil dikirimi
     * untuk siklus KGB mereka saat ini (satu tombol, kirim massal).
     */
    public function kirimSemua(): void
    {
        $this->requireAuth();

        if (!class_exists('EmailService')) {
            $this->flash('pengingat_error', 'Modul pengiriman email belum aktif.');
            $this->redirect('pengingat');
        }

        $emailService = new EmailService();
        $semuaPegawai = $this->pegawaiModel->allWithUnit();

        $terkirim = 0;
        $gagal = 0;
        $dilewati = 0;

        foreach ($semuaPegawai as $pegawai) {
            $sudahDikirim = $this->emailLogModel->sudahDikirimUntukSiklus((int) $pegawai['id'], $pegawai['tanggal_kgb_berikutnya']);
            if ($sudahDikirim) {
                $dilewati++;
                continue;
            }
            $sukses = $emailService->kirimPengingatKgb($pegawai);
            $sukses ? $terkirim++ : $gagal++;
        }

        $pesan = "Selesai: {$terkirim} email terkirim";
        if ($gagal > 0) {
            $pesan .= ", {$gagal} gagal";
        }
        if ($dilewati > 0) {
            $pesan .= ", {$dilewati} dilewati (sudah pernah terkirim untuk siklus ini)";
        }
        $pesan .= '.';

        $gagal > 0 ? $this->flash('pengingat_error', $pesan) : $this->flash('pengingat_success', $pesan);
        $this->redirect('pengingat');
    }
}
