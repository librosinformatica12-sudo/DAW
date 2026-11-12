<?php
// =====================================================
// CAPA DE ACCESO A DATOS
// Aquí está la conexión y TODAS las llamadas a MySQL.
// =====================================================

$host     = '127.0.0.1';
$port     = 3306;
$database = 'clinica_examen';
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

/**
 * Lee el parámetro OUT @resultado de la última llamada.
 * Todos los procedures con OUT lo guardan ahí.
 */
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

/**
 * Ejecuta un CALL que devuelve filas y las devuelve como array asociativo.
 */
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
// DOCTORES
// -----------------------------------------------------

function PA_ListaDoctores(): array
{
    return ejecutarSelect('CALL ListaDoctores()');
}

function PA_DameDoctor(int $licencia): ?array
{
    global $connection;

    $stmt = $connection->prepare('CALL DameDoctor(?)');
    $stmt->bind_param('i', $licencia);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar DameDoctor: ' . $error);
    }

    $resultado = $stmt->get_result();
    $fila = $resultado ? $resultado->fetch_assoc() : null;

    if ($resultado) {
        $resultado->free();
    }
    $stmt->close();

    return $fila ?: null;
}

function PA_AltaDoctor(int $licencia, string $nombre, string $apellidos): int
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaDoctor(?, ?, ?, @resultado)');
    $stmt->bind_param('iss', $licencia, $nombre, $apellidos);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar AltaDoctor: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

// -----------------------------------------------------
// CITAS
// -----------------------------------------------------

function PA_ListaCitas(): array
{
    return ejecutarSelect('CALL ListaCitas()');
}

function PA_AltaCita(string $paciente, string $especialidad, string $fecha, int $doctor): int
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaCita(?, ?, ?, ?, @resultado)');
    $stmt->bind_param('sssi', $paciente, $especialidad, $fecha, $doctor);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar AltaCita: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

function PA_ModificarCita(int $id, string $paciente, string $especialidad, string $fecha, int $doctor): int
{
    global $connection;

    $stmt = $connection->prepare('CALL ModificarCita(?, ?, ?, ?, ?, @resultado)');
    $stmt->bind_param('isssi', $id, $paciente, $especialidad, $fecha, $doctor);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar ModificarCita: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}

function PA_BorrarCita(int $id): int
{
    global $connection;

    $stmt = $connection->prepare('CALL BorrarCita(?, @resultado)');
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar BorrarCita: ' . $error);
    }

    $stmt->close();

    return leerResultadoOut();
}
