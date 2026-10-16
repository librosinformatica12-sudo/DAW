<?php
/* ============================================================
   php/login.php
   ------------------------------------------------------------------
   Endpoint JSON del inicio de sesion (estilo dino-prueba).
   Devuelve: { "ok": true/false, "mensaje": "..." }
   ============================================================ */

require_once __DIR__ . "/../conexion.php";

header("Content-Type: application/json; charset=utf-8");

function responder(bool $ok, string $mensaje, array $extra = []): void
{
    echo json_encode(array_merge(["ok" => $ok, "mensaje" => $mensaje], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    responder(false, "Metodo no permitido.");
}

$usuario = trim($_POST["usuario"] ?? "");
$clave   = $_POST["contrasena"] ?? "";

if ($usuario === "" || $clave === "") {
    responder(false, "Escribe tu usuario o correo y tu contraseña.");
}

// Buscamos la cuenta (la contrasena esta guardada con md5)
$hash = md5($clave);
$consulta = $conexion->prepare(
    "SELECT id, username FROM usuarios
     WHERE (email = ? OR username = ?) AND password_hash = ?
     LIMIT 1"
);
$consulta->bind_param("sss", $usuario, $usuario, $hash);
$consulta->execute();
$fila = $consulta->get_result()->fetch_assoc();
$consulta->close();

if ($fila) {
    // Nueva sesion segura (como en dino-prueba)
    session_regenerate_id(true);
    $_SESSION["usuario_id"] = (int) $fila["id"];
    $_SESSION["usuario"]    = $fila["username"];
    responder(true, "¡Bienvenido, " . $fila["username"] . "!");
} else {
    responder(false, "Usuario o contraseña incorrectos.");
}
