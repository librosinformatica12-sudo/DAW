<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';

function jsonResponse(bool $ok, string $message = '', array $data = [], int $status = 200): never
{
    http_response_code($status);
    echo json_encode([
        'ok' => $ok,
        'message' => $message,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function readInput(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw ?: '{}', true);
    return is_array($data) ? $data : [];
}

function finishProcedure(PDO $pdo): void
{
    while ($pdo->nextRowset()) {
        // Limpia posibles rowsets pendientes de un CALL.
    }
}

function passwordCandidates(string $password, string $storedHash): array
{
    // Las filas iniciales del SQL utilizan MD5. Los nuevos registros usan
    // password_hash(). El PA Login compara cadenas, por lo que para un hash
    // moderno primero verificamos la contraseña con PHP y, si es correcta,
    // enviamos al PA exactamente el hash que ya está guardado.
    if (password_verify($password, $storedHash)) {
        return [$storedHash];
    }

    // Compatibilidad con los registros preinsertados del SQL.
    return [md5($password)];
}

try {
    $pdo = getPDO();
    $action = $_GET['action'] ?? '';

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'session') {
        if (!isset($_SESSION['user'])) {
            jsonResponse(true, 'No hay una sesión activa.', ['authenticated' => false]);
        }
        jsonResponse(true, 'Sesión activa.', [
            'authenticated' => true,
            'user' => $_SESSION['user']
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = readInput();

        if ($action === 'register') {
            $username = trim((string)($input['username'] ?? ''));
            $email = trim((string)($input['email'] ?? ''));
            $password = (string)($input['password'] ?? '');
            $passwordConfirm = (string)($input['passwordConfirm'] ?? '');

            if ($username === '' || $email === '' || $password === '') {
                jsonResponse(false, 'Todos los campos son obligatorios.', [], 422);
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                jsonResponse(false, 'El correo electrónico no es válido.', [], 422);
            }
            if ($password !== $passwordConfirm) {
                jsonResponse(false, 'Las contraseñas no coinciden.', [], 422);
            }
            if (mb_strlen($password) < 6) {
                jsonResponse(false, 'La contraseña debe tener al menos 6 caracteres.', [], 422);
            }

            // El PA Registro recibe el hash, no la contraseña en claro.
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('CALL Registro(:username, :email, :password, @res)');
            $stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hash,
            ]);
            finishProcedure($pdo);

            $res = (int)$pdo->query('SELECT @res AS res')->fetchColumn();

            $messages = [
                -1 => 'Username o email vacío.',
                -2 => 'El username o el email ya existe.',
                -3 => 'La contraseña está vacía.',
                0 => 'Cuenta creada correctamente.'
            ];

            if ($res !== 0) {
                jsonResponse(false, $messages[$res] ?? 'No se pudo registrar el usuario.', [], 409);
            }

            jsonResponse(true, $messages[0]);
        }

        if ($action === 'login') {
            $identifier = trim((string)($input['identifier'] ?? ''));
            $password = (string)($input['password'] ?? '');

            if ($identifier === '' || $password === '') {
                jsonResponse(false, 'Identificador y contraseña son obligatorios.', [], 422);
            }

            // BONUS: el mismo campo acepta email o username.
            $findUser = $pdo->prepare(
                'SELECT id, username, email, password_hash, fecha_registro, ultima_obtencion
                 FROM usuarios
                 WHERE email = :identifier OR username = :identifier
                 LIMIT 1'
            );
            $findUser->execute([':identifier' => $identifier]);
            $user = $findUser->fetch();

            if (!$user) {
                jsonResponse(false, 'Usuario/email o contraseña incorrectos.', [], 401);
            }

            $loggedUser = null;

            foreach (passwordCandidates($password, (string)$user['password_hash']) as $candidateHash) {
                $stmt = $pdo->prepare('CALL Login(:email, :password, @res)');
                $stmt->execute([
                    ':email' => $user['email'],
                    ':password' => $candidateHash,
                ]);

                $profile = $stmt->fetch();
                finishProcedure($pdo);
                $res = (int)$pdo->query('SELECT @res AS res')->fetchColumn();

                if ($res === 0 && $profile) {
                    $loggedUser = $profile;
                    break;
                }
            }

            if (!$loggedUser) {
                jsonResponse(false, 'Usuario/email o contraseña incorrectos.', [], 401);
            }

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int)$loggedUser['id'],
                'username' => $loggedUser['username'],
                'email' => $loggedUser['email'],
                'fecha_registro' => $loggedUser['fecha_registro'],
                'ultima_obtencion' => $loggedUser['ultima_obtencion'],
            ];

            jsonResponse(true, 'Inicio de sesión correcto.', [
                'user' => $_SESSION['user']
            ]);
        }

        if ($action === 'logout') {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params['path'], $params['domain'], $params['secure'], $params['httponly']
                );
            }
            session_destroy();
            jsonResponse(true, 'Sesión cerrada.');
        }
    }

    jsonResponse(false, 'Acción no encontrada.', [], 404);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonResponse(false, 'Error interno del servidor.', [], 500);
}
