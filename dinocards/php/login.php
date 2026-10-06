<?php
// Cambia a false antes de entregar
$DEBUG = true;

ob_start(); // evita que cualquier warning o espacio rompa el JSON
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    if (ob_get_length()) ob_clean();
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------- Conexión con AccesoDatos.php (carpeta src) ----------
$rutaAcceso = __DIR__ . '/../src/AccesoDatos.php';

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
// Nota: Tu procedimiento de MySQL busca por EMAIL
$email = trim($_POST['usuario'] ?? $_POST['email'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';

if ($email === '' || $contrasena === '') {
    responder(false, 'Correo y contraseña son obligatorios.');
}

// ---------- Llamada al procedimiento Login ----------
try {
    $r = PA_Login($email, $contrasena);   // Devuelve ['codigo' => int, 'usuario' => array|null]
    $codigo = $r['codigo'];
    $datosUsuario = $r['usuario'];

    // En tu PA de MySQL: 0 = Éxito total
    if ($codigo === 0 && $datosUsuario !== null) {
        session_regenerate_id(true);

        // Guardar sesión de DinoCards
        $_SESSION['usuario_id'] = $datosUsuario['id'] ?? null;
        $_SESSION['usuario']    = $datosUsuario['username'] ?? $email;
        $_SESSION['email']      = $datosUsuario['email'] ?? $email;

        $nombreMostrar = $datosUsuario['username'] ?? $email;
        responder(true, '¡Bienvenido, ' . $nombreMostrar . '!', ['usuario' => $datosUsuario]);

    } elseif ($codigo === -1) {
        responder(false, 'El correo electrónico no puede estar vacío.');
    } elseif ($codigo === -2) {
        responder(false, 'La contraseña no puede estar vacía.');
    } elseif ($codigo === -3) {
        responder(false, 'El correo o la contraseña son incorrectos.');
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