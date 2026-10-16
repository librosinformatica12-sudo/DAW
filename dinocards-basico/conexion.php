<?php
/**
 * conexion.php
 * ------------------------------------------------------------------
 * Punto de entrada a la capa de datos: crea la conexión $conexion
 * y define las funciones de ayuda que usan casi todas las paginas.
 *
 * Mismo estilo que AccesoDatos.php de dino-prueba.
 * ------------------------------------------------------------------
 */

// Iniciamos la sesion (solo si no esta iniciada todavia)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$port = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$conexion = @new mysqli($host, $username, $password, $database, $port);

if ($conexion->connect_errno) {
    die("Error de conexion: " . $conexion->connect_error);
}

$conexion->set_charset('utf8mb4');

// Alias con el nombre usado en dino-prueba ($connection)
$connection = $conexion;


// ------------------------------------------------------------
// Funciones de ayuda
// ------------------------------------------------------------

// Limpia un texto antes de mostrarlo en el HTML (evita problemas de seguridad)
function escapar($texto)
{
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
}

// Alias corto de escapar() (lo usan algunas plantillas al estilo dino-prueba)
function e($texto)
{
    return escapar($texto);
}

// Dice si el usuario ha iniciado sesion
function esta_logueado()
{
    return isset($_SESSION["usuario_id"]);
}

// Si no ha iniciado sesion, lo manda a la pagina de entrada
function pedir_login()
{
    if (!esta_logueado()) {
        header("Location: index.php");
        exit;
    }
}

// Añade un dinosaurio a la coleccion de un usuario
function anadir_a_coleccion($conexion, $usuario_id, $dinosaurio_id)
{
    $sentencia = $conexion->prepare(
        "INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
         VALUES (?, ?, NOW())"
    );
    $sentencia->bind_param("ii", $usuario_id, $dinosaurio_id);
    $sentencia->execute();
    $sentencia->close();
}
