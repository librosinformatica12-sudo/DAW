<?php

require_once __DIR__ . '/../src/AccesoDatos.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, 'Metodo no permitido.');
}

$resultado = registrarUsuario(
    $_POST['usuario'] ?? '',
    $_POST['email'] ?? '',
    $_POST['contrasena'] ?? '',
    $_POST['confirma'] ?? ''
);

responder($resultado['ok'], $resultado['mensaje']);
