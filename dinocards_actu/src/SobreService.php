<?php
/**
 * src/SobreService.php
 * ------------------------------------------------------------------
 * Logica de negocio de los sobres (sobre del dia y sobre de prueba):
 * comprobar la espera, elegir dinosaurios al azar y guardar las cartas.
 * No contiene SQL: usa dal/*DAL.php.
 *
 * Cada funcion devuelve el "flash" que las paginas muestran despues:
 *   ['tipo' => 'ok'|'error', 'mensaje' => string, 'carta' => array | 'cartas' => array[]]
 * ------------------------------------------------------------------
 */

const ESPERA_SOBRE_DIA    = 24 * 60 * 60; // 24 horas
const ESPERA_SOBRE_PRUEBA = 120;          // 2 minutos
const CARTAS_SOBRE_PRUEBA = 4;            // cartas por sobre de prueba

/** Abre el sobre del dia (1 carta cada 24 horas). */
function abrirSobreDelDia(int $usuario_id): array
{
    $usuario = obtenerUsuarioPorId($usuario_id);
    $faltan  = segundosRestantes($usuario["ultima_obtencion"] ?? null, ESPERA_SOBRE_DIA);

    if ($faltan > 0) {
        return [
            "tipo"    => "error",
            "mensaje" => "Ya has abierto tu sobre del día. Vuelve en unas horas.",
        ];
    }

    $azar = dinosaurioAleatorio();
    if (!$azar) {
        return [
            "tipo"    => "error",
            "mensaje" => "No hay dinosaurios disponibles.",
        ];
    }

    anadirCarta($usuario_id, (int) $azar["id"]);
    marcarUltimaObtencion($usuario_id);

    return [
        "tipo"    => "ok",
        "mensaje" => "¡Has conseguido una carta nueva!",
        "carta"   => $azar,
    ];
}

/** Abre el sobre de prueba (4 cartas, espera de 2 minutos). */
function abrirSobrePrueba(int $usuario_id): array
{
    $usuario = obtenerUsuarioPorId($usuario_id);
    $faltan  = segundosRestantes($usuario["carta_prueba"] ?? null, ESPERA_SOBRE_PRUEBA);

    if ($faltan > 0) {
        return [
            "tipo"    => "error",
            "mensaje" => "Aún no puedes abrir el sobre de prueba. Espera a que termine el contador.",
        ];
    }

    $dinos = dinosauriosAleatorios(CARTAS_SOBRE_PRUEBA);
    if (count($dinos) < CARTAS_SOBRE_PRUEBA) {
        return [
            "tipo"    => "error",
            "mensaje" => "No hay dinosaurios suficientes en el catálogo.",
        ];
    }

    anadirCartas($usuario_id, array_map(static function (array $d): int {
        return (int) $d["id"];
    }, $dinos));
    marcarCartaPrueba($usuario_id);

    return [
        "tipo"    => "ok",
        "mensaje" => "¡Has conseguido " . count($dinos) . " cartas de prueba!",
        "cartas"  => $dinos,
    ];
}
