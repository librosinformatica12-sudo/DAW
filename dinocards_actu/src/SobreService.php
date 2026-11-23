<?php

const CARTAS_SOBRE_PRUEBA = 4;

function abrirSobreDelDia(int $usuarioId): array
{
    $resultado = dal_abrirSobreDia($usuarioId);
    if (empty($resultado['ok'])) {
        return ['tipo' => 'error', 'mensaje' => (string) ($resultado['mensaje'] ?? 'No se pudo abrir el sobre.')];
    }

    $cartas = ultimasCartas($usuarioId, 1);

    return [
        'tipo'    => 'ok',
        'mensaje' => (string) $resultado['mensaje'],
        'carta'   => $cartas[0] ?? null,
    ];
}

function abrirSobreDePrueba(int $usuarioId): array
{
    $resultado = dal_abrirSobrePrueba($usuarioId);
    if (empty($resultado['ok'])) {
        return ['tipo' => 'error', 'mensaje' => (string) ($resultado['mensaje'] ?? 'No se pudo abrir el sobre.')];
    }

    $cartas = ultimasCartas($usuarioId, CARTAS_SOBRE_PRUEBA);

    return [
        'tipo'    => 'ok',
        'mensaje' => (string) $resultado['mensaje'],
        'cartas'  => $cartas,
    ];
}
