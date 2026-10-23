<?php
/**
 * ColeccionService.php
 * ------------------------------------------------------------------
 * Consultas sobre la coleccion del usuario y el catalogo de
 * dinosaurios. Usa la conexion $conexion definida en AccesoDatos.php.
 *
 * Equivale (adaptado) a src/ColeccionService.php de dino-prueba.
 * ------------------------------------------------------------------
 */

/** Añade un dinosaurio a la coleccion de un usuario. */
function anadirCarta(int $usuario_id, int $dinosaurio_id): void
{
    global $conexion;

    $consulta = $conexion->prepare(
        "INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
         VALUES (?, ?, NOW())"
    );
    $consulta->bind_param("ii", $usuario_id, $dinosaurio_id);
    $consulta->execute();
    $consulta->close();
}

/**
 * Devuelve la lista de dinosaurio_id que posee un usuario.
 * Se conservan los repetidos.
 */
function obtenerIdsColeccion(int $usuario_id): array
{
    global $conexion;

    $ids = [];
    $consulta = $conexion->prepare("SELECT dinosaurio_id FROM colecciones WHERE usuario_id = ?");
    $consulta->bind_param("i", $usuario_id);
    $consulta->execute();
    $resultado = $consulta->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $ids[] = (int) $fila["dinosaurio_id"];
    }
    $consulta->close();

    return $ids;
}

/** Resumen de la coleccion: ['cartas' => int, 'especies' => int]. */
function resumenColeccion(int $usuario_id): array
{
    global $conexion;

    $consulta = $conexion->prepare(
        "SELECT COUNT(*) AS total, COUNT(DISTINCT dinosaurio_id) AS especies
         FROM colecciones WHERE usuario_id = ?"
    );
    $consulta->bind_param("i", $usuario_id);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    return [
        'cartas'   => (int) ($fila["total"] ?? 0),
        'especies' => (int) ($fila["especies"] ?? 0),
    ];
}

/** Numero total de dinosaurios del catalogo. */
function contarDinosaurios(): int
{
    global $conexion;

    $fila = $conexion->query("SELECT COUNT(*) AS total FROM dinosaurios")->fetch_assoc();

    return (int) ($fila["total"] ?? 0);
}

/** Devuelve un dinosaurio al azar, o null si no hay ninguno. */
function dinosaurioAleatorio(): ?array
{
    global $conexion;

    $fila = $conexion->query("SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 1")->fetch_assoc();

    return $fila ?: null;
}

/** Devuelve N dinosaurios al azar (sin repetir), o [] si no hay ninguno. */
function dinosauriosAleatorios(int $n): array
{
    global $conexion;

    $n = max(1, min(50, $n));

    $dinos = [];
    $resultado = $conexion->query("SELECT * FROM dinosaurios ORDER BY RAND() LIMIT " . $n);
    while ($fila = $resultado->fetch_assoc()) {
        $dinos[] = $fila;
    }

    return $dinos;
}

/**
 * Devuelve los dinosaurios indicados indexados por su id:
 *   [ id => [fila de dinosaurios] ]
 */
function dinosauriosPorIds(array $ids): array
{
    global $conexion;

    $ids = array_values(array_unique(array_filter($ids, 'is_numeric')));
    if (!$ids) {
        return [];
    }

    $lista = implode(",", array_map('intval', $ids)); // son numeros, no hay peligro

    $mapa = [];
    $resultado = $conexion->query("SELECT * FROM dinosaurios WHERE id IN ($lista)");
    while ($fila = $resultado->fetch_assoc()) {
        $mapa[(int) $fila["id"]] = $fila;
    }

    return $mapa;
}

/** Ultimas cartas obtenidas por un usuario (mas recientes primero). */
function ultimasCartas(int $usuario_id, int $limite = 6): array
{
    global $conexion;

    $limite = max(1, min(50, $limite));

    $consulta = $conexion->prepare(
        "SELECT d.*
         FROM colecciones c
         INNER JOIN dinosaurios d ON d.id = c.dinosaurio_id
         WHERE c.usuario_id = ?
         ORDER BY c.fecha_adquisicion DESC
         LIMIT " . $limite
    );
    $consulta->bind_param("i", $usuario_id);
    $consulta->execute();
    $resultado = $consulta->get_result();

    $filas = [];
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    $consulta->close();

    return $filas;
}
