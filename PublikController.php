<?php
/**
 * controllers/PublikController.php
 * Halaman publik (tanpa perlu login) agar pegawai bisa mengecek tanggal KGB
 * mereka sendiri dengan memasukkan NIP. Sengaja tidak menampilkan data pribadi
 * lain (email, no HP) di halaman ini demi privasi.
 */
class PublikController extends Controller
{
    private PegawaiModel $pegawaiModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
    }

    public function formCek(): void
    {
        $this->viewAuth('publik/cek_kgb', [
            'hasil' => null,
            'nipDicari' => '',
            'error' => null,
        ]);
    }

    public function cari(): void
    {
        $nip = trim($_GET['nip'] ?? '');
        $hasil = null;
        $error = null;

        if ($nip === '') {
            $error = 'Silakan masukkan NIP terlebih dahulu.';
        } else {
            $pegawai = Database::selectOne(
                "SELECT p.*, u.nama_unit FROM pegawai p LEFT JOIN unit_kerja u ON u.id = p.unit_kerja_id
                 WHERE p.nip = ? AND p.status_aktif = 1",
                [$nip]
            );

            if (!$pegawai) {
                $error = 'NIP tidak ditemukan. Pastikan NIP yang dimasukkan sudah benar, atau hubungi bagian kepegawaian.';
            } else {
                $sisaHari = hitung_sisa_hari($pegawai['tanggal_kgb_berikutnya']);
                $status = status_kgb($sisaHari);
                $hasil = [
                    'nama'          => $pegawai['nama'],
                    'jabatan'       => $pegawai['jabatan'],
                    'nama_unit'     => $pegawai['nama_unit'],
                    'tmt_terakhir'  => $pegawai['tmt_kgb_terakhir'],
                    'tanggal_kgb'   => $pegawai['tanggal_kgb_berikutnya'],
                    'sisa_hari'     => $sisaHari,
                    'status'        => $status,
                ];
            }
        }

        $this->viewAuth('publik/cek_kgb', [
            'hasil'     => $hasil,
            'nipDicari' => $nip,
            'error'     => $error,
        ]);
    }
}
