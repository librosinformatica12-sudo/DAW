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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    responder(false, 'Método no permitido.');
}

$usuarioId = $_SESSION['usuario_id'] ?? null;

if (!is_numeric($usuarioId)) {
    http_response_code(401);
    responder(false, 'Debes iniciar sesión para abrir tu sobre.');
}
$usuarioId = (int) $usuarioId;

try {
    $usuario = obtenerUsuarioPorId($usuarioId);
    if (!$usuario) {
        http_response_code(401);
        responder(false, 'Tu sesión no es válida. Vuelve a iniciar sesión.');
    }

    // Regla: una única carta por día (se compara solo la fecha)
    $ultima = $usuario['ultima_obtencion'];
    if ($ultima !== null && date('Y-m-d', strtotime((string) $ultima)) === date('Y-m-d')) {
        responder(false, 'Ya has abierto tu sobre hoy. Vuelve mañana por otra carta.');
    }

    $dino = dinosaurioAleatorio();
    if (!$dino) {
        responder(false, 'No hay dinosaurios disponibles en este momento.');
    }

    $codigo = PA_AñadirCarta($usuarioId, (int) $dino['id']);

    if ($codigo !== 0) {
        $mensajes = [
            -1 => 'El usuario no existe.',
            -2 => 'El dinosaurio no existe.',
        ];
        responder(false, $mensajes[$codigo] ?? ($DEBUG ? 'Código del procedimiento: ' . $codigo : 'No se pudo añadir la carta.'));
    }

    marcarUltimaObtencion($usuarioId);

    // Refrescar los datos guardados en la sesión
    $_SESSION['usuario_datos']['ultima_obtencion'] = date('Y-m-d H:i:s');
    $_SESSION['usuario_datos']['username'] = $usuario['username'];
    $_SESSION['usuario_datos']['email'] = $usuario['email'];
    $_SESSION['usuario_id'] = $usuarioId;

    responder(true, '¡Has conseguido una carta nueva!', ['dinosaurio' => $dino]);

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG ? 'DEBUG: ' . $ex->getMessage() : 'Error inesperado. Inténtalo más tarde.');
}
