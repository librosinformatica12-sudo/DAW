<?php
/**
 * api/login.php
 * Inicia sesión. Recibe usuario/email y contraseña por POST.
 * Responde en JSON.
 */
require_once __DIR__ . '/../includes/funciones.php';
require_once __DIR__ . '/../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}

$usuario = trim($_POST['usuario'] ?? '');
$clave   = $_POST['contrasena'] ?? '';

if ($usuario === '' || $clave === '') {
    responder_json(['ok' => false, 'mensaje' => 'Rellena usuario y contraseña.'], 422);
}

try {
    // Se usan dos parámetros distintos porque con prepares nativos
    // no se puede repetir el mismo marcador (:usuario) dos veces.
    $stmt = $pdo->prepare(
        'SELECT id, username, password_hash
         FROM usuarios
         WHERE email = :email OR username = :usuario
         LIMIT 1'
    );
    $stmt->execute([':email' => $usuario, ':usuario' => $usuario]);
    $user = $stmt->fetch();

    if (!$user || !verificar_password($clave, $user['password_hash'])) {
        responder_json(['ok' => false, 'mensaje' => 'Usuario o contraseña incorrectos.'], 401);
    }

    // Guardamos los datos del usuario en la sesión
    session_regenerate_id(true);
    $_SESSION['usuario_id'] = (int) $user['id'];
    $_SESSION['usuario']    = $user['username'];

    responder_json(['ok' => true, 'mensaje' => 'Bienvenido, ' . $user['username'] . '!']);
} catch (PDOException $e) {
    responder_json(['ok' => false, 'mensaje' => 'Error del servidor.'], 500);
}
