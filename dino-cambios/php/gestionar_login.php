<?php

require_once __DIR__ . '/../src/AccesoDatos.php';
require_once __DIR__ . '/../src/Respuesta.php';

ejecutarEndpoint(function (): void {
    $identificador = trim((string) ($_POST['usuario'] ?? $_POST['email'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? $_POST['password'] ?? '');

    if ($identificador === '' || $contrasena === '') {
        responder(false, 'Introduce tu usuario o email y tu contraseña.');
    }

    $consulta = PA_Login($identificador);
    $fila = $consulta['datos'][0] ?? null;
    if ($consulta['codigo'] !== 0 || !$fila || !verificarContrasena($contrasena, (string) ($fila['password_hash'] ?? ''))) {
        http_response_code(401);
        responder(false, 'Usuario o contraseña incorrectos.');
    }

    if (esHashAntiguo((string) $fila['password_hash'])) {
        $nuevoHash = hashContrasena($contrasena);
        PA_MigrarPassword((int) $fila['id'], $nuevoHash);
        $fila['password_hash'] = $nuevoHash;
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $fila['id'];
    unset($fila['password_hash']);
    $_SESSION['usuario_datos'] = $fila;
    $_SESSION['usuario'] = [
        'id' => (int) $fila['id'],
        'username' => $fila['username'],
        'email' => $fila['email'],
        'fechaHoraUltimaObtencionCarta' => $fila['ultima_obtencion'],
    ];

    responder(true, '¡Bienvenido, ' . $fila['username'] . '!', ['usuario' => $fila]);
});
