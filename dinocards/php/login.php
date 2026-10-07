<?php
<<<<<<< HEAD
// Cambia a false antes de entregar
$DEBUG = true;

ob_start(); // evita que cualquier warning o espacio rompa el JSON
=======
// php/login.php

// 1. Incluimos AccesoDatos.php subiendo un nivel desde la carpeta 'php' para entrar a 'src'
require_once __DIR__ . '/../src/AccesoDatos.php';

>>>>>>> 88528de (clase mal)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

<<<<<<< HEAD
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
=======
// Función auxiliar para responder siempre en formato JSON
if (!function_exists('responder')) {
    function responder(bool $ok, string $mensaje, array $datos = []): void {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'ok' => $ok,
            'mensaje' => $mensaje
        ], $datos));
        exit;
    }
}

$DEBUG = $DEBUG ?? true; // Cambiar a false en entorno de producción

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Soporta datos recibidos desde distintos nombres de input
    $usuario = trim($_POST['usuario'] ?? $_POST['log-usuario'] ?? '');
    $contrasena = $_POST['contrasena'] ?? $_POST['log-pass'] ?? '';

    try {
        if (empty($usuario) || empty($contrasena)) {
            responder(false, 'Por favor, introduce el usuario y la contraseña.');
        }

        if (!function_exists('PA_Login')) {
            throw new Exception('La función PA_Login no está definida dentro de AccesoDatos.php.');
        }

        $r = PA_Login($usuario, $contrasena);
        $codigo = $r['codigo'];

        // En MySQL, 0 (o un ID > 0) representa un login exitoso
        if ($codigo >= 0) {
            session_regenerate_id(true);

            // Obtenemos los datos devueltos por la BD o usamos los valores por defecto
            $idUsuario = $r['staff']['id'] ?? ($codigo > 0 ? $codigo : 1);
            $nombreUsuario = $r['staff']['username'] ?? $r['staff']['first_name'] ?? $usuario;

            // Claves de sesión exactas que busca inicio.php
            $_SESSION['usuario_id'] = $idUsuario;
            $_SESSION['username']   = $nombreUsuario;
            $_SESSION['usuario']    = $nombreUsuario;

            responder(true, '¡Bienvenido, ' . $nombreUsuario . '!', [
                'redirect' => 'inicio.php',
                'staff'    => $r['staff'] ?? null
            ]);

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
        responder(false, $DEBUG 
            ? 'Excepción: ' . $ex->getMessage() 
            : 'Ocurrió un error en el sistema. Inténtalo más tarde.');
    }
} else {
    responder(false, 'Método no permitido.');
>>>>>>> 88528de (clase mal)
}