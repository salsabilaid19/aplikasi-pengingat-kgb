<?php
/**
 * models/NotifikasiModel.php
 * Notifikasi internal dashboard (lonceng 🔔), terpisah dari email.
 */
class NotifikasiModel extends Model
{
    protected string $table = 'notifikasi';

    public function terbaru(int $limit = 10): array
    {
        return Database::select(
            "SELECT * FROM notifikasi ORDER BY created_at DESC LIMIT {$limit}"
        );
    }

    public function jumlahBelumDibaca(): int
    {
        $row = Database::selectOne(
            'SELECT COUNT(*) AS total FROM notifikasi WHERE sudah_dibaca = 0'
        );
        return (int) ($row['total'] ?? 0);
    }

    public function tandaiSemuaSudahDibaca(): int
    {
        return Database::execute('UPDATE notifikasi SET sudah_dibaca = 1 WHERE sudah_dibaca = 0');
    }

    public function tambah(string $pesan, string $tipe = 'info'): int
    {
        return $this->insert(['pesan' => $pesan, 'tipe' => $tipe]);
    }
}
