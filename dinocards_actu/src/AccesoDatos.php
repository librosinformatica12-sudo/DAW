<?php

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

function escapar(mixed $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

function e(mixed $texto): string
{
    return escapar($texto);
}

function esta_logueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

function pedir_login(): void
{
    if (!esta_logueado()) {
        header('Location: index.php');
        exit;
    }
}

require_once __DIR__ . '/datos/Conexion.php';
require_once __DIR__ . '/datos/UsuarioDAL.php';
require_once __DIR__ . '/datos/ColeccionDAL.php';
require_once __DIR__ . '/datos/DinosaurioDAL.php';
require_once __DIR__ . '/datos/SobreDAL.php';
require_once __DIR__ . '/ReglasColeccion.php';
require_once __DIR__ . '/Respuesta.php';
require_once __DIR__ . '/Usuario.php';
require_once __DIR__ . '/Dinosaurio.php';
require_once __DIR__ . '/UsuarioService.php';
require_once __DIR__ . '/ColeccionService.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/SobreService.php';
