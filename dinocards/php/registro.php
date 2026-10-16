<?php
/* ============================================================
   php/registro.php
   ------------------------------------------------------------------
   Endpoint JSON del registro (estilo dino-prueba).
   Devuelve: { "ok": true/false, "mensaje": "..." }
   ============================================================ */

require_once __DIR__ . "/../src/AccesoDatos.php";
require_once __DIR__ . "/../src/Respuesta.php";

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    responder(false, "Metodo no permitido.");
}

$usuario = trim($_POST["usuario"] ?? "");
$email   = trim($_POST["email"] ?? "");
$clave1  = $_POST["contrasena"] ?? "";
$clave2  = $_POST["confirma"] ?? "";

if ($usuario === "" || $email === "" || $clave1 === "") {
    responder(false, "Rellena todos los campos.");
}
if (strlen($usuario) < 3) {
    responder(false, "El usuario debe tener al menos 3 caracteres.");
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, "El correo no es válido.");
}
if (strlen($clave1) < 4) {
    responder(false, "La contraseña debe tener al menos 4 caracteres.");
}
if ($clave1 !== $clave2) {
    responder(false, "Las contraseñas no coinciden.");
}

// Miramos si ese usuario o email ya existen
$consulta = $conexion->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
$consulta->bind_param("ss", $usuario, $email);
$consulta->execute();
$consulta->store_result();

if ($consulta->num_rows > 0) {
    $consulta->close();
    responder(false, "Ese usuario o correo ya está registrado.");
}
$consulta->close();

// Guardamos la contraseña cifrada de forma segura (password_hash)
$hash = password_hash($clave1, PASSWORD_DEFAULT);
$alta = $conexion->prepare(
    "INSERT INTO usuarios (username, email, password_hash, fecha_registro)
     VALUES (?, ?, ?, NOW())"
);
$alta->bind_param("sss", $usuario, $email, $hash);

if ($alta->execute()) {
    $alta->close();
    responder(true, "Registro completado. ¡Ya puedes iniciar sesión!");
} else {
    $alta->close();
    responder(false, "No se pudo completar el registro.");
}
