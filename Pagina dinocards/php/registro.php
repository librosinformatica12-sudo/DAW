<?php
// Cambia a false antes de entregar
$DEBUG = true;

ob_start(); // evita que cualquier warning o espacio rompa el JSON

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    ob_clean();
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- Conexión con AccesoDatos.php (carpeta src) ----------
$rutaAcceso = __DIR__ . '/src/AccesoDatos.php';

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
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$usuario = trim($_POST['usuario'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirma = $_POST['confirmaContrasena'] ?? '';

// ---------- Validaciones de servidor ----------
if ($contrasena !== $confirma) {
    responder(false, 'Las contraseñas no coinciden.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'El email no es válido.');
}

// ---------- Llamada al procedimiento Registro ----------
try {
    $codigo = PA_Registrar($nombre, $apellido, $email, $usuario, $contrasena);

    if ($codigo > 0) {
        responder(true, 'Registro correcto. ID de empleado: ' . $codigo, ['staff_id' => $codigo]);
    }

    $mensajes = [
        -1  => 'El usuario no puede estar vacío.',
        -2  => 'El usuario ya existe. Elige otro nombre de usuario.',
        -3  => 'La contraseña no puede estar vacía.',
        -4  => 'El email ya está registrado.',
        -5  => 'Nombre, apellido y email son obligatorios.',
        -99 => 'Error inesperado en la base de datos.',
    ];
    responder(false, $mensajes[$codigo] ?? "No se pudo completar el registro (código $codigo).");

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG
        ? 'DEBUG: ' . $ex->getMessage()
        : 'No se pudo completar el registro. Inténtalo más tarde.');
}