<?php
// src/AccesoDatos.php

$host = '127.0.0.1';
$port = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '561Cazadora#%';

$connection = new mysqli($host, $username, $password, $database, $port);

if ($connection->connect_error) {
    die('Error de conexión a la BD: ' . $connection->connect_error);
}

$connection->set_charset('utf8mb4');

/**
 * Genera el hash MD5 de la contraseña.
 */
function hashContrasena(string $contrasena): string
{
    return md5($contrasena);
}

/**
 * Devuelve el código de salida (_res) del procedimiento Registro:
 */
function PA_Registrar(string $usuario, string $email, string $contrasena): int
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    $stmt = $connection->prepare('CALL Registro(?, ?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('sss', $usuario, $email, $hash);

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

    return isset($fila['resultado']) ? (int) $fila['resultado'] : -99;
}

/** 
 * Devuelve ['codigo' => int, 'staff' => array|null].
 */
function PA_Login(string $usuarioOEmail, string $contrasena): array
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
    $stmt->bind_param('ss', $usuarioOEmail, $hash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    // Leer los datos devueltos por la consulta si el login es correcto
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

    // Recuperar la variable de salida OUT
    $res = $connection->query('SELECT @resultado AS resultado');
    if (!$res) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }
    $fila = $res->fetch_assoc();
    $res->free();

    // Si @resultado no tiene valor, devolver -2 (Usuario/Contraseña incorrectos) en lugar de -99
    $codigoFinal = (isset($fila['resultado']) && $fila['resultado'] !== null) 
        ? (int) $fila['resultado'] 
        : -2;

    return [
        'codigo' => $codigoFinal,
        'staff' => $staff
    ];
}