<?php
$DEBUG = true;

ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    if (ob_get_length()) {
        ob_clean();
    }
    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

$rutaAcceso = __DIR__ . '/../src/AccesoDatos.php';

if (!file_exists($rutaAcceso)) {
    responder(false, $DEBUG ? 'No se encuentra AccesoDatos.php en: ' . $rutaAcceso : 'Error interno del servidor.');
}
require_once $rutaAcceso;

if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'GET'], true)) {
    http_response_code(405);
    responder(false, 'Método no permitido.');
}

$usuarioId = $_SESSION['usuario_id'] ?? null;

if (!is_numeric($usuarioId)) {
    http_response_code(401);
    responder(false, 'Debes iniciar sesión para añadir una carta.');
}
$usuarioId = (int) $usuarioId;

$cartaId = (int) ($_REQUEST['dinosaurio_id'] ?? 0);

if ($cartaId <= 0) {
    responder(false, 'Falta el dinosaurio_id.');
}

try {
    $dino = dinosauriosPorIds([$cartaId])[$cartaId] ?? null;
    if (!$dino) {
        responder(false, 'El dinosaurio indicado no existe.');
    }

    $codigo = PA_AñadirCarta($usuarioId, $cartaId);

    if ($codigo !== 0) {
        $mensajes = [
            -1 => 'El usuario no existe.',
            -2 => 'El dinosaurio no existe.',
        ];
        responder(false, $mensajes[$codigo] ?? ($DEBUG ? 'Código del procedimiento: ' . $codigo : 'No se pudo añadir la carta.'));
    }

    responder(true, 'Carta añadida a tu colección.', ['dinosaurio' => $dino]);

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG ? 'DEBUG: ' . $ex->getMessage() : 'Error inesperado. Inténtalo más tarde.');
}
