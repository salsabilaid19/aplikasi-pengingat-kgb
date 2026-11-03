<?php
/**
 * controllers/EmailLogController.php
 * Halaman "Log Pengiriman Email": riwayat seluruh percobaan pengiriman,
 * dengan pencarian & filter berdasarkan tanggal maupun status.
 */
class EmailLogController extends Controller
{
    private EmailLogModel $emailLogModel;

    public function __construct()
    {
        $this->emailLogModel = new EmailLogModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        $status = $_GET['status'] ?? '';
        $dari = $_GET['dari'] ?? '';
        $sampai = $_GET['sampai'] ?? '';

        $log = $this->emailLogModel->filter(
            $status !== '' ? $status : null,
            $dari !== '' ? $dari : null,
            $sampai !== '' ? $sampai : null
        );

        $this->view('email_log/index', [
            'log'    => $log,
            'status' => $status,
            'dari'   => $dari,
            'sampai' => $sampai,
        ]);
    }
}
