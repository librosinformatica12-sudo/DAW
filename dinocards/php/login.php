<?php
/* ============================================================
   php/login.php
   ------------------------------------------------------------------
   Endpoint JSON del inicio de sesion (estilo dino-prueba).
   Devuelve: { "ok": true/false, "mensaje": "..." }
   ============================================================ */

require_once __DIR__ . "/../src/AccesoDatos.php";
require_once __DIR__ . "/../src/Respuesta.php";

header("Content-Type: application/json; charset=utf-8");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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

// Buscamos la cuenta por usuario o correo. La contrasena se comprueba
// despues, para poder aceptar tambien los hashes antiguos (md5).
$consulta = $conexion->prepare(
    "SELECT id, username, password_hash FROM usuarios
     WHERE email = ? OR username = ?
     LIMIT 1"
);
$consulta->bind_param("ss", $usuario, $usuario);
$consulta->execute();
$fila = $consulta->get_result()->fetch_assoc();
$consulta->close();

$correcto = false;

if ($fila) {
    $guardado = (string) $fila["password_hash"];

    if (password_verify($clave, $guardado)) {
        // Contrasena moderna (password_hash).
        $correcto = true;
    } elseif (strlen($guardado) === 32 && hash_equals($guardado, md5($clave))) {
        // Contrasena antigua guardada con md5: la aceptamos y de paso
        // la actualizamos al formato nuevo (password_hash).
        $correcto = true;
        $nuevo = password_hash($clave, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $actualizar->bind_param("si", $nuevo, $fila["id"]);
        $actualizar->execute();
        $actualizar->close();
    }
}

if ($correcto) {
    // Nueva sesion segura (como en dino-prueba)
    session_regenerate_id(true);
    $_SESSION["usuario_id"] = (int) $fila["id"];
    $_SESSION["usuario"]    = $fila["username"];
    responder(true, "¡Bienvenido, " . $fila["username"] . "!");
} else {
    responder(false, "Usuario o contraseña incorrectos.");
}
