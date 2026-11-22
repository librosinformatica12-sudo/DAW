<?php
/**
 * config/database.php
 * ------------------------------------------------------------------
 * Parametros de conexion a MySQL. Es el UNICO sitio donde viven.
 *
 * Orden de prioridad (el ultimo gana):
 *   1. Valores por defecto de este archivo (entorno de desarrollo).
 *   2. Variables de entorno DINOCARDS_DB_HOST / _PORT / _NAME / _USER / _PASS.
 *   3. config/database.local.php (NO se sube a git; ver .gitignore).
 *      Copia database.local.php.example y pon ahi tus credenciales reales.
 *
 * Devuelve un array. Solo lo lee dal/conexion.php.
 * ------------------------------------------------------------------
 */

$env = static function (string $nombre, $defecto) {
    $valor = getenv($nombre);
    return ($valor === false || $valor === '') ? $defecto : $valor;
};

$config = [
    'host'     => $env('DINOCARDS_DB_HOST', '127.0.0.1'),
    'port'     => (int) $env('DINOCARDS_DB_PORT', 3306),
    'database' => $env('DINOCARDS_DB_NAME', 'dinocards'),
    'username' => $env('DINOCARDS_DB_USER', 'oscar'),
    'password' => $env('DINOCARDS_DB_PASS', '1234'),
    'charset'  => 'utf8mb4',
];

$local = __DIR__ . '/database.local.php';
if (is_file($local)) {
    $config = array_merge($config, (array) require $local);
}

return $config;
