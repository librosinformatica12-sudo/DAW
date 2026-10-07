<?php
$DEBUG = true;

ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    ob_clean();
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$rutaAcceso = __DIR__ . '/../src/AccesoDatos.php';

if (!file_exists($rutaAcceso)) {
    responder(false, $DEBUG ? 'No se encuentra AccesoDatos.php en: ' . $rutaAcceso : 'Error interno del servidor.');
}
require_once $rutaAcceso;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, 'Método no permitido.');
}

$email = trim($_POST['usuario'] ?? ''); // Se envía como email para el procedimiento
$contrasena = $_POST['contrasena'] ?? '';

if ($email === '' || $contrasena === '') {
    responder(false, 'Email y contraseña son obligatorios.');
}

try {
    $r = PA_Login($email, $contrasena);
    $codigo = $r['codigo'];

    // Tu procedimiento almacena 0 en _res cuando el login es correcto
    if ($codigo === 0) {
        session_regenerate_id(true);
        $_SESSION['usuario_datos'] = $r['usuario'];
        $_SESSION['usuario_id'] = $r['usuario']['id'] ?? $email;

        $nombre = $r['usuario']['username'] ?? $email;
        responder(true, '¡Bienvenido, ' . $nombre . '!', ['usuario' => $r['usuario']]);

    } elseif ($codigo === -1) {
        responder(false, 'El campo de correo electrónico no puede estar vacío.');
    } elseif ($codigo === -2) {
        responder(false, 'La contraseña no puede estar vacía.');
    } elseif ($codigo === -3) {
        responder(false, 'Email o contraseña incorrectos.');
    } else {
        responder(false, $DEBUG ? 'Código devuelto por el procedimiento: ' . $codigo : 'Error inesperado. Inténtalo más tarde.');
    }

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG ? 'DEBUG: ' . $ex->getMessage() : 'Error inesperado. Inténtalo más tarde.');
}