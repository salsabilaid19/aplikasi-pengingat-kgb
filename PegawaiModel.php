<?php
/**
 * models/PegawaiModel.php
 * Model untuk data pegawai + query terkait perhitungan KGB.
 */
class PegawaiModel extends Model
{
    protected string $table = 'pegawai';

    private const SELECT_JOIN = "
        SELECT p.*, u.nama_unit
        FROM pegawai p
        LEFT JOIN unit_kerja u ON u.id = p.unit_kerja_id
    ";

    /** Semua pegawai aktif beserta nama unit kerja, diurutkan berdasarkan tanggal KGB terdekat */
    public function allWithUnit(): array
    {
        return Database::select(self::SELECT_JOIN . " WHERE p.status_aktif = 1 ORDER BY p.tanggal_kgb_berikutnya ASC");
    }

    public function findWithUnit(int $id): ?array
    {
        return Database::selectOne(self::SELECT_JOIN . " WHERE p.id = ?", [$id]);
    }

    /**
     * Cari pegawai cepat (untuk kolom pencarian dashboard).
     * Mencocokkan nama, NIP, jabatan, atau unit kerja.
     */
    public function quickSearch(string $keyword, int $limit = 15): array
    {
        $like = '%' . $keyword . '%';
        return Database::select(
            self::SELECT_JOIN . "
            WHERE p.status_aktif = 1
              AND (p.nama LIKE ? OR p.nip LIKE ? OR p.jabatan LIKE ? OR u.nama_unit LIKE ?)
            ORDER BY p.tanggal_kgb_berikutnya ASC
            LIMIT {$limit}",
            [$like, $like, $like, $like]
        );
    }

    /**
     * Hitung jumlah pegawai per kategori status KGB (untuk kartu dashboard).
     * Mengembalikan array asosiatif: ['hijau'=>n, 'kuning'=>n, 'orange'=>n, 'merah'=>n, 'lewat'=>n]
     */
    public function hitungKartuStatus(): array
    {
        $semua = $this->allWithUnit();
        $counter = ['hijau' => 0, 'kuning' => 0, 'orange' => 0, 'merah' => 0, 'lewat' => 0];

        foreach ($semua as $pegawai) {
            $sisaHari = hitung_sisa_hari($pegawai['tanggal_kgb_berikutnya']);
            $status = status_kgb($sisaHari);
            $counter[$status['kode']]++;
        }

        return $counter;
    }

    /** Ambil daftar pegawai berdasarkan kategori status (dipakai saat kartu diklik) */
    public function byStatusKategori(string $kategori): array
    {
        $semua = $this->allWithUnit();
        return array_values(array_filter($semua, function ($pegawai) use ($kategori) {
            $sisaHari = hitung_sisa_hari($pegawai['tanggal_kgb_berikutnya']);
            return status_kgb($sisaHari)['kode'] === $kategori;
        }));
    }

    /** Pegawai yang KGB-nya jatuh dalam N hari ke depan (untuk notifikasi & kalender) */
    public function dalamRentangHari(int $hari): array
    {
        return Database::select(
            self::SELECT_JOIN . "
            WHERE p.status_aktif = 1
              AND p.tanggal_kgb_berikutnya BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY p.tanggal_kgb_berikutnya ASC",
            [$hari]
        );
    }

    /** Simpan pegawai baru; tanggal KGB berikutnya dihitung otomatis */
    public function simpanBaru(array $data): int
    {
        $data['tanggal_kgb_berikutnya'] = hitung_tanggal_kgb_berikutnya(
            $data['tmt_kgb_terakhir'],
            (int) ($data['periode_kgb_tahun'] ?? 2)
        );
        return $this->insert($data);
    }

    /** Perbarui data pegawai; tanggal KGB berikutnya dihitung ulang otomatis */
    public function perbarui(int $id, array $data): int
    {
        $data['tanggal_kgb_berikutnya'] = hitung_tanggal_kgb_berikutnya(
            $data['tmt_kgb_terakhir'],
            (int) ($data['periode_kgb_tahun'] ?? 2)
        );
        return $this->update($id, $data);
    }

    public function riwayatKgb(int $pegawaiId): array
    {
        return Database::select(
            'SELECT * FROM riwayat_kgb WHERE pegawai_id = ? ORDER BY tanggal_kgb DESC',
            [$pegawaiId]
        );
    }
}
