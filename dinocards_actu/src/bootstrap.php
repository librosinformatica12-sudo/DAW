<?php
/**
 * src/bootstrap.php
 * ------------------------------------------------------------------
 * Arranque comun de DinoCards (sustituye al antiguo AccesoDatos.php,
 * que mezclaba sesion, conexion MySQL y funciones de datos).
 *
 * Que hace:
 *   1. Inicia la sesion con cookie endurecida (HttpOnly, SameSite, Secure).
 *   2. Registra un manejador global de errores: se guarda el detalle en
 *      el log del servidor y el usuario solo ve un mensaje generico.
 *   3. Define helpers de presentacion y seguridad (escapar, login, CSRF).
 *   4. Carga la capa de datos (dal/) y la logica de negocio (src/).
 *
 * NO abre la conexion a MySQL: eso lo hace dal/conexion.php de forma
 * perezosa, solo cuando alguna funcion de dal/ la necesita.
 *
 * Capas del proyecto:
 *   paginas y php/*.php  ->  src/*Service.php  ->  dal/*DAL.php  ->  MySQL
 *   (HTML / endpoints)       (reglas de negocio)   (unico SQL)
 * ------------------------------------------------------------------
 */

// ------------------------------------------------------------
// 1. SESION (cookie endurecida)
// ------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');   // rechaza ids de sesion no generados por el servidor
    ini_set('session.use_only_cookies', '1');  // nunca el id en la URL

    $es_https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $es_https,   // solo por HTTPS cuando el sitio lo usa
        'httponly' => true,        // no accesible desde JavaScript
        'samesite' => 'Lax',       // no se envia en POST desde otros sitios
    ]);
    session_start();
}

// ------------------------------------------------------------
// 2. ERRORES: detalle al log, mensaje generico al usuario
// ------------------------------------------------------------
set_exception_handler(static function (Throwable $e): void {
    error_log(
        '[DinoCards] ' . get_class($e) . ': ' . $e->getMessage()
        . ' en ' . $e->getFile() . ':' . $e->getLine()
    );

    if (!headers_sent()) {
        http_response_code(500);
    }
    echo 'Ha ocurrido un error interno. Inténtalo de nuevo más tarde.';
    exit;
});

// ------------------------------------------------------------
// 3. HELPERS DE PRESENTACION Y SEGURIDAD
// ------------------------------------------------------------

/** Limpia un texto antes de mostrarlo en el HTML (evita XSS). */
function escapar($texto)
{
    return htmlspecialchars((string) ($texto ?? ""), ENT_QUOTES, "UTF-8");
}

/** Alias corto de escapar(). */
function e($texto)
{
    return escapar($texto);
}

/** Devuelve true si el usuario ha iniciado sesion. */
function esta_logueado()
{
    return isset($_SESSION["usuario_id"]);
}

/**
 * Si el usuario no ha iniciado sesion, lo manda a la pagina de entrada
 * (index.php) y detiene la ejecucion.
 */
function pedir_login()
{
    if (!esta_logueado()) {
        header("Location: index.php");
        exit;
    }
}

/** Token CSRF de la sesion actual (se crea la primera vez que se pide). */
function csrf_token(): string
{
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }

    return $_SESSION["csrf_token"];
}

/** Campo oculto con el token CSRF, para meter dentro de un <form method="POST">. */
function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . escapar(csrf_token()) . '">';
}

/** ¿El POST recibido trae un token CSRF valido? */
function csrf_valido(): bool
{
    $enviado = $_POST["csrf_token"] ?? "";

    return is_string($enviado)
        && !empty($_SESSION["csrf_token"])
        && hash_equals($_SESSION["csrf_token"], $enviado);
}

// ------------------------------------------------------------
// 4. CAPAS: datos (dal/) y negocio (src/)
// ------------------------------------------------------------
require_once __DIR__ . '/../dal/conexion.php';
require_once __DIR__ . '/../dal/UsuarioDAL.php';
require_once __DIR__ . '/../dal/ColeccionDAL.php';
require_once __DIR__ . '/../dal/DinosaurioDAL.php';

require_once __DIR__ . '/ReglasColeccion.php';
require_once __DIR__ . '/AuthService.php';
require_once __DIR__ . '/SobreService.php';
