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

function finishProcedure(PDO $pdo): void
{
    while ($pdo->nextRowset()) {}
}

function currentUserId(): int
{
    return isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : 0;
}

try {
    $pdo = getPDO();
    $action = $_GET['action'] ?? 'get';

    if ($action === 'all' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query(
            'SELECT id, nombre, especie, periodo, image_url, altura, largo, peso,
                    hp, vigor, ataque, defensa, agilidad
             FROM dinosaurios ORDER BY id ASC'
        );
        jsonResponse(true, 'Catálogo obtenido.', ['dinosaurs' => $stmt->fetchAll()]);
    }

    $userId = currentUserId();
    if ($userId <= 0) {
        jsonResponse(false, 'Debes iniciar sesión.', [], 401);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
        $stmt = $pdo->prepare('CALL ObtenerColeccion(:user_id, @res)');
        $stmt->execute([':user_id' => $userId]);
        $ids = $stmt->fetchAll();
        finishProcedure($pdo);
        $res = (int)$pdo->query('SELECT @res AS res')->fetchColumn();

        if ($res !== 0) {
            jsonResponse(false, 'No se pudo obtener la colección.', [], 500);
        }

        $ids = array_map(static fn(array $row): int => (int)$row['dinosaurio_id'], $ids);
        $dinosaurs = [];

        if ($ids) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare(
                "SELECT id, nombre, especie, periodo, image_url, altura, largo, peso,
                        hp, vigor, ataque, defensa, agilidad
                 FROM dinosaurios
                 WHERE id IN ($placeholders)
                 ORDER BY id ASC"
            );
            $stmt->execute($ids);
            $dinosaurs = $stmt->fetchAll();
        }

        jsonResponse(true, 'Colección obtenida.', [
            'ids' => $ids,
            'dinosaurs' => $dinosaurs
        ]);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
        $input = json_decode(file_get_contents('php://input') ?: '{}', true);
        $cardId = (int)($input['dinosaurio_id'] ?? 0);

        if ($cardId <= 0) {
            jsonResponse(false, 'ID de dinosaurio no válido.', [], 422);
        }

        $stmt = $pdo->prepare('CALL AñadirNuevaCartaAColeccion(:user_id, :card_id, @res)');
        $stmt->execute([
            ':user_id' => $userId,
            ':card_id' => $cardId,
        ]);
        finishProcedure($pdo);
        $res = (int)$pdo->query('SELECT @res AS res')->fetchColumn();

        $messages = [
            -1 => 'El usuario no existe.',
            -2 => 'El dinosaurio no existe.',
            0 => 'Carta añadida a tu colección.'
        ];

        if ($res !== 0) {
            jsonResponse(false, $messages[$res] ?? 'No se pudo añadir la carta.', [], 409);
        }

        jsonResponse(true, $messages[0]);
    }

    jsonResponse(false, 'Acción no encontrada.', [], 404);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonResponse(false, 'Error interno del servidor.', [], 500);
}
