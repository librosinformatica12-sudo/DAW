<?php
/**
 * api/registro.php
 * Crea una cuenta nueva. Valida los datos en el servidor y responde en JSON.
 */
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

$usuario = trim($_POST['usuario'] ?? '');
$email   = trim($_POST['email'] ?? '');
$clave1  = $_POST['contrasena'] ?? '';
$clave2  = $_POST['confirma'] ?? '';

// --- Validación en el servidor ---
$errores = [];
if (mb_strlen($usuario) < 3) {
    $errores[] = 'El usuario debe tener al menos 3 caracteres.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo no es válido.';
}
if (strlen($clave1) < 4) {
    $errores[] = 'La contraseña debe tener al menos 4 caracteres.';
}
if ($clave1 !== $clave2) {
    $errores[] = 'Las contraseñas no coinciden.';
}
if ($errores) {
    responder_json(['ok' => false, 'mensaje' => implode(' ', $errores)], 422);
}

try {
    // ¿Ya existe ese usuario o email?
    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE username = :u OR email = :e LIMIT 1');
    $stmt->execute([':u' => $usuario, ':e' => $email]);
    if ($stmt->fetch()) {
        responder_json(['ok' => false, 'mensaje' => 'Ese usuario o correo ya está registrado.'], 409);
    }

    // Guardamos la contraseña cifrada de forma segura
    $hash = password_hash($clave1, PASSWORD_DEFAULT);
    $ins  = $pdo->prepare(
        'INSERT INTO usuarios (username, email, password_hash, fecha_registro)
         VALUES (:u, :e, :p, NOW())'
    );
    $ins->execute([':u' => $usuario, ':e' => $email, ':p' => $hash]);

    responder_json(['ok' => true, 'mensaje' => 'Registro completado. ¡Ya puedes iniciar sesión!']);
} catch (PDOException $e) {
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
