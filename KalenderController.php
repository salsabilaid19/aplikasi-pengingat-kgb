<?php
/**
 * controllers/KalenderController.php
 * Halaman Kalender KGB: menampilkan tanggal KGB seluruh pegawai di FullCalendar.
 */
class KalenderController extends Controller
{
    private PegawaiModel $pegawaiModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('kalender/index', []);
    }

    /** Endpoint JSON yang dikonsumsi FullCalendar (format events standar FullCalendar) */
    public function events(): void
    {
        $this->requireAuth();
        $pegawai = $this->pegawaiModel->allWithUnit();

        $events = array_map(function ($p) {
            $sisaHari = hitung_sisa_hari($p['tanggal_kgb_berikutnya']);
            $status = status_kgb($sisaHari);
            $warnaHex = [
                'success' => '#198754', 'info' => '#0dcaf0', 'warning' => '#fd7e14',
                'danger' => '#dc3545', 'dark' => '#343a40',
            ][$status['warna']] ?? '#0d3b7a';

            return [
                'id'    => $p['id'],
                'title' => $p['nama'],
                'start' => $p['tanggal_kgb_berikutnya'],
                'color' => $warnaHex,
                'extendedProps' => [
                    'jabatan'    => $p['jabatan'],
                    'sisa_hari'  => $sisaHari,
                    'status'     => $status['label'],
                    'pegawai_id' => $p['id'],
                ],
            ];
        }, $pegawai);

        $this->json($events);
    }
}
