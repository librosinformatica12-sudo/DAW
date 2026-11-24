<?php
declare(strict_types=1);

function dal_contarDinosaurios(): int
{
    $filas = dal_llamar('sp_dino_contar');

    return (int) ($filas[0]['total'] ?? 0);
}

function dal_todosLosDinosaurios(): array
{
    return dal_llamar('sp_dino_todos');
}

function dal_dinosaurioAleatorio(): ?array
{
    $filas = dal_llamar('sp_dino_aleatorio');

    return $filas[0] ?? null;
}

function dal_dinosauriosAleatorios(int $cantidad): array
{
    $cantidad = max(1, min(50, $cantidad));

    return dal_llamar('sp_dino_aleatorios_n', 'i', [$cantidad]);
}

function dal_dinosauriosPorIds(array $ids): array
{
    $ids = array_values(array_unique(array_filter(
        array_map('intval', $ids),
        static fn (int $id): bool => $id > 0
    )));
    if ($ids === []) {
        return [];
    }

    $filas = dal_llamar('sp_dino_por_ids', 's', [implode(',', $ids)]);
    $mapa = [];
    foreach ($filas as $fila) {
        $mapa[(int) $fila['id']] = $fila;
    }

    return $mapa;
}
