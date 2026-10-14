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
    responder(false, 'Debes iniciar sesión para abrir el sobre de prueba.');
}
$usuarioId = (int) $usuarioId;

$ESPERA   = 300; // 5 minutos. Debe coincidir con $esperaPrueba de inicio.php
$CUANTAS  = 4;   // nº de cartas distintas por sobre

try {
    $usuario = obtenerUsuarioPorId($usuarioId);
    if (!$usuario) {
        http_response_code(401);
        responder(false, 'Tu sesión no es válida. Vuelve a iniciar sesión.');
    }

    // Regla: esperar $ESPERA segundos desde la última apertura
    $ultima = $usuario['carta_prueba'] ?? null;
    if ($ultima !== null) {
        $pasados = time() - strtotime((string) $ultima);
        $faltan  = $ESPERA - $pasados;
        if ($faltan > 0) {
            responder(false, 'Aún no puedes abrirlo. Vuelve en unos minutos.', ['espera' => $faltan]);
        }
    }

    // 4 dinosaurios distintos al azar
    $dinos = dinosauriosAleatorios($CUANTAS);
    if (count($dinos) < $CUANTAS) {
        responder(false, 'No hay dinosaurios suficientes en el catálogo.');
    }

    // Guardar las cartas (sin tocar ultima_obtencion del sobre diario)
    foreach ($dinos as $d) {
        insertarCarta($usuarioId, (int) $d['id']);
    }

    marcarCartaPrueba($usuarioId);

    responder(true, '¡Has conseguido ' . $CUANTAS . ' cartas nuevas!', [
        'dinosaurios' => $dinos,
        'espera'      => $ESPERA,
    ]);

} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responder(false, $DEBUG ? 'DEBUG: ' . $ex->getMessage() : 'Error inesperado. Inténtalo más tarde.');
}
