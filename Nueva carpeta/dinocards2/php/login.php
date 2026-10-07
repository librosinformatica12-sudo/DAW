<?php
// php/login.php

// 1. Incluimos AccesoDatos.php subiendo un nivel desde la carpeta 'php' para entrar a 'src'
require_once __DIR__ . '/../src/AccesoDatos.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
}