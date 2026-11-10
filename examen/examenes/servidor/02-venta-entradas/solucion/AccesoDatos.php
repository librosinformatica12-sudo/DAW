<?php

/*
 * AccesoDatos.php
 * Conexion a MySQL y funciones que llaman a los procedimientos del
 * ejercicio de venta de entradas.
 */

// Datos de conexion. Cambialos si tu MySQL usa otro usuario o contrasena.
$host = "127.0.0.1";
$port = 3306;
$database = "conciertos_ventas_examen";
$username = "oscar";
$password = "1234";

// Desactiva las excepciones automaticas para controlar los errores a mano.
mysqli_report(MYSQLI_REPORT_OFF);

$connection = @new mysqli($host, $username, $password, $database, $port);

if (!$connection || $connection->connect_errno) {
    die('Error de conexion: ' . ($connection ? $connection->connect_error : 'mysqli devolvio false'));
}

/*
 * PA_ComprarEntradas
 * Llama al procedure ComprarEntradas.
 *
 * Parametros:
 *   - $idConcierto: id del concierto.
 *   - $cantidad: numero de entradas que se quieren comprar.
 *
 * Devuelve el codigo OUT del procedure:
 *    0 = compra correcta
 *   -1 = concierto no existe
 *   -2 = cantidad incorrecta
 *   -3 = no hay entradas suficientes
 *   -4 = concierto pasado
 *   -5 = error inesperado
 */
function PA_ComprarEntradas(int $idConcierto, int $cantidad): int
{
    global $connection;

    // Los ? son marcadores de posicion. @resultado recoge el parametro OUT.
    $sql = 'CALL ComprarEntradas(?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Error al preparar ComprarEntradas: ' . $connection->error);
    }

    // 'ii' significa dos numeros enteros.
    $stmt->bind_param('ii', $idConcierto, $cantidad);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar ComprarEntradas: ' . $error);
    }

    // Hay que cerrar el statement antes de consultar @resultado.
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el resultado: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int)$fila['resultado'];
}

/*
 * PA_ListaConciertos
 * Devuelve los conciertos con el aforo, las vendidas y las disponibles.
 */
function PA_ListaConciertos(): array
{
    global $connection;

    $sql = 'CALL ListaConciertos()';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Error al preparar ListaConciertos: ' . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar ListaConciertos: ' . $error);
    }

    $conciertos = [];
    $resultadoSelect = $stmt->get_result();

    while ($fila = $resultadoSelect->fetch_assoc()) {
        $conciertos[] = $fila;
    }

    $resultadoSelect->free();
    $stmt->close();

    return $conciertos;
}

/*
 * PA_ListaEntradas
 * Devuelve las ventas realizadas con el nombre del concierto.
 */
function PA_ListaEntradas(): array
{
    global $connection;

    $sql = 'CALL ListaEntradas()';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Error al preparar ListaEntradas: ' . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar ListaEntradas: ' . $error);
    }

    $entradas = [];
    $resultadoSelect = $stmt->get_result();

    while ($fila = $resultadoSelect->fetch_assoc()) {
        $entradas[] = $fila;
    }

    $resultadoSelect->free();
    $stmt->close();

    return $entradas;
}
