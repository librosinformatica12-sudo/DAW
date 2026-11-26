<?php

require_once __DIR__ . '/../src/AccesoDatos.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, 'Metodo no permitido.');
}

$usuario = trim($_POST['usuario'] ?? '');
$clave   = $_POST['contrasena'] ?? '';

if ($usuario === '' || $clave === '') {
    responder(false, 'Escribe tu usuario o correo y tu contraseña.');
}

$credenciales = autenticarUsuario($usuario, $clave);
if ($credenciales === null) {
    responder(false, 'Usuario o contraseña incorrectos.');
}

session_regenerate_id(true);
$_SESSION['usuario_id'] = (int) $credenciales['id'];
$_SESSION['usuario']    = $credenciales['username'];

responder(true, '¡Bienvenido, ' . $credenciales['username'] . '!');
