<?php

require_once __DIR__ . '/../src/AccesoDatos.php';
require_once __DIR__ . '/../src/Respuesta.php';

ejecutarEndpoint(function (): void {
    $espera = 120;
    $cuantas = 4;
    $usuarioId = idUsuarioSesion();

    $ultima = $_SESSION['carta_prueba_local'] ?? null;
    if ($ultima !== null) {
        $faltan = $espera - (time() - strtotime((string) $ultima));
        if ($faltan > 0) {
            responder(false, 'Aún no puedes abrirlo. Vuelve en unos minutos.', ['espera' => $faltan]);
        }
    }

    $resultado = PA_AnadirCartasPrueba($usuarioId, $cuantas);
    $mensajes = [
        -1 => 'Tu sesión no es válida. Vuelve a iniciar sesión.',
        -2 => 'El número de cartas no es válido.',
        -3 => 'No hay dinosaurios suficientes en el catálogo.',
    ];

    if ($resultado['codigo'] !== 0) {
        http_response_code($resultado['codigo'] === -1 ? 401 : 200);
        responder(false, $mensajes[$resultado['codigo']] ?? 'No se pudo abrir el sobre.');
    }

    $dinos = $resultado['datos'];
    foreach ($dinos as $indice => $d) {
        $dinos[$indice]['imagen_url'] = imagenCarta($d);
    }

    $_SESSION['carta_prueba_local'] = date('Y-m-d H:i:s');
    responder(true, '¡Has conseguido ' . count($dinos) . ' cartas nuevas!', [
        'dinosaurios' => $dinos,
        'espera' => $espera,
    ]);
});
