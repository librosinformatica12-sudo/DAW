<?php
/**
 * ReglasColeccion.php
 * ------------------------------------------------------------------
 * Reglas y cálculos de la colección. NO accede a la base de datos:
 * son funciones puras que se pueden probar por separado.
 * ------------------------------------------------------------------
 */

/** ¿El usuario tiene más de una copia de una especie? */
function esRepetida(int $veces): bool
{
    return $veces > 1;
}

/**
 * Agrupa una lista de dinosaurio_id contando cuántas veces aparece cada uno.
 * Devuelve ['conteo' => [id => veces], 'orden' => [ids en orden de aparición]].
 */
function agruparColeccion(array $ids): array
{
    $conteo = [];
    $orden  = [];

    foreach ($ids as $id) {
        $id = (int) $id;
        if (!isset($conteo[$id])) {
            $conteo[$id] = 0;
            $orden[]     = $id;
        }
        $conteo[$id]++;
    }

    return ['conteo' => $conteo, 'orden' => $orden];
}

/**
 * Calcula el progreso de la colección.
 * Devuelve ['especies' => int, 'total' => int, 'faltan' => int, 'porcentaje' => int].
 */
function calcularProgreso(int $especies, int $total): array
{
    $porcentaje = $total > 0 ? (int) round($especies / $total * 100) : 0;

    return [
        'especies'   => $especies,
        'total'      => $total,
        'faltan'     => max(0, $total - $especies),
        'porcentaje' => $porcentaje,
    ];
}
