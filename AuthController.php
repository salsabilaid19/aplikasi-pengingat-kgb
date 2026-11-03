<?php
/**
 * controllers/AuthController.php
 * Menangani login, logout, dan pembaruan profil admin.
 */
class AuthController extends Controller
{
    private AdminModel $adminModel;

    public function __construct()
    {
        $this->adminModel = new AdminModel();
    }

    public function showLogin(): void
    {
        // Jika sudah login, langsung ke dashboard
        if (!empty($_SESSION['admin_id'])) {
            $this->redirect('dashboard');
        }
        $this->viewAuth('auth/login', ['error' => $this->flash('login_error')]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $this->flash('login_error', 'Email dan password wajib diisi.');
            $this->redirect('login');
        }

        $admin = $this->adminModel->verifyCredentials($email, $password);

        if (!$admin) {
            $this->flash('login_error', 'Email atau password salah.');
            $this->redirect('login');
        }

        // Cegah session fixation
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_nama'] = $admin['nama'];
        $_SESSION['admin_foto'] = $admin['foto_profil'];

        $this->redirect('dashboard');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('login');
    }

    public function profil(): void
    {
        $this->requireAuth();
        $admin = $this->adminModel->find((int) $_SESSION['admin_id']);
        $this->view('auth/profil', [
            'admin'   => $admin,
            'success' => $this->flash('profil_success'),
            'error'   => $this->flash('profil_error'),
        ]);
    }

    public function updateProfil(): void
    {
        $this->requireAuth();
        $id = (int) $_SESSION['admin_id'];

        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if ($nama === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->flash('profil_error', 'Nama wajib diisi dan email harus valid.');
            $this->redirect('profil');
        }

        $fotoPath = null;
        if (!empty($_FILES['foto_profil']['name'])) {
            $fotoPath = $this->simpanFotoProfil($_FILES['foto_profil']);
            if ($fotoPath === null) {
                $this->flash('profil_error', 'Foto profil harus berupa JPG/PNG maksimal 2MB.');
                $this->redirect('profil');
            }
        }

        $this->adminModel->updateProfile($id, $nama, $email, $fotoPath);

        // Ganti password hanya jika diisi
        if (!empty($_POST['password_baru'])) {
            if (strlen($_POST['password_baru']) < 8) {
                $this->flash('profil_error', 'Password baru minimal 8 karakter (profil tersimpan, password tidak diubah).');
                $this->redirect('profil');
            }
            $this->adminModel->updatePassword($id, $_POST['password_baru']);
        }

        $_SESSION['admin_nama'] = $nama;
        if ($fotoPath) {
            $_SESSION['admin_foto'] = $fotoPath;
        }

        $this->flash('profil_success', 'Profil berhasil diperbarui.');
        $this->redirect('profil');
    }

    /** Validasi & simpan upload foto profil, kembalikan path relatif atau null jika gagal */
    private function simpanFotoProfil(array $file): ?string
    {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > $maxSize) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes, true)) {
            return null;
        }

        $ext = $mime === 'image/png' ? 'png' : 'jpg';
        $filename = 'profil_' . uniqid() . '.' . $ext;
        $destination = __DIR__ . '/../public/uploads/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return null;
        }

        return 'uploads/' . $filename;
    }
}
