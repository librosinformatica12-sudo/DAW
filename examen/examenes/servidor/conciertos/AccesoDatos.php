<?php

/*
 * AccesoDatos.php
 * En este archivo se concentra el acceso a MySQL:
 *   - Se crea la conexión.
 *   - Se definen las funciones que llaman a los procedimientos.
 *   - Se devuelven arrays o códigos de resultado al PHP de la página.
 */

// Datos de conexión. En el examen se cambian según el enunciado.
$host = "127.0.0.1";
$port = 3306;
$database = "gestor_conciertos";
$username = "oscar";
$password = "1234";

// Desactiva las excepciones automáticas de mysqli para controlar los errores a mano.
mysqli_report(MYSQLI_REPORT_OFF);

// El @ evita que PHP muestre el warning si la conexión falla; después comprobamos el error.
$connection = @new mysqli($host, $username, $password, $database, $port);

// connect_errno devuelve un número distinto de 0 cuando hay error de conexión.
if (!$connection || $connection->connect_errno) {
    die('Error de conexión: ' . ($connection ? $connection->connect_error : 'mysqli devolvió false'));
}

/*
 * PA_AltaConcierto
 * Llama al procedure AltaConcierto.
 *
 * Parámetros:
 *   - $nombre: nombre de la banda.
 *   - $fecha: fecha y hora del concierto.
 *   - $precio: precio de la entrada.
 *
 * Devuelve el código que el procedure guarda en el parámetro OUT:
 *    0 = insertado correctamente
 *   -1 = nombre vacío
 *   -2 = fecha vacía
 *   -3 = fecha pasada
 *   -4 = precio no válido
 */
function PA_AltaConcierto(string $nombre, string $fecha, float $precio)
{
    // global permite usar la conexión creada fuera de la función.
    global $connection;

    // Convierte la fecha al formato que entiende MySQL: YYYY-MM-DD HH:MM:SS.
    $fecha = date('Y-m-d H:i:s', strtotime($fecha));

    // Los ? son marcadores de posición. @resultado recoge el parámetro OUT del procedure.
    $sql = 'CALL AltaConcierto(?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Si prepare() falla, $stmt es false; lanzamos una excepción con el error real.
    if (!$stmt) {
        throw new RuntimeException('Error al preparar AltaConcierto: ' . $connection->error);
    }

    // 'ssd' significa string, string y double (nombre, fecha y precio).
    $stmt->bind_param('ssd', $nombre, $fecha, $precio);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    // Hay que cerrar el statement antes de consultar la variable @resultado.
    $stmt->close();

    // SELECT @resultado lee el valor que el procedure dejó en la variable de usuario.
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    // (int) convierte el valor a número entero antes de devolverlo.
    return (int)$fila['resultado'];
}

/*
 * PA_ListaConcierto
 * Llama al procedure ListaConcierto y devuelve todas las filas en un array.
 */
function PA_ListaConcierto()
{
    global $connection;

    $sql = 'CALL ListaConcierto()';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error al preparar ListaConcierto. " . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException("Error al ejecutar el procedimiento: " . $error);
    }

    // Array donde se guarda cada fila que devuelve el SELECT.
    $conciertos = [];

    // get_result() obtiene el resultado del SELECT ejecutado por el procedure.
    $resultadoSelect = $stmt->get_result();

    // fetch_assoc() devuelve una fila como array asociativo o null cuando ya no hay filas.
    while ($fila = $resultadoSelect->fetch_assoc()) {
        $conciertos[] = $fila;
    }

    // Libera la memoria del resultado y cierra el statement.
    $resultadoSelect->free();
    $stmt->close();

    return $conciertos;
}
