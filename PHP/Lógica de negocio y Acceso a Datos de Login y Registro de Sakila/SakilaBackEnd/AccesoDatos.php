<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);


function PA_Registrar(string $nombre, string $apellido, string $email, int $tienda, string $usuario, string $contrasena)
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL Registro(?, ?, ?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }
    $contrasenaHash = md5($contrasena);
    $stmt->bind_param('sssiss', $nombre, $apellido, $email, $tienda, $usuario, $contrasenaHash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
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






    
