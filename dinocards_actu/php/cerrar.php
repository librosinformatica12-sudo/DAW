<?php
/* ============================================================
   php/cerrar.php
   ------------------------------------------------------------------
   Cierra la sesion y vuelve a la pagina principal.
   (Antes el archivo empezaba con una linea "```php" de Markdown que
   se enviaba al navegador y rompia la redireccion.)
   ============================================================ */

require_once __DIR__ . "/../src/bootstrap.php";

// Vaciar todas las variables de sesión.
$_SESSION = [];

// Eliminar la cookie de sesión del navegador.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión.
session_destroy();

// Volver a la página principal.
header("Location: ../index.php");
exit;
