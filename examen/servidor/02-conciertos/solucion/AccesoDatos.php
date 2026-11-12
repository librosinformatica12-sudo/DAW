<?php
// =====================================================
// CAPA DE ACCESO A DATOS — CONCIERTOS
// =====================================================

$host     = '127.0.0.1';
$port     = 3306;
$database = 'gestor_conciertos_examen';
$username = 'root';
$password = '1234';      // <-- cámbialo si tu MySQL usa otra

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

if ($connection->connect_errno) {
    die('Error de conexión: ' . $connection->connect_error);
}

$connection->set_charset('utf8mb4');

// -----------------------------------------------------
// HELPERS
// -----------------------------------------------------

function leerResultadoOut(): int
{
    global $connection;

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al leer @resultado: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int)$fila['resultado'];
}

function ejecutarSelect(string $sql): array
{
    global $connection;

    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Error al preparar: ' . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar: ' . $error);
    }

    $filas = [];
    $resultado = $stmt->get_result();

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }
        $resultado->free();
    }

    $stmt->close();

    return $filas;
}

// -----------------------------------------------------
// CONSULTAS
// -----------------------------------------------------

function PA_ListaConciertos(): array
{
    return ejecutarSelect('CALL ListaConciertos()');
}

function PA_DameConcierto(int $id): ?array
{
    global $connection;

    $stmt = $connection->prepare('CALL DameConcierto(?)');
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar DameConcierto: ' . $error);
    }

    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;

    if ($resultado) {
        $resultado->free();
    }
    $stmt->close();

    return $fila ?: null;
}

// -----------------------------------------------------
// ALTAS / MODIFICACIONES
// -----------------------------------------------------

function PA_AltaConcierto(string $nombre, string $fecha, float $precio, int $aforo): int
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaConcierto(?, ?, ?, ?, @resultado)');
    $stmt->bind_param('ssdi', $nombre, $fecha, $precio, $aforo);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar AltaConcierto: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

function PA_ModificarConcierto(int $id, string $nombre, string $fecha, float $precio, int $aforo): int
{
    global $connection;

    $stmt = $connection->prepare('CALL ModificarConcierto(?, ?, ?, ?, ?, @resultado)');
    $stmt->bind_param('issdi', $id, $nombre, $fecha, $precio, $aforo);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar ModificarConcierto: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

function PA_BorrarConcierto(int $id): int
{
    global $connection;

    $stmt = $connection->prepare('CALL BorrarConcierto(?, @resultado)');
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar BorrarConcierto: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

function PA_ComprarEntrada(int $idConcierto): int
{
    global $connection;

    $stmt = $connection->prepare('CALL ComprarEntrada(?, @resultado)');
    $stmt->bind_param('i', $idConcierto);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar ComprarEntrada: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}
