<?php
// src/AccesoDatos.php

$host = 'localhost';
$db   = 'dinocards';
$user = 'oscar';
$pass = '561Cazadora#%';

$connection = new mysqli($host, $user, $pass, $db);

if ($connection->connect_error) {
    die('Error de conexión a la BD: ' . $connection->connect_error);
}

$connection->set_charset('utf8mb4');

/**
 * Genera el hash de la contraseña si es necesario.
 */
function hashContrasena(string $contrasena): string
{
    // Si guardas en texto plano para pruebas, devuelve $contrasena tal cual.
    // Si usas hash, ajusta según tu estándar:
    return md5($contrasena);
}

/**
 * Devuelve el código de salida (_res) del procedimiento Registro:
 *  0  -> Todo OK
 * -1  -> Usuario o email vacío
 * -2  -> Usuario o email ya existe
 * -3  -> Contraseña vacía
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

    return (int) ($fila['resultado'] ?? -99);
}

/**
 * Devuelve un array con:
 * ['codigo' => 0, 'usuario' => array] si es correcto
 * ['codigo' => -1|-2|-3, 'usuario' => null] si falla
 */
function PA_Login(string $email, string $contrasena): array
{
    global $connection;

    $stmt = $connection->prepare('CALL Login(?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar Login: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('ss', $email, $hash);
    $stmt->execute();

    // Obtener el ResultSet (SELECT * FROM usuarios)
    $result = $stmt->get_result();
    $datosUsuario = null;
    if ($result && $fila = $result->fetch_assoc()) {
        $datosUsuario = $fila;
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }
    $stmt->close();

    // Consultar el valor OUT
    $res = $connection->query('SELECT @resultado AS resultado');
    $filaRes = $res->fetch_assoc();
    $res->free();

    $codigo = (int) ($filaRes['resultado'] ?? -3);

    return [
        'codigo' => $codigo,
        'usuario' => $datosUsuario
    ];
}