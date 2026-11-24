<?php
declare(strict_types=1);

function dal_idsColeccion(int $usuarioId): array
{
    $filas = dal_llamar('sp_coleccion_ids', 'i', [$usuarioId]);

    return array_map(static fn (array $fila): int => (int) $fila['dinosaurio_id'], $filas);
}

function dal_resumenColeccion(int $usuarioId): array
{
    $filas = dal_llamar('sp_coleccion_resumen', 'i', [$usuarioId]);
    $fila = $filas[0] ?? [];

    return [
        'cartas'   => (int) ($fila['total'] ?? 0),
        'especies' => (int) ($fila['especies'] ?? 0),
    ];
}

function dal_ultimasCartas(int $usuarioId, int $limite = 6): array
{
    return dal_llamar('sp_ultimas_cartas', 'ii', [$usuarioId, $limite]);
}
