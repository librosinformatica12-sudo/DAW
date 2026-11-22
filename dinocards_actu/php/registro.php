<?php
/* ============================================================
   php/registro.php
   ------------------------------------------------------------------
   Endpoint JSON del registro.
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

$resultado = registrarUsuario(
    trim($_POST["usuario"] ?? ""),
    trim($_POST["email"] ?? ""),
    $_POST["contrasena"] ?? "",
    $_POST["confirma"] ?? ""
);

responder($resultado["ok"], $resultado["mensaje"]);
