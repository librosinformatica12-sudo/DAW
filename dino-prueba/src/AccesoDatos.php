<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

if (!$connection->connect_errno) {
    $connection->set_charset('utf8mb4');
}

function hashContrasena(string $contrasena): string
{
    return md5($contrasena);
}

/** Devuelve el código de salida (_res) del procedimiento Registro. */
function PA_Registrar(string $usuario, string $email, string $contrasena): int
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    // El SP 'Registro' recibe 3 IN (username, email, password) y 1 OUT (_res)
    $stmt = $connection->prepare('CALL Registro(?, ?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('sss', $usuario, $email, $hash);

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

    return (int) $fila['resultado'];
}

/** Devuelve ['codigo' => int, 'usuario' => array|null]. */
function PA_Login(string $email, string $contrasena): array
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    // El SP 'Login' recibe 2 IN (email, password) y 1 OUT (_res)
    $stmt = $connection->prepare('CALL Login(?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('ss', $email, $hash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $datosUsuario = null;
    do {
        $rs = $stmt->get_result();
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $datosUsuario = $fila;
            }
            $rs->free();
        }
    } while ($stmt->more_results() && $stmt->next_result());
    $stmt->close();

    $res = $connection->query('SELECT @resultado AS resultado');
    if (!$res) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }
    $fila = $res->fetch_assoc();
    $res->free();

    return ['codigo' => (int) $fila['resultado'], 'usuario' => $datosUsuario];
}

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

/** Devuelve los datos de un usuario por su id, o null si no existe. */
function obtenerUsuarioPorId(int $usuarioId): ?array
{
    global $connection;

    if ($connection->connect_errno) {
        return null;
    }

    $stmt = $connection->prepare(
        'SELECT id, username, email, fecha_registro, ultima_obtencion
         FROM usuarios WHERE id = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $fila ?: null;
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

/** Actualiza la fecha/hora de la última carta obtenida del usuario. */
function marcarUltimaObtencion(int $usuarioId): void
{
    global $connection;

    if ($connection->connect_errno) {
        return;
    }

    $stmt = $connection->prepare('UPDATE usuarios SET ultima_obtencion = NOW() WHERE id = ?');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $stmt->close();
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
        'SELECT d.*
         FROM colecciones c
         JOIN dinosaurios d ON d.id = c.dinosaurio_id
         WHERE c.usuario_id = ?
         ORDER BY c.fecha_adquisicion DESC, c.id DESC
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