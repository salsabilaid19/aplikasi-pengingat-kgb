<?php
/**
 * core/Database.php
 * Wrapper PDO sederhana dengan pola singleton, plus helper query
 * agar Model tidak perlu menulis boilerplate PDO berulang-ulang.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset={$config['charset']}";

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // Jangan tampilkan detail koneksi ke user di production
                error_log('DB Connection Error: ' . $e->getMessage());
                http_response_code(500);
                die('Koneksi database gagal. Periksa konfigurasi .env Anda.');
            }
        }

        return self::$instance;
    }

    /** Jalankan query SELECT, kembalikan seluruh baris */
    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Jalankan query SELECT, kembalikan satu baris (atau null) */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row === false ? null : $row;
    }

    /** Jalankan INSERT/UPDATE/DELETE, kembalikan jumlah baris terdampak */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** Ambil last insert id */
    public static function lastInsertId(): string
    {
        return self::connection()->lastInsertId();
    }
}
