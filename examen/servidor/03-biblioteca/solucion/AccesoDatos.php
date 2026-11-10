<?php
// =====================================================
// CAPA DE ACCESO A DATOS — BIBLIOTECA
// =====================================================

$host     = '127.0.0.1';
$port     = 3306;
$database = 'biblioteca_examen';
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
// LIBROS
// -----------------------------------------------------

function PA_ListaLibros(): array
{
    return ejecutarSelect('CALL ListaLibros()');
}

function PA_AltaLibro(string $titulo, string $autor, int $ejemplares): int
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaLibro(?, ?, ?, @resultado)');
    $stmt->bind_param('ssi', $titulo, $autor, $ejemplares);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar AltaLibro: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

// -----------------------------------------------------
// SOCIOS
// -----------------------------------------------------

function PA_AltaSocio(string $nombre): int
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaSocio(?, @resultado)');
    $stmt->bind_param('s', $nombre);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar AltaSocio: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

// -----------------------------------------------------
// PRÉSTAMOS
// -----------------------------------------------------

function PA_ListaPrestamos(): array
{
    return ejecutarSelect('CALL ListaPrestamos()');
}

function PA_PrestarLibro(int $libro, int $socio): int
{
    global $connection;

    $stmt = $connection->prepare('CALL PrestarLibro(?, ?, @resultado)');
    $stmt->bind_param('ii', $libro, $socio);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar PrestarLibro: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

function PA_DevolverLibro(int $idPrestamo): int
{
    global $connection;

    $stmt = $connection->prepare('CALL DevolverLibro(?, @resultado)');
    $stmt->bind_param('i', $idPrestamo);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar DevolverLibro: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}
