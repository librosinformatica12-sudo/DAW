<?php
/**
 * conexion.php
 * ------------------------------------------------------------
 * Conexión única a la base de datos mediante PDO.
 * Se definen las credenciales como constantes para no repetirlas.
 * ------------------------------------------------------------
 */

// Datos de la base de datos en XAMPP
const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'dinocards';
const DB_USER = 'oscar';
const DB_PASS = '561Cazadora#%';

/**
 * Crea y devuelve una conexión PDO lista para usar.
 * Lanza una excepción si no se puede conectar.
 */
function conectar_bd(): PDO
{
    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $opciones = [
        // Que los errores salgan como excepciones (más fácil de depurar)
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        // Que los resultados vengan como array asociativo
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Usar consultas preparadas reales (más seguras)
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $opciones);
}

// Conexión global reutilizable
$pdo = conectar_bd();
