<?php
/**
 * services/BackupService.php
 * Backup & restore database murni PHP (PDO), tanpa bergantung pada
 * shell_exec/mysqldump, karena banyak hosting shared menonaktifkan fungsi exec.
 */
class BackupService
{
    private string $backupDir;

    public function __construct()
    {
        $this->backupDir = __DIR__ . '/../storage/backups';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /** Buat file backup .sql baru, kembalikan nama filenya */
    public function buatBackup(): string
    {
        $pdo = Database::connection();
        $dbName = config('database.name');
        $filename = 'backup_' . $dbName . '_' . date('Ymd_His') . '.sql';
        $path = $this->backupDir . '/' . $filename;

        $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        $sql = "-- Backup database `{$dbName}`\n-- Dibuat otomatis pada " . date('Y-m-d H:i:s') . "\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // Struktur tabel
            $createStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createStmt['Create Table'] . ";\n\n";

            // Isi data
            $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $columns = array_map(fn($c) => "`{$c}`", array_keys($row));
                $values = array_map(function ($v) use ($pdo) {
                    return $v === null ? 'NULL' : $pdo->quote((string) $v);
                }, array_values($row));

                $sql .= "INSERT INTO `{$table}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
            }
            $sql .= "\n";
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        file_put_contents($path, $sql);
        return $filename;
    }

    /** Daftar file backup yang tersedia, terbaru dulu */
    public function daftarBackup(): array
    {
        $files = glob($this->backupDir . '/*.sql') ?: [];
        usort($files, fn($a, $b) => filemtime($b) - filemtime($a));

        return array_map(fn($f) => [
            'nama'    => basename($f),
            'ukuran'  => round(filesize($f) / 1024, 1) . ' KB',
            'tanggal' => date('Y-m-d H:i:s', filemtime($f)),
        ], $files);
    }

    public function pathBackup(string $filename): ?string
    {
        // Cegah path traversal: hanya izinkan nama file yang aman
        $safe = basename($filename);
        $path = $this->backupDir . '/' . $safe;
        return file_exists($path) ? $path : null;
    }

    /**
     * Restore database dari isi file .sql (mis. hasil upload).
     * Statement dipisah berdasarkan titik koma di akhir baris.
     *
     * Catatan: TIDAK dibungkus dalam transaction karena statement DDL
     * (CREATE/DROP TABLE) di MySQL otomatis melakukan implicit COMMIT,
     * yang akan merusak transaction manapun yang sedang berjalan.
     */
    public function restoreDariIsi(string $sqlContent): void
    {
        $pdo = Database::connection();

        // Buang seluruh baris komentar (--) dulu, baru pisahkan per statement.
        // Jika tidak, statement asli bisa "tertelan" karena menyatu dengan baris komentar di depannya.
        $bersih = preg_replace('/^--.*$/m', '', $sqlContent);

        $statements = array_filter(array_map('trim', explode(";\n", $bersih)));

        $nomor = 0;
        foreach ($statements as $statement) {
            $nomor++;
            if ($statement === '') {
                continue;
            }
            try {
                $pdo->exec($statement);
            } catch (PDOException $e) {
                $cuplikan = substr($statement, 0, 80);
                throw new RuntimeException("Gagal pada statement #{$nomor} (\"{$cuplikan}...\"): " . $e->getMessage());
            }
        }
    }
}
