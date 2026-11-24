<?php
declare(strict_types=1);

function dal_abrirSobreDia(int $usuarioId): array
{
    $filas = dal_llamar('sp_abrir_sobre_dia', 'i', [$usuarioId]);

    return $filas[0] ?? ['ok' => 0, 'mensaje' => 'No se pudo abrir el sobre.'];
}

function dal_abrirSobrePrueba(int $usuarioId): array
{
    $filas = dal_llamar('sp_abrir_sobre_prueba', 'i', [$usuarioId]);

    return $filas[0] ?? ['ok' => 0, 'mensaje' => 'No se pudo abrir el sobre.'];
}
