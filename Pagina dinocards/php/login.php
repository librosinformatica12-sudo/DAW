<?php
// Cambia a false antes de entregar
$DEBUG = true;

ob_start(); // evita que cualquier warning o espacio rompa el JSON
session_start();

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    ob_clean();
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- Conexión con AccesoDatos.php (carpeta src) ----------
$rutaAcceso = __DIR__ . '../src/AccesoDatos.php';

if (!file_exists($rutaAcceso)) {
    responder(false, $DEBUG
        ? 'No se encuentra AccesoDatos.php en: ' . $rutaAcceso
        : 'Error interno del servidor.');
}
require_once $rutaAcceso;

// ---------- Solo POST ----------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, 'Método no permitido.');
}

// ---------- Datos del formulario ----------
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($usuario === '' || $contrasena === '') {
    responder(false, 'Usuario y contraseña son obligatorios.');
}

// ---------- Llamada al procedimiento Login ----------
try {
    $r = PA_Login($usuario, $contrasena);   // ['codigo' => int, 'staff' => array|null]
    $codigo = $r['codigo'];

    if ($codigo > 0) {
        session_regenerate_id(true);
        $_SESSION['staff'] = $r['staff'];
        $_SESSION['staff_id'] = $codigo;
        $_SESSION['login'] = $usuario;

        $nombre = $r['staff']['first_name'] ?? $usuario;
        responder(true, '¡Bienvenido, ' . $nombre . '!', ['staff' => $r['staff']]);

    } elseif ($codigo === -1) {
        responder(false, 'Introduce usuario y contraseña.');
    } elseif ($codigo === -2) {
        responder(false, 'Usuario o contraseña incorrectos.');
    } elseif ($codigo === -3) {
        responder(false, 'Cuenta bloqueada por demasiados intentos fallidos. Inténtalo de nuevo en 5 minutos.');
    } else {
        responder(false, $DEBUG
            ? 'Código devuelto por el procedimiento: ' . $codigo
            : 'Error inesperado. Inténtalo más tarde.');
    }

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG
        ? 'DEBUG: ' . $ex->getMessage()
        : 'Error inesperado. Inténtalo más tarde.');
}