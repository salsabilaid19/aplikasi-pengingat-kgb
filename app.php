<?php
/**
 * app.php
 * Konfigurasi umum aplikasi. Dibaca setelah .env dimuat.
 */
return [
    'name'        => env('APP_NAME', 'Aplikasi Pengingat KGB'),
    'url'         => rtrim(env('APP_URL', 'http://localhost'), '/'),
    'env'         => env('APP_ENV', 'production'),
    'key'         => env('APP_KEY'),
    'timezone'    => 'Asia/Jakarta',

    'kgb_threshold' => [
        'kuning' => (int) env('KGB_THRESHOLD_KUNING', 30),
        'orange' => (int) env('KGB_THRESHOLD_ORANGE', 14),
        'merah'  => (int) env('KGB_THRESHOLD_MERAH', 7),
    ],
];
