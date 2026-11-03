<?php
/**
 * config/bootstrap.php
 * Titik masuk inisialisasi aplikasi: dipanggil sekali dari public/index.php.
 */

error_reporting(E_ALL);

date_default_timezone_set('Asia/Jakarta');

session_start();

// Muat helper config() dan env() dulu sebelum dipakai di mana pun
require __DIR__ . '/../helpers/config_helper.php';
require __DIR__ . '/env.php';

// Muat .env
load_env(__DIR__ . '/../.env');

ini_set('display_errors', config('app.env') === 'local' ? '1' : '0');
date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));

require __DIR__ . '/../helpers/kgb_helper.php';

// Autoload sederhana untuk core, models, controllers (tanpa composer)
spl_autoload_register(function (string $class) {
    $dirs = [
        __DIR__ . '/../core/',
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../services/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require $file;
            return;
        }
    }
});
