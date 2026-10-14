<?php
/**
 * ColeccionService.php
 * ------------------------------------------------------------------
 * Lógica de la colección y consultas al catálogo de dinosaurios.
 * Usa la conexión $connection definida en AccesoDatos.php.
 * ------------------------------------------------------------------
 */

require_once __DIR__ . '/ReglasColeccion.php';

/**
 * Añade una carta a la colección del usuario mediante el procedimiento
 * AñadirNuevaCartaAColeccion. Devuelve el código _res:
 *   0  -> añadida OK
 *  -1  -> el usuario no existe
 *  -2  -> el dinosaurio no existe
 */
function PA_AñadirCarta(int $usuarioId, int $cartaId): int
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    $stmt = $connection->prepare('CALL AñadirNuevaCartaAColeccion(?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $stmt->bind_param('ii', $usuarioId, $cartaId);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }
    $stmt->close();

    $res = $connection->query('SELECT @resultado AS resultado');
    if (!$res) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }
    $fila = $res->fetch_assoc();
    $res->free();

    return isset($fila['resultado']) ? (int) $fila['resultado'] : -99;
}

/**
 * Devuelve la lista de dinosaurio_id que posee un usuario usando el
 * procedimiento ObtenerColeccion. Se conservan los repetidos.
 */
function PA_ObtenerColeccion(int $usuarioId): array
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    $stmt = $connection->prepare('CALL ObtenerColeccion(?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $stmt->bind_param('i', $usuarioId);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $ids = [];
    $resultado = $stmt->get_result();
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $ids[] = (int) $fila['dinosaurio_id'];
        }
        $resultado->free();
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }
    $stmt->close();

    return $ids;
}

/** Resumen de la colección de un usuario: ['cartas' => int, 'especies' => int]. */
function resumenColeccion(int $usuarioId): array
{
    global $connection;

    $resumen = ['cartas' => 0, 'especies' => 0];
    if ($connection->connect_errno) {
        return $resumen;
    }

    $stmt = $connection->prepare(
        'SELECT COUNT(*) AS total, COUNT(DISTINCT dinosaurio_id) AS especies
         FROM colecciones WHERE usuario_id = ?'
    );
    if (!$stmt) {
        return $resumen;
    }

    $stmt->bind_param('i', $usuarioId);
    if ($stmt->execute()) {
        $fila = $stmt->get_result()->fetch_assoc();
        $resumen['cartas']   = (int) ($fila['total'] ?? 0);
        $resumen['especies'] = (int) ($fila['especies'] ?? 0);
    }
    $stmt->close();

    return $resumen;
}

/** Últimas cartas obtenidas por un usuario (más recientes primero). */
function ultimasCartas(int $usuarioId, int $limite = 6): array
{
    global $connection;

    if ($connection->connect_errno) {
        return [];
    }

    $limite = max(1, min(50, $limite));
    $stmt = $connection->prepare(
        'SELECT d.*, MAX(c.fecha_adquisicion) AS ultima
         FROM colecciones c
         JOIN dinosaurios d ON d.id = c.dinosaurio_id
         WHERE c.usuario_id = ?
         GROUP BY d.id
         ORDER BY ultima DESC, d.id DESC
         LIMIT ' . $limite
    );
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $filas = [];
    while ($fila = $resultado->fetch_assoc()) {
        $filas[] = $fila;
    }
    $stmt->close();

    return $filas;
}

/**
 * Devuelve los dinosaurios indicados indexados por su id:
 *   [ id => [fila de dinosaurios] ]
 */
function dinosauriosPorIds(array $ids): array
{
    global $connection;

    $ids = array_values(array_unique(array_filter($ids, 'is_numeric')));
    if (!$ids || $connection->connect_errno) {
        return [];
    }

    $lista = implode(',', array_map('intval', $ids));

    $res = $connection->query('SELECT * FROM dinosaurios WHERE id IN (' . $lista . ')');
    if (!$res) {
        return [];
    }

    $mapa = [];
    while ($fila = $res->fetch_assoc()) {
        $mapa[(int) $fila['id']] = $fila;
    }
    $res->free();

    return $mapa;
}

/** Número total de dinosaurios del catálogo. */
function contarDinosaurios(): int
{
    global $connection;

    if ($connection->connect_errno) {
        return 0;
    }

    $res = $connection->query('SELECT COUNT(*) AS total FROM dinosaurios');
    if (!$res) {
        return 0;
    }

    $fila = $res->fetch_assoc();
    $res->free();

    return (int) ($fila['total'] ?? 0);
}

/** Devuelve un dinosaurio al azar, o null si no hay ninguno. */
function dinosaurioAleatorio(): ?array
{
    global $connection;

    if ($connection->connect_errno) {
        return null;
    }

    $res = $connection->query('SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 1');
    if (!$res) {
        return null;
    }

    $fila = $res->fetch_assoc();
    $res->free();

    return $fila ?: null;
}
