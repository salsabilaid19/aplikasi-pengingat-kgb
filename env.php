<?php
/**
 * env.php
 * Loader sederhana untuk file .env tanpa dependency composer.
 * Membaca file .env di root project dan menaruh isinya ke $_ENV / getenv().
 */

function load_env(string $path): void
{
    if (!file_exists($path)) {
        // Jika .env belum ada, hentikan dengan pesan yang jelas (bukan error PHP mentah)
        http_response_code(500);
        die('File .env tidak ditemukan. Salin .env.example menjadi .env lalu sesuaikan isinya.');
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        // Hilangkan tanda kutip pembungkus jika ada
        $value = trim($value, "\"'");

        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Helper untuk mengambil nilai env dengan default value.
 */
function env(string $key, $default = null)
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return $default;
    }
    return $value;
}
