<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
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
<<<<<<< HEAD
function PA_Registrar(string $nombre, string $apellido, string $email, int $tienda, string $usuario, string $contrasena): int
=======
function PA_Registrar(string $nombre, string $apellido, string $email, string $usuario, string $contrasena): int
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

<<<<<<< HEAD
    $stmt = $connection->prepare('CALL Registro(?, ?, ?, ?, ?, ?, @resultado)');
=======
    $stmt = $connection->prepare('CALL Registro(?, ?, ?, ?, ?, @resultado)');
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
<<<<<<< HEAD
    $stmt->bind_param('sssiss', $nombre, $apellido, $email, $tienda, $usuario, $hash);
=======
    $stmt->bind_param('sssss', $nombre, $apellido, $email, $usuario, $hash);
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2

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

/** Devuelve ['codigo' => int, 'staff' => array|null]. */
function PA_Login(string $usuario, string $contrasena): array
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    $stmt = $connection->prepare('CALL Login(?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('ss', $usuario, $hash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    // Leer el registro del empleado ANTES de descartar los result sets
    $staff = null;
    do {
        $rs = $stmt->get_result();
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $staff = $fila;
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

    return ['codigo' => (int) $fila['resultado'], 'staff' => $staff];
}