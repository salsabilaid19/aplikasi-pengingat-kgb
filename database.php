<?php
/**
 * database.php
 * Konfigurasi koneksi database (dibaca oleh core/Database.php)
 */
return [
    'host'    => env('DB_HOST', '127.0.0.1'),
    'port'    => env('DB_PORT', '3306'),
    'name'    => env('DB_NAME', 'kgb_app'),
    'user'    => env('DB_USER', 'root'),
    'pass'    => env('DB_PASS', ''),
    'charset' => 'utf8mb4',
];
