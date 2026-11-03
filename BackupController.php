<?php
/**
 * controllers/BackupController.php
 * Menu Backup & Restore Database.
 */
class BackupController extends Controller
{
    private BackupService $backupService;

    public function __construct()
    {
        $this->backupService = new BackupService();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('backup/index', [
            'daftar'  => $this->backupService->daftarBackup(),
            'success' => $this->flash('backup_success'),
            'error'   => $this->flash('backup_error'),
        ]);
    }

    public function buat(): void
    {
        $this->requireAuth();
        try {
            $filename = $this->backupService->buatBackup();
            $this->flash('backup_success', "Backup berhasil dibuat: {$filename}");
        } catch (Throwable $e) {
            error_log('Backup error: ' . $e->getMessage());
            $this->flash('backup_error', 'Gagal membuat backup. Periksa log server untuk detail.');
        }
        $this->redirect('backup');
    }

    public function unduh(string $filename): void
    {
        $this->requireAuth();
        $path = $this->backupService->pathBackup($filename);

        if ($path === null) {
            http_response_code(404);
            die('File backup tidak ditemukan.');
        }

        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    public function restore(): void
    {
        $this->requireAuth();

        if (empty($_FILES['file_sql']['tmp_name']) || $_FILES['file_sql']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('backup_error', 'Silakan pilih file .sql yang valid untuk di-restore.');
            $this->redirect('backup');
        }

        $ext = strtolower(pathinfo($_FILES['file_sql']['name'], PATHINFO_EXTENSION));
        if ($ext !== 'sql') {
            $this->flash('backup_error', 'File harus berformat .sql');
            $this->redirect('backup');
        }

        try {
            $content = file_get_contents($_FILES['file_sql']['tmp_name']);
            $this->backupService->restoreDariIsi($content);
            $this->flash('backup_success', 'Database berhasil di-restore dari file backup.');
        } catch (Throwable $e) {
            error_log('Restore error: ' . $e->getMessage());
            $this->flash('backup_error', 'Restore gagal: ' . $e->getMessage());
        }

        $this->redirect('backup');
    }
}
