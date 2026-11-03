<?php
/**
 * controllers/PegawaiController.php
 * Detail pegawai, form tambah/edit data pegawai.
 */
class PegawaiController extends Controller
{
    private PegawaiModel $pegawaiModel;
    private EmailLogModel $emailLogModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
        $this->emailLogModel = new EmailLogModel();
    }

    public function detail(int $id): void
    {
        $this->requireAuth();

        $pegawai = $this->pegawaiModel->findWithUnit($id);
        if (!$pegawai) {
            http_response_code(404);
            die('Pegawai tidak ditemukan.');
        }

        $sisaHari = hitung_sisa_hari($pegawai['tanggal_kgb_berikutnya']);
        $status = status_kgb($sisaHari);

        $this->view('pegawai/detail', [
            'pegawai'      => $pegawai,
            'sisaHari'     => $sisaHari,
            'status'       => $status,
            'riwayatKgb'   => $this->pegawaiModel->riwayatKgb($id),
            'riwayatEmail' => $this->emailLogModel->riwayatUntukPegawai($id),
        ]);
    }

    public function formTambah(): void
    {
        $this->requireAuth();
        $this->view('pegawai/form', [
            'pegawai'    => null,
            'unitKerja'  => Database::select('SELECT * FROM unit_kerja ORDER BY nama_unit'),
            'error'      => $this->flash('pegawai_error'),
        ]);
    }

    public function formEdit(int $id): void
    {
        $this->requireAuth();
        $pegawai = $this->pegawaiModel->find($id);
        if (!$pegawai) {
            http_response_code(404);
            die('Pegawai tidak ditemukan.');
        }
        $this->view('pegawai/form', [
            'pegawai'   => $pegawai,
            'unitKerja' => Database::select('SELECT * FROM unit_kerja ORDER BY nama_unit'),
            'error'     => $this->flash('pegawai_error'),
        ]);
    }

    public function simpan(): void
    {
        $this->requireAuth();
        $data = $this->validasiInput();

        if ($data === null) {
            $this->redirect('pegawai/tambah');
        }

        try {
            $id = $this->pegawaiModel->simpanBaru($data);
        } catch (PDOException $e) {
            $this->flash('pegawai_error', $this->pesanErrorRamah($e, $data['nip']));
            $this->redirect('pegawai/tambah');
        }

        $this->flash('pengingat_success', 'Data pegawai berhasil ditambahkan.');
        $this->redirect('pegawai/' . $id);
    }

    public function perbarui(int $id): void
    {
        $this->requireAuth();
        $data = $this->validasiInput();

        if ($data === null) {
            $this->redirect('pegawai/' . $id . '/edit');
        }

        try {
            $this->pegawaiModel->perbarui($id, $data);
        } catch (PDOException $e) {
            $this->flash('pegawai_error', $this->pesanErrorRamah($e, $data['nip']));
            $this->redirect('pegawai/' . $id . '/edit');
        }

        $this->flash('pengingat_success', 'Data pegawai berhasil diperbarui.');
        $this->redirect('pegawai/' . $id);
    }

    /** Terjemahkan error database mentah menjadi pesan yang ramah untuk pengguna */
    private function pesanErrorRamah(PDOException $e, string $nip): string
    {
        // SQLSTATE 23000 = integrity constraint violation (duplikat / FK)
        if ($e->getCode() === '23000' && str_contains($e->getMessage(), 'nip')) {
            return "NIP \"{$nip}\" sudah terdaftar untuk pegawai lain. Gunakan NIP yang berbeda, atau cek kembali data pegawai yang sudah ada.";
        }
        if ($e->getCode() === '23000') {
            return 'Data gagal disimpan karena bentrok dengan data yang sudah ada (kemungkinan NIP duplikat).';
        }

        error_log('Kesalahan database saat simpan pegawai: ' . $e->getMessage());
        return 'Terjadi kesalahan saat menyimpan data. Silakan coba lagi atau hubungi admin sistem.';
    }

    /**
     * "Hapus" pegawai yang sudah keluar/pindah instansi.
     * Diimplementasikan sebagai non-aktifkan (status_aktif = 0), BUKAN hapus baris
     * dari database — supaya riwayat KGB dan log email pegawai tsb tetap tersimpan
     * utuh untuk keperluan arsip/audit, dan pegawai otomatis hilang dari semua
     * daftar aktif (dashboard, pengingat, kalender, laporan) karena semua query
     * tersebut sudah memfilter status_aktif = 1.
     */
    public function hapus(int $id): void
    {
        $this->requireAuth();

        $pegawai = $this->pegawaiModel->find($id);
        if (!$pegawai) {
            $this->flash('pengingat_error', 'Pegawai tidak ditemukan.');
            $this->redirect('pengingat');
        }

        $this->pegawaiModel->update($id, ['status_aktif' => 0]);
        $this->flash('pengingat_success', "Data {$pegawai['nama']} berhasil dihapus dari daftar aktif.");
        $this->redirect('pengingat');
    }

    /** Validasi input form pegawai. Kembalikan array bersih atau null jika gagal (+ set flash error) */
    private function validasiInput(): ?array
    {
        $nip = trim($_POST['nip'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $jabatan = trim($_POST['jabatan'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tmtKgbTerakhir = $_POST['tmt_kgb_terakhir'] ?? '';

        if ($nip === '' || $nama === '' || $jabatan === '' || $tmtKgbTerakhir === '') {
            $this->flash('pegawai_error', 'NIP, Nama, Jabatan, dan Tanggal KGB Terakhir wajib diisi.');
            return null;
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('pegawai_error', 'Format email tidak valid.');
            return null;
        }

        return [
            'nip'                => $nip,
            'nama'               => $nama,
            'jabatan'            => $jabatan,
            'unit_kerja_id'      => !empty($_POST['unit_kerja_id']) ? (int) $_POST['unit_kerja_id'] : null,
            'email'              => $email,
            'no_hp'              => trim($_POST['no_hp'] ?? ''),
            'golongan'           => trim($_POST['golongan'] ?? ''),
            'tmt_kgb_terakhir'   => $tmtKgbTerakhir,
            'periode_kgb_tahun'  => (int) ($_POST['periode_kgb_tahun'] ?? 2),
        ];
    }
}
