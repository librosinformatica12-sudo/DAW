<?php

ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    if (ob_get_length()) {
        ob_clean();
    }

    echo json_encode(array_merge(['ok' => $ok, 'mensaje' => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function idUsuarioSesion(): int
{
    $usuarioId = $_SESSION['usuario_id'] ?? null;
    if (!is_numeric($usuarioId) || (int) $usuarioId < 1) {
        http_response_code(401);
        responder(false, 'Debes iniciar sesión para continuar.');
    }

    return (int) $usuarioId;
}

function ejecutarEndpoint(callable $accion): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        responder(false, 'Método no permitido.');
    }

    try {
        $accion();
    } catch (Throwable $ex) {
        error_log($ex->getMessage());
        http_response_code(500);
        responder(false, 'Error inesperado. Inténtalo más tarde.');
    }
}
