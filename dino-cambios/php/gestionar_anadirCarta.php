<?php

require_once __DIR__ . '/../src/AccesoDatos.php';
require_once __DIR__ . '/../src/Respuesta.php';

ejecutarEndpoint(function (): void {
    $usuarioId = idUsuarioSesion();
    $cartaId = (int) ($_POST['dinosaurio_id'] ?? 0);

    if ($cartaId < 1) {
        responder(false, 'Falta el dinosaurio_id.');
    }

    $resultado = PA_AnadirCartaElegida($usuarioId, $cartaId);
    $mensajes = [
        -1 => 'Tu sesión no es válida. Vuelve a iniciar sesión.',
        -2 => 'El dinosaurio indicado no existe.',
    ];

    if ($resultado['codigo'] !== 0) {
        http_response_code($resultado['codigo'] === -1 ? 401 : 200);
        responder(false, $mensajes[$resultado['codigo']] ?? 'No se pudo añadir la carta.');
    }

    $dino = $resultado['datos'][0] ?? null;
    if (!$dino) {
        responder(false, 'No se pudo obtener la carta.');
    }
    $dino['imagen_url'] = imagenCarta($dino);

    responder(true, 'Carta añadida a tu colección.', ['dinosaurio' => $dino]);
});
