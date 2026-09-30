<?php
/**
 * Conexión a la base de datos Sakila.
 * Cambia estos cuatro valores por los tuyos (los de tu MySQL Workbench).
 */
$host = 'localhost';
$puerto = '3306';
$nombreBD = 'sakila';
$usuarioBD = 'TU_USUARIO_MYSQL';       // <-- cámbialo
$passwordBD = 'TU_CONTRASEÑA_MYSQL';   // <-- cámbialo

$dsn = "mysql:host=$host;port=$puerto;dbname=$nombreBD;charset=utf8mb4";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $usuarioBD, $passwordBD, $opciones);
} catch (PDOException $error) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No se pudo conectar con MySQL: ' . $error->getMessage(),
    ]);
    exit;
}