<?php
/**
 * models/EmailLogModel.php
 * Model untuk tabel email_log: pencatatan setiap percobaan pengiriman email.
 */
class EmailLogModel extends Model
{
    protected string $table = 'email_log';

    /** Log terbaru dulu, dengan filter opsional status & rentang tanggal */
    public function filter(?string $status = null, ?string $dari = null, ?string $sampai = null): array
    {
        $sql = 'SELECT * FROM email_log WHERE 1=1';
        $params = [];

        if (!empty($status)) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }
        if (!empty($dari)) {
            $sql .= ' AND DATE(created_at) >= ?';
            $params[] = $dari;
        }
        if (!empty($sampai)) {
            $sql .= ' AND DATE(created_at) <= ?';
            $params[] = $sampai;
        }
        $sql .= ' ORDER BY created_at DESC';

        return Database::select($sql, $params);
    }

    public function riwayatUntukPegawai(int $pegawaiId): array
    {
        return Database::select(
            'SELECT * FROM email_log WHERE pegawai_id = ? ORDER BY created_at DESC',
            [$pegawaiId]
        );
    }

    public function statusTerbaruUntukPegawai(int $pegawaiId): string
    {
        $row = Database::selectOne(
            'SELECT status FROM email_log WHERE pegawai_id = ? ORDER BY created_at DESC LIMIT 1',
            [$pegawaiId]
        );
        return $row['status'] ?? 'Belum Dikirim';
    }

    /**
     * Cek apakah pengingat untuk siklus KGB saat ini (tanggal_kgb tertentu) sudah
     * pernah berhasil terkirim. Mencegah pengiriman berulang setiap hari selama 30 hari.
     */
    public function sudahDikirimUntukSiklus(int $pegawaiId, string $tanggalKgb): bool
    {
        $row = Database::selectOne(
            "SELECT id FROM email_log WHERE pegawai_id = ? AND tanggal_kgb = ? AND status = 'Sudah Dikirim' LIMIT 1",
            [$pegawaiId, $tanggalKgb]
        );
        return $row !== null;
    }

    public function catatSukses(int $pegawaiId, string $nama, string $email, string $tanggalKgb): int
    {
        return $this->insert([
            'pegawai_id'       => $pegawaiId,
            'nama_pegawai'     => $nama,
            'email_tujuan'     => $email,
            'tanggal_kgb'      => $tanggalKgb,
            'status'           => 'Sudah Dikirim',
            'waktu_pengiriman' => date('Y-m-d H:i:s'),
        ]);
    }

    public function catatGagal(int $pegawaiId, string $nama, string $email, string $tanggalKgb, string $errorMessage): int
    {
        return $this->insert([
            'pegawai_id'       => $pegawaiId,
            'nama_pegawai'     => $nama,
            'email_tujuan'     => $email,
            'tanggal_kgb'      => $tanggalKgb,
            'status'           => 'Gagal',
            'error_message'    => $errorMessage,
            'waktu_pengiriman' => date('Y-m-d H:i:s'),
        ]);
    }

    public function hitungGagalHariIni(): int
    {
        $row = Database::selectOne(
            "SELECT COUNT(*) AS total FROM email_log WHERE status = 'Gagal' AND DATE(created_at) = CURDATE()"
        );
        return (int) ($row['total'] ?? 0);
    }
}
