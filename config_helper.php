<?php
/**
 * helpers/config_helper.php
 * Helper global config('app.name') untuk mengambil nilai config
 * bertingkat memakai notasi titik, dengan caching sederhana.
 */
function config(string $key, $default = null)
{
    static $cache = [];

    [$file, $rest] = array_pad(explode('.', $key, 2), 2, null);

    if (!isset($cache[$file])) {
        $path = __DIR__ . "/../config/{$file}.php";
        if (!file_exists($path)) {
            return $default;
        }
        $cache[$file] = require $path;
    }

    if ($rest === null) {
        return $cache[$file];
    }

    return $cache[$file][$rest] ?? $default;
}
