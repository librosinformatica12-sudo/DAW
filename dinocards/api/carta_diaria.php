<?php
/**
 * api/carta_diaria.php
 * Abre el sobre del día: da una carta aleatoria y solo una vez al día.
 * Responde en JSON con el dinosaurio obtenido.
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
    // ¿Ya se ha abierto hoy? Comparamos solo la fecha
    $stmt = $pdo->prepare('SELECT ultima_obtencion FROM usuarios WHERE id = :id');
    $stmt->execute([':id' => $uid]);
    $usuario = $stmt->fetch();

    $hoy = date('Y-m-d');
    if ($usuario && $usuario['ultima_obtencion'] !== null
        && substr($usuario['ultima_obtencion'], 0, 10) === $hoy) {
        responder_json(['ok' => false, 'mensaje' => 'Ya has abierto tu sobre hoy. Vuelve mañana.'], 409);
    }

    // Dinosaurio al azar
    $dino = $pdo->query('SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 1')->fetch();
    if (!$dino) {
        responder_json(['ok' => false, 'mensaje' => 'No hay dinosaurios disponibles.'], 500);
    }

    // Guardamos la carta y marcamos la fecha
    $pdo->beginTransaction();
    $pdo->prepare('INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
                   VALUES (:u, :d, NOW())')
        ->execute([':u' => $uid, ':d' => $dino['id']]);
    $pdo->prepare('UPDATE usuarios SET ultima_obtencion = NOW() WHERE id = :u')
        ->execute([':u' => $uid]);
    $pdo->commit();

    responder_json([
        'ok'         => true,
        'mensaje'    => '¡Has conseguido una carta nueva!',
        'dinosaurio' => $dino,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
