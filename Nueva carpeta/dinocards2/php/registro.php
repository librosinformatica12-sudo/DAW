<?php
// php/registro.php

$DEBUG = true;

// 1. Incluimos AccesoDatos.php subiendo un nivel desde la carpeta 'php' para entrar a 'src'
require_once __DIR__ . '/../src/AccesoDatos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar si es una petición AJAX (fetch/axios)
$esAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($esAjax) {
    ob_start();
    header('Content-Type: application/json; charset=utf-8');
}

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    global $esAjax;
    if ($esAjax) {
        if (ob_get_length()) ob_clean();
        echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
        exit;
    }
}

// ---------- Conexión con AccesoDatos.php ----------
$rutaAcceso = __DIR__ . '/../src/AccesoDatos.php';

if (!file_exists($rutaAcceso)) {
    $msg = $DEBUG ? 'No se encuentra AccesoDatos.php en: ' . $rutaAcceso : 'Error interno del servidor.';
    if ($esAjax) {
        responder(false, $msg);
    } else {
        $errorRegistro = $msg;
        return;
    }
}
require_once $rutaAcceso;

// ---------- Datos del formulario ----------
$usuario = trim($_POST['usuario'] ?? '');
$email = trim($_POST['email'] ?? '');
$contrasena = $_POST['contrasena'] ?? '';
$confirma = $_POST['confirmaContrasena'] ?? '';

// ---------- Validaciones en servidor ----------
if (empty($usuario) || empty($email) || empty($contrasena)) {
    $msg = 'Todos los campos son obligatorios.';
    if ($esAjax) responder(false, $msg);
    $errorRegistro = $msg;
    return;
}

if ($contrasena !== $confirma) {
    $msg = 'Las contraseñas no coinciden.';
    if ($esAjax) responder(false, $msg);
    $errorRegistro = $msg;
    return;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $msg = 'El correo electrónico no es válido.';
    if ($esAjax) responder(false, $msg);
    $errorRegistro = $msg;
    return;
}

// ---------- Llamada a PA_Registrar ----------
try {
    $codigo = PA_Registrar($usuario, $email, $contrasena);

    // 0 = Registro correcto
    if ($codigo === 0) {
        // Consultar los datos del usuario recién registrado
        $stmtUser = $connection->prepare("SELECT id, username, email FROM usuarios WHERE email = ? OR username = ? LIMIT 1");
        if ($stmtUser) {
            $stmtUser->bind_param("ss", $email, $usuario);
            $stmtUser->execute();
            $resUser = $stmtUser->get_result();
            if ($userRow = $resUser->fetch_assoc()) {
                $_SESSION['usuario_id'] = $userRow['id'];
                $_SESSION['usuario']    = $userRow['username'];
                $_SESSION['email']      = $userRow['email'];
            }
            $stmtUser->close();
        }

        if ($esAjax) {
            responder(true, 'Registro completado con éxito.', ['usuario' => $usuario]);
        } else {
            // Si la petición viene de un submit normal de formulario
            header('Location: inicio.php');
            exit;
        }
    }

    // Mapeo de respuestas del procedimiento almacenado
    $mensajes = [
        -1 => 'El nombre de usuario o el correo no pueden estar vacíos.',
        -2 => 'El nombre de usuario o el correo electrónico ya están registrados.',
        -3 => 'La contraseña no puede estar vacía.',
        -99 => 'Error inesperado en la base de datos.'
    ];

    $msg = $mensajes[$codigo] ?? "No se pudo completar el registro (código $codigo).";
    if ($esAjax) responder(false, $msg);
    $errorRegistro = $msg;

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    $msg = $DEBUG ? 'DEBUG: ' . $ex->getMessage() : 'No se pudo completar el registro. Inténtalo más tarde.';
    if ($esAjax) responder(false, $msg);
    $errorRegistro = $msg;
}