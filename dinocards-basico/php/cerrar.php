<?php
/* ====================================================================
   cerrar.php
   --------------------------------------------------------------------
   Cierra la sesión del usuario y vuelve a la página de entrada.
   ==================================================================== */

// 1. Iniciar la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Vaciar las variables de sesión
$_SESSION = array();

// 3. Destruir la cookie de sesión en el navegador (si existe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión por completo
session_destroy();

// 5. Redirigir a inicio.php (subiendo un nivel desde la carpeta php/)
header("Location: ../index.php");
exit;
?>