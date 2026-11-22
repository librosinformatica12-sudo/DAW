<?php
/* ============================================================
   php/login.php
   ------------------------------------------------------------------
   Endpoint JSON del inicio de sesion.
   Devuelve: { "ok": true/false, "mensaje": "..." }

   Controlador fino: valida la peticion y delega en
   src/AuthService.php (la consulta a MySQL esta en dal/UsuarioDAL.php).
   ============================================================ */

require_once __DIR__ . "/../src/bootstrap.php";
require_once __DIR__ . "/../src/Respuesta.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    responder(false, "Metodo no permitido.");
}

if (!csrf_valido()) {
    http_response_code(403);
    responder(false, "La página ha caducado. Recárgala e inténtalo de nuevo.");
}

$usuario = trim($_POST["usuario"] ?? "");
$clave   = $_POST["contrasena"] ?? "";

if ($usuario === "" || $clave === "") {
    responder(false, "Escribe tu usuario o correo y tu contraseña.");
}

$cuenta = autenticarUsuario($usuario, $clave);

if ($cuenta) {
    // Nueva sesion segura (evita fijacion de sesion)
    session_regenerate_id(true);
    $_SESSION["usuario_id"] = $cuenta["id"];
    $_SESSION["usuario"]    = $cuenta["username"];
    responder(true, "¡Bienvenido, " . $cuenta["username"] . "!");
} else {
    responder(false, "Usuario o contraseña incorrectos.");
}
