<?php
/**
 * dal/ColeccionDAL.php
 * ------------------------------------------------------------------
 * Acceso a datos de la tabla `colecciones` (cartas que tiene cada usuario).
 * ------------------------------------------------------------------
 */

/** Añade un dinosaurio a la coleccion de un usuario. */
function anadirCarta(int $usuario_id, int $dinosaurio_id): void
{
    dal_ejecutar(
        "INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
         VALUES (?, ?, NOW())",
        "ii",
        [$usuario_id, $dinosaurio_id]
    );
}

/**
 * Añade varias cartas de golpe dentro de una transaccion: o entran todas
 * o no entra ninguna (evita sobres a medias si algo falla).
 *
 * @param int[] $dinosaurio_ids
 */
function anadirCartas(int $usuario_id, array $dinosaurio_ids): void
{
    $conexion = dal_conexion();
    $conexion->begin_transaction();

    try {
        foreach ($dinosaurio_ids as $dinosaurio_id) {
            anadirCarta($usuario_id, (int) $dinosaurio_id);
        }
        $conexion->commit();
    } catch (Throwable $e) {
        $conexion->rollback();
        throw $e;
    }
}

/**
 * Devuelve la lista de dinosaurio_id que posee un usuario.
 * Se conservan los repetidos.
 *
 * @return int[]
 */
function obtenerIdsColeccion(int $usuario_id): array
{
    $filas = dal_filas("SELECT dinosaurio_id FROM colecciones WHERE usuario_id = ?", "i", [$usuario_id]);

    return array_map(static function (array $fila): int {
        return (int) $fila["dinosaurio_id"];
    }, $filas);
}

/** Resumen de la coleccion: ['cartas' => int, 'especies' => int]. */
function resumenColeccion(int $usuario_id): array
{
    $fila = dal_fila(
        "SELECT COUNT(*) AS total, COUNT(DISTINCT dinosaurio_id) AS especies
         FROM colecciones WHERE usuario_id = ?",
        "i",
        [$usuario_id]
    );

    return [
        'cartas'   => (int) ($fila["total"] ?? 0),
        'especies' => (int) ($fila["especies"] ?? 0),
    ];
}

/** Ultimas cartas obtenidas por un usuario (mas recientes primero). */
function ultimasCartas(int $usuario_id, int $limite = 6): array
{
    $limite = max(1, min(50, $limite));

    return dal_filas(
        "SELECT d.*
         FROM colecciones c
         INNER JOIN dinosaurios d ON d.id = c.dinosaurio_id
         WHERE c.usuario_id = ?
         ORDER BY c.fecha_adquisicion DESC
         LIMIT ?",
        "ii",
        [$usuario_id, $limite]
    );
}
