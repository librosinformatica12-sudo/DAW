<?php
/**
 * funciones.php
 * ------------------------------------------------------------
 * Funciones de ayuda que se usan en varias páginas y en la API:
 * sesión, escape de HTML y respuestas JSON.
 * ------------------------------------------------------------
 */

// Arrancamos la sesión una sola vez
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escapa un texto para poder mostrarlo en HTML sin peligro. */
function escapar(?string $texto): string
{
    return htmlspecialchars($texto ?? '', ENT_QUOTES, 'UTF-8');
}

/** ¿Hay un usuario con la sesión iniciada? */
function esta_logueado(): bool
{
    return isset($_SESSION['usuario_id']);
}

/** Devuelve el id del usuario actual (o null). */
function usuario_actual(): ?int
{
    return esta_logueado() ? (int) $_SESSION['usuario_id'] : null;
}

/** Si no hay sesión, redirige a la página de entrada. */
function pedir_login(): void
{
    if (!esta_logueado()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Envía una respuesta en JSON y termina la ejecución.
 * Se usa desde los archivos de la carpeta api/.
 */
function responder_json(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Comprueba la contraseña admitiendo también hashes antiguos en md5.
 * Los usuarios nuevos se guardan con password_hash() y se validan con
 * password_verify(); los antiguos (32 caracteres) se validan con md5().
 */
function verificar_password(string $clave, string $hash): bool
{
    if (password_verify($clave, $hash)) {
        return true;
    }
    // Compatibilidad con contraseñas antiguas guardadas en md5
    return strlen($hash) === 32 && hash_equals($hash, md5($clave));
}
