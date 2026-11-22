<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '1234';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

function falloAccesoDatos(string $detalle): void
{
    error_log($detalle);
    responder('No se pudo completar la operación. Inténtalo de nuevo.', 500);
}

function PA_Registrar(string $nick, string $email, string $contrasena)
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL Registro(?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }
    $contrasenaHash = md5($contrasena);
    $stmt->bind_param('sss', $nick, $email, $contrasenaHash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}
function PA_Login(string $email, string $contrasena): array
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL Login(?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }
    $contrasenaHash = md5($contrasena);
    $stmt->bind_param('ss', $email, $contrasenaHash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    $usuario = null;
    if ($stmt->field_count > 0) {
        $resultadoUsuario = $stmt->get_result();
        if ($resultadoUsuario) {
            $usuario = $resultadoUsuario->fetch_assoc() ?: null;
            $resultadoUsuario->free();
        }
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }

        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return [
        'resultado' => (int) $fila['resultado'],
        'usuario' => $usuario,
    ];
}

/** @return array{resultado: int, coleccion: list<array<string, mixed>>} */
function PA_ObtenerColeccion(int $idUsuario): array
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL ObtenerColeccion(?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }

    $stmt->bind_param('i', $idUsuario);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    $coleccion = [];
    if ($stmt->field_count > 0) {
        $resultadoColeccion = $stmt->get_result();
        if ($resultadoColeccion) {
            while ($fila = $resultadoColeccion->fetch_assoc()) {
                $fila['id'] = (int) $fila['id'];
                $coleccion[] = $fila;
            }
            $resultadoColeccion->free();
        }
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }

        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return [
        'resultado' => (int) $fila['resultado'],
        'coleccion' => $coleccion,
    ];
}

function PA_AñadirNuevaCartaAColeccion(int $idUsuario): int
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL `AñadirNuevaCartaAColeccion`(?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }

    $stmt->bind_param('i', $idUsuario);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }

        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int) $fila['resultado'];
}
