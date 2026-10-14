<?php
/* ============================================================
   conexion.php
   ------------------------------------------------------------------
   Aqui conectamos con la base de datos y escribimos algunas
   funciones que usan casi todas las paginas.
   ============================================================ */

// Iniciamos la sesion (solo si no esta iniciada todavia)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datos de la base de datos (XAMPP)
$host       = "127.0.0.1";
$usuario_bd = "oscar";
$clave_bd   = "561Cazadora#%";
$base_datos = "dinocards";

// Creamos la conexion con MySQLi
$conexion = new mysqli($host, $usuario_bd, $clave_bd, $base_datos);

// Si falla, mostramos un error y paramos
if ($conexion->connect_error) {
    die("Error de conexion: " . $conexion->connect_error);
}

// Para que los acentos y las tildes se vean bien
$conexion->set_charset("utf8mb4");


// ------------------------------------------------------------
// Funciones de ayuda
// ------------------------------------------------------------

// Limpia un texto antes de mostrarlo en el HTML (evita problemas de seguridad)
function escapar($texto)
{
    return htmlspecialchars($texto ?? "", ENT_QUOTES, "UTF-8");
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
?>
