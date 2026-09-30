<?php
require_once __DIR__ . '/AccesoDatos.php';
header('Content-Type: text/plain; charset=utf-8');

if ($connection->connect_errno) {
    exit('ERROR DE CONEXION: ' . $connection->connect_error);
}

echo "== SERVIDOR AL QUE SE CONECTA PHP ==\n";
$r = $connection->query('SELECT @@port AS puerto, @@version AS version, @@datadir AS datadir, DATABASE() AS bd, CURRENT_USER() AS usuario');
print_r($r->fetch_assoc());

echo "\n== PROCEDIMIENTOS LLAMADOS Registro* QUE VE PHP ==\n";
$r = $connection->query("SELECT ROUTINE_SCHEMA, ROUTINE_NAME,
        IF(LOCATE('FROM store', ROUTINE_DEFINITION) > 0, 'ANTIGUO (comprueba tienda, devuelve -4)', 'NUEVO') AS version_procedimiento,
        LAST_ALTERED
    FROM information_schema.ROUTINES
    WHERE ROUTINE_NAME LIKE 'Registro%'");
if ($r->num_rows === 0) {
    echo "No se ve ningun procedimiento Registro (o el usuario no tiene permisos para verlo).\n";
}
while ($fila = $r->fetch_assoc()) {
    print_r($fila);
}

echo "\n== EMPLEADOS EN staff (lo que ve PHP) ==\n";
$r = $connection->query('SELECT staff_id, first_name, last_name, store_id, username FROM staff');
while ($fila = $r->fetch_assoc()) {
    echo implode(' | ', $fila) . "\n";
}
