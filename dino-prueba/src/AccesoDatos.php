<?php
/**
 * AccesoDatos.php
 * ------------------------------------------------------------------
 * Punto de entrada a la capa de datos: crea la conexión $connection
 * y carga los servicios de la aplicación.
 *
 *   - UsuarioService.php    -> usuarios (registro, login, datos)
 *   - ColeccionService.php  -> colección y catálogo de dinosaurios
 *   - ReglasColeccion.php   -> reglas y cálculos (sin BD)
 *
 * Cualquier archivo que haga require de este ya tiene acceso a todo.
 * ------------------------------------------------------------------
 */

$host = '127.0.0.1';
$port = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

if (!$connection->connect_errno) {
    $connection->set_charset('utf8mb4');
}

require_once __DIR__ . '/ReglasColeccion.php';
require_once __DIR__ . '/UsuarioService.php';
require_once __DIR__ . '/ColeccionService.php';
