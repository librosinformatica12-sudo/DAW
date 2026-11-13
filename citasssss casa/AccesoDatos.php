<?php
    //No olvides poner aquí tus parámetros de conexión para que funcione en tu maquina
$host = '127.0.0.1';
$port = 3306;
$database = 'aerolinea';
$username = 'guilleDB';
$password = '1234';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = new mysqli($host, $username, $password, $database, $port);

if ($connection->connect_error) {
    throw new RuntimeException('Error al conectar con la base de datos: ' . $connection->connect_error);
}

function PA_ListaBilletes()
{
    global $connection;

    $stmt = $connection->prepare('CALL ListaBilletes()');
    if (!$stmt || !$stmt->execute()) {
        $error = $stmt ? $stmt->error : $connection->error;
        if ($stmt) {
            $stmt->close();
        }
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $billetes = [];
    $resultadoSelect = $stmt->get_result();
    if ($resultadoSelect) {
        while ($fila = $resultadoSelect->fetch_assoc()) {
            $billetes[] = [
                'id_billete' => $fila['id_billete'],
                'destino' => $fila['destino'],
                'fecha' => $fila['fecha'],
                'documento_viajero' => $fila['fk_viajero'],
            ];
        }
        $resultadoSelect->free();
    }
    $stmt->close();

    return $billetes;
}

function PA_ListaViajeros()
{
    global $connection;

    $stmt = $connection->prepare('CALL ListaViajeros()');
    if (!$stmt || !$stmt->execute()) {
        $error = $stmt ? $stmt->error : $connection->error;
        if ($stmt) {
            $stmt->close();
        }
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $viajeros = [];
    $resultadoSelect = $stmt->get_result();
    if ($resultadoSelect) {
        while ($fila = $resultadoSelect->fetch_assoc()) {
            $viajeros[] = [
                'documento' => $fila['documento'],
                'nombre' => $fila['nombre'],
                'apellidos' => $fila['apellidos'],
            ];
        }
        $resultadoSelect->free();
    }
    $stmt->close();

    return $viajeros;
}

function PA_DameViajero(string $documento)
{
    global $connection;

    $stmt = $connection->prepare('CALL DameViajero(?)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }
    $stmt->bind_param('s', $documento);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $viajeros = [];
    $resultadoSelect = $stmt->get_result();
    if ($resultadoSelect) {
        while ($fila = $resultadoSelect->fetch_assoc()) {
            $viajeros[] = [
                'documento' => $fila['documento'],
                'nombre' => $fila['nombre'],
                'apellidos' => $fila['apellidos'],
            ];
        }
        $resultadoSelect->free();
    }
    $stmt->close();

    return $viajeros;
}

function PA_AltaViajero(string $documento, string $nombre, string $apellidos)
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaViajero(?, ?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }
    $stmt->bind_param('sss', $documento, $nombre, $apellidos);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}

function PA_AltaBillete(string $destino, string $fecha, string $documento_viajero)
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaBillete(?, ?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }
    $stmt->bind_param('sss', $destino, $fecha, $documento_viajero);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}

function PA_ActualizarBillete(int $idBillete, string $destino, string $fecha, string $documento_viajero)
{
    global $connection;

    $stmt = $connection->prepare('CALL ModificarBillete(?, ?, ?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }
    $stmt->bind_param('isss', $idBillete, $destino, $fecha, $documento_viajero);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}

function PA_CancelarTodosBilletes()
{
    global $connection;

    $stmt = $connection->prepare('CALL CancelarTodosBilletes()');
    if (!$stmt || !$stmt->execute()) {
        $error = $stmt ? $stmt->error : $connection->error;
        if ($stmt) {
            $stmt->close();
        }
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();
}
/*Pregunta 7 - Construye una funcion que llame al PA que cancela UN solo billete*/
function PA_CancelarBillete(int $idBillete)
{
    global $connection;

    $stmt = $connection->prepare('CALL BorrarBillete(?)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }
    $stmt->bind_param('i', $idBillete);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();
}

