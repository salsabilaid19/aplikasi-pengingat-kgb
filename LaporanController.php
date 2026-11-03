<?php
/**
 * controllers/LaporanController.php
 * Halaman laporan: tampilan cetak (print-friendly) + ekspor PDF/Excel.
 * Ekspor dibuat tanpa dependency composer (CSV/HTML-based agar ringan dan portabel).
 */
class LaporanController extends Controller
{
    private PegawaiModel $pegawaiModel;
    private EmailLogModel $emailLogModel;

    public function __construct()
    {
        $this->pegawaiModel = new PegawaiModel();
        $this->emailLogModel = new EmailLogModel();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('laporan/index', []);
    }

    /**
     * Tampilan cetak (dibuka di tab baru, memicu window.print() otomatis).
     * jenis: semua | bulan-ini | sudah-dikirim | belum-dikirim | rekap
     */
    public function cetak(string $jenis): void
    {
        $this->requireAuth();
        [$judul, $data, $kolom] = $this->siapkanData($jenis);

        // Halaman cetak tidak memakai layout sidebar
        $this->viewAuth('laporan/cetak', compact('judul', 'data', 'kolom', 'jenis'));
    }

    /** Ekspor ke Excel (format .xls berbasis HTML table, dibuka native oleh Excel/LibreOffice) */
    public function exportExcel(string $jenis): void
    {
        $this->requireAuth();
        [$judul, $data, $kolom] = $this->siapkanData($jenis);

        $filename = 'laporan_' . $jenis . '_' . date('Ymd_His') . '.xls';
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo "\xEF\xBB\xBF"; // BOM agar karakter UTF-8 tampil benar di Excel
        echo '<table border="1"><tr><th colspan="' . count($kolom) . '">' . htmlspecialchars($judul) . '</th></tr><tr>';
        foreach ($kolom as $k) {
            echo '<th>' . htmlspecialchars($k) . '</th>';
        }
        echo '</tr>';
        foreach ($data as $baris) {
            echo '<tr>';
            foreach ($baris as $nilai) {
                echo '<td>' . htmlspecialchars((string) $nilai) . '</td>';
            }
            echo '</tr>';
        }
        echo '</table>';
        exit;
    }

    /** Ekspor ke PDF sederhana (memakai halaman cetak yang sama; browser men-generate PDF via print dialog) */
    public function exportPdf(string $jenis): void
    {
        $this->requireAuth();
        [$judul, $data, $kolom] = $this->siapkanData($jenis);
        $this->viewAuth('laporan/cetak', ['judul' => $judul, 'data' => $data, 'kolom' => $kolom, 'jenis' => $jenis, 'modePdf' => true]);
    }

    /**
     * Siapkan data laporan sesuai jenis yang diminta.
     * @return array{0:string,1:array,2:array} [judul, data, nama_kolom]
     */
    private function siapkanData(string $jenis): array
    {
        switch ($jenis) {
            case 'bulan-ini':
                $judul = 'Pegawai yang Akan KGB Bulan Ini';
                $semua = $this->pegawaiModel->allWithUnit();
                $bulanIni = date('Y-m');
                $filtered = array_filter($semua, fn($p) => substr($p['tanggal_kgb_berikutnya'], 0, 7) === $bulanIni);
                $kolom = ['NIP', 'Nama', 'Jabatan', 'Unit Kerja', 'Tanggal KGB'];
                $data = array_map(fn($p) => [$p['nip'], $p['nama'], $p['jabatan'], $p['nama_unit'] ?? '-', format_tanggal_indo($p['tanggal_kgb_berikutnya'])], $filtered);
                break;

            case 'sudah-dikirim':
                $judul = 'Pegawai yang Sudah Dikirim Email Pengingat';
                $log = $this->emailLogModel->filter('Sudah Dikirim');
                $kolom = ['Nama Pegawai', 'Email', 'Tanggal KGB', 'Waktu Pengiriman'];
                $data = array_map(fn($l) => [$l['nama_pegawai'], $l['email_tujuan'], format_tanggal_indo($l['tanggal_kgb']), format_tanggal_indo($l['waktu_pengiriman'])], $log);
                break;

            case 'belum-dikirim':
                $judul = 'Pegawai yang Belum Dikirim Email Pengingat';
                $semua = $this->pegawaiModel->allWithUnit();
                $filtered = array_filter($semua, fn($p) => $this->emailLogModel->statusTerbaruUntukPegawai((int) $p['id']) !== 'Sudah Dikirim');
                $kolom = ['NIP', 'Nama', 'Jabatan', 'Unit Kerja', 'Tanggal KGB'];
                $data = array_map(fn($p) => [$p['nip'], $p['nama'], $p['jabatan'], $p['nama_unit'] ?? '-', format_tanggal_indo($p['tanggal_kgb_berikutnya'])], $filtered);
                break;

            case 'rekap':
                $judul = 'Rekap Pengiriman Email';
                $log = $this->emailLogModel->filter();
                $kolom = ['Nama Pegawai', 'Email', 'Status', 'Waktu Pengiriman', 'Keterangan'];
                $data = array_map(fn($l) => [$l['nama_pegawai'], $l['email_tujuan'], $l['status'], $l['waktu_pengiriman'] ? format_tanggal_indo($l['waktu_pengiriman']) : '-', $l['error_message'] ?? ''], $log);
                break;

            case 'semua':
            default:
                $judul = 'Seluruh Data Pegawai';
                $semua = $this->pegawaiModel->allWithUnit();
                $kolom = ['NIP', 'Nama', 'Jabatan', 'Unit Kerja', 'Golongan', 'Tanggal KGB Terakhir', 'Tanggal KGB Berikutnya'];
                $data = array_map(fn($p) => [
                    $p['nip'], $p['nama'], $p['jabatan'], $p['nama_unit'] ?? '-', $p['golongan'] ?: '-',
                    format_tanggal_indo($p['tmt_kgb_terakhir']), format_tanggal_indo($p['tanggal_kgb_berikutnya']),
                ], $semua);
                break;
        }

        return [$judul, array_values($data), $kolom];
    }
}
