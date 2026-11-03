<?php

// Copia este archivo a AccesoDatos.php y pon tus credenciales reales.

$host = '127.0.0.1';
$port = 3306;
$database = 'clinica';
$username = 'oscar';
$password = '1234';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

function PA_AltaDoctor(string $n_licencia, string $n_nombre, string $n_apellido) {
    global $connection;

    // Se añadió el '$' a las variables $sql y $stmt
    $sql = "CALL AltaDoctor(?, ?, ?, @resultado)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        return "Error en la preparación: " . $connection->error;
    }

    $stmt->bind_param("sss", $n_licencia, $n_nombre, $n_apellido);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return "Error al ejecutar el procedimiento almacenado: " . $error;
    }
    
    $stmt->close();

    $resultadoQuery = $connection->query("SELECT @resultado AS resultado;");
    
    if ($resultadoQuery) {
        $fila = $resultadoQuery->fetch_assoc();
        return (int)($fila['resultado'] ?? 0);
    }

    return null;
}