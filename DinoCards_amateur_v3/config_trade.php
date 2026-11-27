<?php
/*
 * CONFIGURACIÓN DE LA CONEXIÓN PARA INTERCAMBIOS
 * Cambia estos datos para que coincidan con tu MySQL/XAMPP.
 * La base de datos indicada es la del script original: DinoCards_amateur.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$DB_HOST = 'localhost';
$DB_USER = 'oscar';
$DB_PASS = '1234';
$DB_NAME = 'dinocards'; // Si tu BD se llama dinocards, cambia este nombre.

try {
    $db = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    exit('No se pudo conectar con MySQL. Revisa config_trade.php.');
}
