<?php
// Iniciar la sesión para poder cerrarla correctamente.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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