<?php
/**
 * api/dinosaurios.php
 * Devuelve el catálogo completo de dinosaurios en JSON.
 */
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/conexion.php';

if (!esta_logueado()) {
    responder_json(['ok' => false, 'mensaje' => 'Debes iniciar sesión.'], 401);
}

try {
    $dinos = $pdo->query('SELECT * FROM dinosaurios ORDER BY id')->fetchAll();
    responder_json(['ok' => true, 'dinosaurios' => $dinos]);
} catch (PDOException $e) {
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
