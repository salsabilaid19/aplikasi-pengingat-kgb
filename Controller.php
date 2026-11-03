<?php
/**
 * core/Controller.php
 * Base controller: rendering view dengan layout, redirect, JSON response,
 * dan pengecekan sesi login.
 */
abstract class Controller
{
    /**
     * Render sebuah view di dalam layout utama.
     * @param string $view  path relatif terhadap folder /views, mis. "dashboard/index"
     * @param array  $data  variabel yang akan di-extract ke dalam view
     */
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        $viewFile = __DIR__ . "/../views/{$view}.php";

        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("View tidak ditemukan: {$view}");
        }

        // Tangkap output view dulu, baru masukkan ke layout sebagai $content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require __DIR__ . '/../views/layout/main.php';
    }

    /** Render view auth (tanpa layout sidebar, mis. halaman login) */
    protected function viewAuth(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . "/../views/{$view}.php";
    }

    protected function redirect(string $path): void
    {
        $base = rtrim(config('app.url'), '/');
        header("Location: {$base}/{$path}");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /** Pastikan admin sudah login, redirect ke /login jika belum */
    protected function requireAuth(): void
    {
        if (empty($_SESSION['admin_id'])) {
            $this->redirect('login');
        }
    }

    /** Ambil pesan flash sekali-pakai (mis. notifikasi sukses/gagal setelah redirect) */
    protected function flash(string $key, ?string $message = null)
    {
        if ($message !== null) {
            $_SESSION['flash'][$key] = $message;
            return null;
        }
        $value = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $value;
    }
}
