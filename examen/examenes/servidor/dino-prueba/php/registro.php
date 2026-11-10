<?php
$DEBUG = true;

ob_start();

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

$usuario = trim($_POST['usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirma = $_POST['confirmaContrasena'] ?? '';

if ($usuario === '' || $email === '' || $contrasena === '') {
    responder(false, 'Todos los campos son obligatorios.');
}
if ($contrasena !== $confirma) {
    responder(false, 'Las contraseñas no coinciden.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'El email no es válido.');
}

try {
    $codigo = PA_Registrar($usuario, $email, $contrasena);

    // Tu procedimiento almacena 0 cuando todo sale OK
    if ($codigo === 0) {
        responder(true, 'Registro completado con éxito. Ya puedes iniciar sesión.');
    }

    $mensajes = [
        -1  => 'El usuario o email no pueden estar vacíos.',
        -2  => 'El usuario o el correo electrónico ya están registrados.',
        -3  => 'La contraseña no puede estar vacía.'
    ];
    responder(false, $mensajes[$codigo] ?? "No se pudo completar el registro (código $codigo).");

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG ? 'DEBUG: ' . $ex->getMessage() : 'No se pudo completar el registro. Inténtalo más tarde.');
}