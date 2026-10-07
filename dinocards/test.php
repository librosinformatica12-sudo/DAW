<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli('127.0.0.1', 'oscar', '561Cazadora#%', 'dinocards', 3306);

if ($conexion->connect_error) {
    echo "Fallo de conexión: " . $conexion->connect_error;
} else {
    echo "¡Conectado a MySQL correctamente!";
}