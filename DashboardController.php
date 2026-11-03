<?php
/**
 * controllers/DashboardController.php
 * Halaman utama setelah login: kartu status KGB, notifikasi, kalender ringkas.
 */
class DashboardController extends Controller
{
    private PegawaiModel $pegawaiModel;
    private NotifikasiModel $notifikasiModel;
    private EmailLogModel $emailLogModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
        $this->notifikasiModel = new NotifikasiModel();
        $this->emailLogModel = new EmailLogModel();
    }

    public function index(): void
    {
        $this->requireAuth();

        $kartu = $this->pegawaiModel->hitungKartuStatus();
        $notifikasi = $this->notifikasiModel->terbaru(10);
        $belumDibaca = $this->notifikasiModel->jumlahBelumDibaca();
        $gagalHariIni = $this->emailLogModel->hitungGagalHariIni();
        $dalam30Hari = $this->pegawaiModel->dalamRentangHari(30);
        // Data lengkap pegawai (memakai method yang sudah ada, dipakai juga oleh PengingatController)
        // — ditambahkan di sini hanya untuk keperluan tampilan grafik & tabel dashboard baru.
        $semuaPegawai = $this->pegawaiModel->allWithUnit();
        $statusCron = $this->cekStatusCron();

        $this->view('dashboard/index', [
            'kartu'         => $kartu,
            'notifikasi'    => $notifikasi,
            'belumDibaca'   => $belumDibaca,
            'gagalHariIni'  => $gagalHariIni,
            'dalam30Hari'   => $dalam30Hari,
            'semuaPegawai'  => $semuaPegawai,
            'statusCron'    => $statusCron,
        ]);
    }

    /**
     * Baca file heartbeat yang ditulis oleh cron/kirim_pengingat_otomatis.php setiap
     * kali berhasil berjalan, untuk mendeteksi apakah penjadwalan otomatis benar-benar
     * aktif di server atau belum pernah/berhenti berjalan (mis. lupa disetel).
     *
     * @return array{status:string, pesan:string, terakhir:?string}
     */
    private function cekStatusCron(): array
    {
        $path = __DIR__ . '/../storage/logs/cron_heartbeat.txt';

        if (!file_exists($path)) {
            return [
                'status'   => 'belum_aktif',
                'pesan'    => 'Pengiriman email otomatis (cron job) belum pernah berjalan di server ini. Email pengingat KGB HANYA akan terkirim kalau Anda mengklik "Kirim Semua Email" secara manual.',
                'terakhir' => null,
            ];
        }

        $waktuTerakhir = trim(file_get_contents($path));
        $jamLalu = (time() - strtotime($waktuTerakhir)) / 3600;

        if ($jamLalu > 26) {
            return [
                'status'   => 'terlambat',
                'pesan'    => "Penjadwalan otomatis terakhir berjalan " . round($jamLalu / 24, 1) . " hari lalu — lebih dari 1 hari. Periksa apakah cron job/Task Scheduler di server masih aktif.",
                'terakhir' => $waktuTerakhir,
            ];
        }

        return [
            'status'   => 'aktif',
            'pesan'    => "Penjadwalan otomatis berjalan normal, terakhir " . round($jamLalu) . " jam yang lalu.",
            'terakhir' => $waktuTerakhir,
        ];
    }

    /** Kartu diklik -> tampilkan daftar pegawai untuk kategori tsb */
    public function kategori(string $kode): void
    {
        $this->requireAuth();
        $daftarValid = ['hijau', 'kuning', 'orange', 'merah', 'lewat'];
        if (!in_array($kode, $daftarValid, true)) {
            $kode = 'hijau';
        }

        $pegawai = $this->pegawaiModel->byStatusKategori($kode);
        $labelKategori = [
            'hijau'  => 'KGB masih lebih dari 30 hari',
            'kuning' => 'KGB kurang dari 30 hari',
            'orange' => 'KGB kurang dari 14 hari',
            'merah'  => 'KGB kurang dari 7 hari',
            'lewat'  => 'KGB sudah lewat tanggal',
        ];

        $this->view('dashboard/kategori', [
            'pegawai' => $pegawai,
            'kode'    => $kode,
            'label'   => $labelKategori[$kode],
        ]);
    }

    /** Endpoint AJAX untuk kolom pencarian cepat di dashboard */
    public function search(): void
    {
        $this->requireAuth();
        $keyword = trim($_GET['q'] ?? '');

        if (strlen($keyword) < 2) {
            $this->json(['results' => []]);
        }

        $hasil = $this->pegawaiModel->quickSearch($keyword);

        $results = array_map(function ($p) {
            $sisaHari = hitung_sisa_hari($p['tanggal_kgb_berikutnya']);
            $status = status_kgb($sisaHari);
            return [
                'id'          => $p['id'],
                'nama'        => $p['nama'],
                'nip'         => $p['nip'],
                'jabatan'     => $p['jabatan'],
                'unit_kerja'  => $p['nama_unit'] ?? '-',
                'sisa_hari'   => $sisaHari,
                'status'      => $status['label'],
                'warna'       => $status['warna'],
                'url'         => config('app.url') . '/pegawai/' . $p['id'],
            ];
        }, $hasil);

        $this->json(['results' => $results]);
    }

    /** Tandai semua notifikasi sudah dibaca (dipanggil saat lonceng diklik) */
    public function bacaNotifikasi(): void
    {
        $this->requireAuth();
        $this->notifikasiModel->tandaiSemuaSudahDibaca();
        $this->json(['success' => true]);
    }
}
