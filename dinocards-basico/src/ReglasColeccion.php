<?php
/**
 * ReglasColeccion.php
 * ------------------------------------------------------------------
 * Reglas y calculos de la coleccion. NO accede a la base de datos:
 * son funciones puras que se pueden probar por separado.
 *
 * Equivale (adaptado) a src/ReglasColeccion.php de dino-prueba.
 * ------------------------------------------------------------------
 */

/**
 * Segundos que faltan para poder repetir una accion con espera.
 * Si la fecha no existe, devuelve 0 (no hay espera).
 */
function segundosRestantes(?string $fecha, int $espera): int
{
    if ($fecha === null || $fecha === "") {
        return 0;
    }

    return max(0, $espera - (time() - strtotime($fecha)));
}

/**
 * Agrupa una lista de dinosaurio_id contando cuantas veces aparece cada uno.
 * Devuelve ['conteo' => [id => veces], 'orden' => [ids en orden de aparicion]].
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

/** ¿El usuario tiene mas de una copia de una especie? */
function esRepetida(int $veces): bool
{
    return $veces > 1;
}

/**
 * Calcula el progreso de la coleccion.
 * Devuelve ['especies' => int, 'total' => int, 'faltan' => int, 'porcentaje' => int].
 */
function calcularProgreso(int $especies, int $total): array
{
    $porcentaje = $total > 0 ? (int) round($especies / $total * 100) : 0;

    return [
        'especies'   => $especies,
        'total'      => $total,
        'faltan'     => max(0, $total - $especies),
        'porcentaje' => min(100, $porcentaje),
    ];
}

/** Nivel de la barra de progreso segun el porcentaje (para perfil.php). */
function calcularNivel(int $porcentaje): string
{
    if ($porcentaje >= 100) {
        return "completo";
    }
    if ($porcentaje >= 70) {
        return "alto";
    }
    if ($porcentaje >= 40) {
        return "medio";
    }

    return "bajo";
}
