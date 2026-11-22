<?php
/**
 * dal/DinosaurioDAL.php
 * ------------------------------------------------------------------
 * Acceso a datos de la tabla `dinosaurios` (catalogo).
 * ------------------------------------------------------------------
 */

/** Numero total de dinosaurios del catalogo. */
function contarDinosaurios(): int
{
    $fila = dal_fila("SELECT COUNT(*) AS total FROM dinosaurios");

    return (int) ($fila["total"] ?? 0);
}

/** Todo el catalogo, ordenado por id (lo usa la DinoPedia). */
function obtenerTodosLosDinosaurios(): array
{
    return dal_filas("SELECT * FROM dinosaurios ORDER BY id");
}

/** Devuelve un dinosaurio al azar, o null si no hay ninguno. */
function dinosaurioAleatorio(): ?array
{
    return dal_fila("SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 1");
}

/** Devuelve N dinosaurios al azar (sin repetir), o [] si no hay ninguno. */
function dinosauriosAleatorios(int $n): array
{
    $n = max(1, min(50, $n));

    return dal_filas("SELECT * FROM dinosaurios ORDER BY RAND() LIMIT ?", "i", [$n]);
}

/**
 * Devuelve los dinosaurios indicados indexados por su id:
 *   [ id => [fila de dinosaurios] ]
 *
 * @param array $ids Lista de ids (se normalizan a enteros sin repetir).
 */
function dinosauriosPorIds(array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', array_filter($ids, 'is_numeric'))));
    if (!$ids) {
        return [];
    }

    $marcadores = implode(",", array_fill(0, count($ids), "?"));
    $filas = dal_filas(
        "SELECT * FROM dinosaurios WHERE id IN ($marcadores)",
        str_repeat("i", count($ids)),
        $ids
    );

    $mapa = [];
    foreach ($filas as $fila) {
        $mapa[(int) $fila["id"]] = $fila;
    }

    return $mapa;
}
