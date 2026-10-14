<?php
/**
 * api/anadir_carta.php
 * Añade un dinosaurio concreto a la colección del usuario.
 * Recibe el id por POST y responde en JSON.
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
$id  = (int) ($_POST['dinosaurio_id'] ?? 0);

if ($id <= 0) {
    responder_json(['ok' => false, 'mensaje' => 'Dinosaurio no indicado.'], 422);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM dinosaurios WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $dino = $stmt->fetch();

    if (!$dino) {
        responder_json(['ok' => false, 'mensaje' => 'Ese dinosaurio no existe.'], 404);
    }

    $pdo->prepare('INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
                   VALUES (:u, :d, NOW())')
        ->execute([':u' => $uid, ':d' => $id]);

    responder_json([
        'ok'         => true,
        'mensaje'    => 'Añadido a tu colección: ' . $dino['especie'] . ' «' . $dino['nombre'] . '»',
        'dinosaurio' => $dino,
    ]);
} catch (PDOException $e) {
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
