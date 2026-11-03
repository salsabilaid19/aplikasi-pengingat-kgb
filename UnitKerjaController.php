<?php
/**
 * controllers/UnitKerjaController.php
 * CRUD sederhana untuk data Unit Kerja (dipakai sebagai referensi di form Pegawai).
 */
class UnitKerjaController extends Controller
{
    private UnitKerjaModel $unitKerjaModel;
    private PegawaiModel $pegawaiModel;

    public function __construct()
    {
        $this->unitKerjaModel = new UnitKerjaModel();
        $this->pegawaiModel = new PegawaiModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        // Hitung jumlah pegawai per unit kerja (untuk ditampilkan di tabel), tanpa query N+1
        $semuaPegawai = $this->pegawaiModel->allWithUnit();
        $jumlahPerUnit = [];
        foreach ($semuaPegawai as $p) {
            $uid = $p['unit_kerja_id'];
            if ($uid !== null) {
                $jumlahPerUnit[$uid] = ($jumlahPerUnit[$uid] ?? 0) + 1;
            }
        }

        $daftar = $this->unitKerjaModel->all('nama_unit ASC');

        $this->view('unit_kerja/index', [
            'daftar'        => $daftar,
            'jumlahPerUnit' => $jumlahPerUnit,
            'success'       => $this->flash('unit_success'),
            'error'         => $this->flash('unit_error'),
        ]);
    }

    public function formTambah(): void
    {
        $this->requireAuth();
        $this->view('unit_kerja/form', ['unit' => null, 'error' => $this->flash('unit_form_error')]);
    }

    public function formEdit(int $id): void
    {
        $this->requireAuth();
        $unit = $this->unitKerjaModel->find($id);
        if (!$unit) {
            http_response_code(404);
            die('Unit kerja tidak ditemukan.');
        }
        $this->view('unit_kerja/form', ['unit' => $unit, 'error' => $this->flash('unit_form_error')]);
    }

    public function simpan(): void
    {
        $this->requireAuth();
        $nama = trim($_POST['nama_unit'] ?? '');

        if ($nama === '') {
            $this->flash('unit_form_error', 'Nama unit kerja wajib diisi.');
            $this->redirect('unit-kerja/tambah');
        }

        $this->unitKerjaModel->insert(['nama_unit' => $nama]);
        $this->flash('unit_success', 'Unit kerja berhasil ditambahkan.');
        $this->redirect('unit-kerja');
    }

    public function perbarui(int $id): void
    {
        $this->requireAuth();
        $nama = trim($_POST['nama_unit'] ?? '');

        if ($nama === '') {
            $this->flash('unit_form_error', 'Nama unit kerja wajib diisi.');
            $this->redirect("unit-kerja/{$id}/edit");
        }

        $this->unitKerjaModel->update($id, ['nama_unit' => $nama]);
        $this->flash('unit_success', 'Unit kerja berhasil diperbarui.');
        $this->redirect('unit-kerja');
    }

    public function hapus(int $id): void
    {
        $this->requireAuth();
        // Aman: pegawai yang terkait akan otomatis menjadi unit_kerja_id = NULL (ON DELETE SET NULL)
        $this->unitKerjaModel->delete($id);
        $this->flash('unit_success', 'Unit kerja berhasil dihapus.');
        $this->redirect('unit-kerja');
    }
}
