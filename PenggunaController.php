<?php
/**
 * controllers/PenggunaController.php
 * Manajemen akun Admin (Pengguna) yang boleh login ke aplikasi.
 */
class PenggunaController extends Controller
{
    private AdminModel $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('pengguna/index', [
            'daftar'      => $this->adminModel->semuaAdmin(),
            'adminIdSaya' => (int) $_SESSION['admin_id'],
            'success'     => $this->flash('pengguna_success'),
            'error'       => $this->flash('pengguna_error'),
        ]);
    }

    public function formTambah(): void
    {
        $this->requireAuth();
        $this->view('pengguna/form', ['pengguna' => null, 'error' => $this->flash('pengguna_form_error')]);
    }

    public function formEdit(int $id): void
    {
        $this->requireAuth();
        $pengguna = $this->adminModel->find($id);
        if (!$pengguna) {
            http_response_code(404);
            die('Pengguna tidak ditemukan.');
        }
        $this->view('pengguna/form', ['pengguna' => $pengguna, 'error' => $this->flash('pengguna_form_error')]);
    }

    public function simpan(): void
    {
        $this->requireAuth();
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nama === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('pengguna_form_error', 'Nama wajib diisi dan email harus valid.');
            $this->redirect('pengguna/tambah');
        }
        if (strlen($password) < 8) {
            $this->flash('pengguna_form_error', 'Password minimal 8 karakter.');
            $this->redirect('pengguna/tambah');
        }
        if ($this->adminModel->emailSudahDipakai($email)) {
            $this->flash('pengguna_form_error', 'Email tersebut sudah dipakai admin lain.');
            $this->redirect('pengguna/tambah');
        }

        $this->adminModel->buatAdminBaru($nama, $email, $password);
        $this->flash('pengguna_success', 'Akun admin baru berhasil ditambahkan.');
        $this->redirect('pengguna');
    }

    public function perbarui(int $id): void
    {
        $this->requireAuth();
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nama === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('pengguna_form_error', 'Nama wajib diisi dan email harus valid.');
            $this->redirect("pengguna/{$id}/edit");
        }
        if ($this->adminModel->emailSudahDipakai($email, $id)) {
            $this->flash('pengguna_form_error', 'Email tersebut sudah dipakai admin lain.');
            $this->redirect("pengguna/{$id}/edit");
        }

        $this->adminModel->updateProfile($id, $nama, $email);

        if (!empty($password)) {
            if (strlen($password) < 8) {
                $this->flash('pengguna_form_error', 'Password baru minimal 8 karakter (nama/email tersimpan, password tidak diubah).');
                $this->redirect("pengguna/{$id}/edit");
            }
            $this->adminModel->updatePassword($id, $password);
        }

        // Kalau admin mengedit akunnya sendiri, sinkronkan nama di session
        if ($id === (int) $_SESSION['admin_id']) {
            $_SESSION['admin_nama'] = $nama;
        }

        $this->flash('pengguna_success', 'Data pengguna berhasil diperbarui.');
        $this->redirect('pengguna');
    }

    public function hapus(int $id): void
    {
        $this->requireAuth();

        if ($id === (int) $_SESSION['admin_id']) {
            $this->flash('pengguna_error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.');
            $this->redirect('pengguna');
        }
        if ($this->adminModel->jumlahAdmin() <= 1) {
            $this->flash('pengguna_error', 'Tidak dapat menghapus — minimal harus ada 1 akun admin.');
            $this->redirect('pengguna');
        }

        $this->adminModel->delete($id);
        $this->flash('pengguna_success', 'Akun pengguna berhasil dihapus.');
        $this->redirect('pengguna');
    }
}
