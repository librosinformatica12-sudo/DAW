<?php

require_once __DIR__ . '/../src/AccesoDatos.php';
require_once __DIR__ . '/../src/Respuesta.php';

ejecutarEndpoint(function (): void {
    $usuarioId = idUsuarioSesion();
    $resultado = PA_AbrirCartaDiaria($usuarioId);
    $codigo = $resultado['codigo'];

    $mensajes = [
        -1 => 'Tu sesión no es válida. Vuelve a iniciar sesión.',
        -3 => 'No hay dinosaurios disponibles en este momento.',
        -4 => 'Ya has abierto tu sobre hoy. Vuelve mañana por otra carta.',
    ];

    if ($codigo !== 0) {
        http_response_code($codigo === -1 ? 401 : 200);
        responder(false, $mensajes[$codigo] ?? 'No se pudo abrir el sobre.');
    }

    $dino = $resultado['datos'][0] ?? null;
    if (!$dino) {
        responder(false, 'No se pudo obtener la carta.');
    }
    $dino['imagen_url'] = imagenCarta($dino);

    $_SESSION['usuario_datos']['ultima_obtencion'] = date('Y-m-d H:i:s');
    responder(true, '¡Has conseguido una carta nueva!', ['dinosaurio' => $dino]);
});
