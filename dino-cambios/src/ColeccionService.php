<?php

function imagenCarta(array $fila): ?string
{
    $url = $fila['image_url'] ?? null;
    if (is_string($url) && trim($url) !== '') {
        return $url;
    }

    $id = (int) ($fila['id'] ?? 0);
    if ($id < 1) {
        return null;
    }

    foreach (['png', 'jfif', 'jpg', 'webp'] as $extension) {
        $ruta = 'img/img-cartas/' . $id . '.' . $extension;
        if (is_file(__DIR__ . '/../' . $ruta)) {
            return $ruta;
        }
    }

    return null;
}

function resumenColeccion(int $usuarioId): array
{
    $resultado = PA_ObtenerResumenColeccion($usuarioId);
    $fila = $resultado['datos'][0] ?? [];

    return [
        'cartas' => (int) ($fila['cartas'] ?? 0),
        'especies' => (int) ($fila['especies'] ?? 0),
    ];
}

function ultimasCartas(int $usuarioId, int $limite = 6): array
{
    return PA_ObtenerUltimasCartas($usuarioId, $limite)['datos'];
}

function dinosauriosPorIds(array $ids): array
{
    $ids = array_values(array_unique(array_filter($ids, 'is_numeric')));
    if (!$ids) {
        return [];
    }

    $lista = implode(',', array_map('intval', $ids));
    $resultado = PA_ObtenerDinosauriosPorIds($lista);

    $mapa = [];
    foreach ($resultado['datos'] as $fila) {
        $mapa[(int) $fila['id']] = $fila;
    }

    return $mapa;
}

function contarDinosaurios(): int
{
    $resultado = PA_ContarDinosaurios();
    $fila = $resultado['datos'][0] ?? [];

    return (int) ($fila['total'] ?? 0);
}
