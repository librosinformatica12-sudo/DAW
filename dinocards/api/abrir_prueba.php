<?php
/**
 * api/abrir_prueba.php
 * Sobre de prueba: entrega 4 cartas aleatorias (sin límite diario).
 * Responde en JSON con la lista de dinosaurios obtenidos.
 */
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/conexion.php';

if (!esta_logueado()) {
    responder_json(['ok' => false, 'mensaje' => 'Debes iniciar sesión.'], 401);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

$uid = usuario_actual();

try {
    $dinos = $pdo->query('SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 4')->fetchAll();
    if (!$dinos) {
        responder_json(['ok' => false, 'mensaje' => 'No hay dinosaurios disponibles.'], 500);
    }

    $pdo->beginTransaction();
    $ins = $pdo->prepare('INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
                          VALUES (:u, :d, NOW())');
    foreach ($dinos as $d) {
        $ins->execute([':u' => $uid, ':d' => $d['id']]);
    }
    $pdo->commit();

    responder_json([
        'ok'          => true,
        'mensaje'     => '¡Has conseguido ' . count($dinos) . ' cartas de prueba!',
        'dinosaurios' => $dinos,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
