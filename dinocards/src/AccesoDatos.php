<?php
/**
 * AccesoDatos.php
 * ------------------------------------------------------------------
 * Punto de entrada a la capa de datos de DinoCards.
 *
 * Se encarga de:
 *   1. Iniciar la sesion (si no lo esta ya).
 *   2. Crear la conexion a MySQL en la variable $conexion
 *      (con alias $connection, igual que en dino-prueba).
 *   3. Definir las funciones de ayuda que usan casi todas las paginas.
 *
 * Cualquier archivo que haga require de este archivo ya tiene
 * acceso a la conexion y a todas las funciones de ayuda.
 *
 * Equivale a src/AccesoDatos.php del proyecto dino-prueba.
 * ------------------------------------------------------------------
 */

// ------------------------------------------------------------
// 1. SESION
// ------------------------------------------------------------
// La abrimos aqui una sola vez para no repetir session_start()
// en cada pagina.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ------------------------------------------------------------
// 2. CONEXION A LA BASE DE DATOS
// ------------------------------------------------------------
$host     = '127.0.0.1';
$port     = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '1234';

// Desactivamos las excepciones automaticas de mysqli para poder
// controlar los errores a mano (mismo criterio que dino-prueba).
mysqli_report(MYSQLI_REPORT_OFF);

$conexion = @new mysqli($host, $username, $password, $database, $port);

if ($conexion->connect_errno) {
    die("Error de conexion: " . $conexion->connect_error);
}

// Aseguramos acentos y caracteres especiales (tildes, ñ...).
$conexion->set_charset('utf8mb4');

// Alias con el nombre que usa dino-prueba ($connection).
$connection = $conexion;


// ------------------------------------------------------------
// 3. FUNCIONES DE AYUDA
// ------------------------------------------------------------

/**
 * Limpia un texto antes de mostrarlo en el HTML.
 * Evita problemas de seguridad (XSS).
 */
function escapar($texto)
{
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}

/**
 * Alias corto de escapar() (lo usan algunas plantillas
 * al estilo dino-prueba).
 */
function e($texto)
{
    return escapar($texto);
}

/**
 * Devuelve true si el usuario ha iniciado sesion.
 */
function esta_logueado()
{
    return isset($_SESSION["usuario_id"]);
}

/**
 * Si el usuario no ha iniciado sesion, lo manda a la pagina
 * de entrada (index.php) y detiene la ejecucion.
 */
function pedir_login()
{
    if (!esta_logueado()) {
        header("Location: index.php");
        exit;
    }
}

// ------------------------------------------------------------
// 4. SERVICIOS
// ------------------------------------------------------------
// Se cargan aqui para que cualquier archivo que haga require de
// AccesoDatos tenga ya disponibles todas las funciones de datos.
// (Mismo patron que AccesoDatos.php de dino-prueba.)
require_once __DIR__ . '/ReglasColeccion.php';
require_once __DIR__ . '/UsuarioService.php';
require_once __DIR__ . '/ColeccionService.php';
