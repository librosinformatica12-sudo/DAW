<?php
require_once __DIR__ . '/src/AccesoDatos.php';
pedir_login();
asegurarConexion($conexion);

$usuarioId = (int) $_SESSION['usuario_id'];
$usuarioActual = obtenerUsuarioPorId($usuarioId);
if (!$usuarioActual) {
    session_destroy();
    header('Location: index.php');
    exit;
}
if (empty($_SESSION['csrf_intercambio'])) {
    $_SESSION['csrf_intercambio'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_intercambio'];

function intercambioJson(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function idsValidos($value): array {
    if (!is_array($value) || count($value) < 1 || count($value) > 20) return [];
    $ids = [];
    foreach ($value as $id) {
        if (filter_var($id, FILTER_VALIDATE_INT) === false || (int)$id < 1) return [];
        $ids[] = (int)$id;
    }
    if (count(array_unique($ids)) !== count($ids)) return [];
    return $ids;
}

function cartasUsuario(mysqli $db, int $idUsuario): array {
    $sql = 'SELECT c.id AS coleccion_id, d.id AS dinosaurio_id, d.nombre, d.especie,
                   d.periodo, d.image_url, d.hp, d.ataque, d.defensa, d.agilidad
            FROM colecciones c
            INNER JOIN dinosaurios d ON d.id = c.dinosaurio_id
            WHERE c.usuario_id = ? ORDER BY c.id';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}

if (isset($_GET['ajax'])) {
    try {
        $action = (string)$_GET['ajax'];
        if ($action === 'usuarios' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $conexion->prepare('SELECT id, username FROM usuarios WHERE id <> ? ORDER BY username');
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            intercambioJson(['ok' => true, 'usuarios' => $usuarios]);
        }

        if ($action === 'cartas' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $otroId = filter_input(INPUT_GET, 'usuario', FILTER_VALIDATE_INT);
            if (!$otroId || $otroId === $usuarioId) intercambioJson(['ok'=>false,'mensaje'=>'Selecciona otro usuario.'],400);
            $stmt = $conexion->prepare('SELECT username FROM usuarios WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $otroId);
            $stmt->execute();
            $otro = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$otro) intercambioJson(['ok'=>false,'mensaje'=>'El usuario seleccionado ya no existe.'],404);
            intercambioJson([
                'ok'=>true,
                'otroUsuario'=>$otro['username'],
                'misCartas'=>cartasUsuario($conexion,$usuarioId),
                'susCartas'=>cartasUsuario($conexion,(int)$otroId)
            ]);
        }

        if ($action === 'intercambiar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!is_string($token) || !hash_equals($csrf, $token)) intercambioJson(['ok'=>false,'mensaje'=>'La sesión ha caducado. Recarga la página.'],403);
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data)) intercambioJson(['ok'=>false,'mensaje'=>'Solicitud no válida.'],400);
            $otroId = filter_var($data['usuario'] ?? null, FILTER_VALIDATE_INT);
            $misIds = idsValidos($data['mis_cartas'] ?? null);
            $susIds = idsValidos($data['sus_cartas'] ?? null);
            if (!$otroId || $otroId === $usuarioId || !$misIds || !$susIds || count($misIds) !== count($susIds)) {
                intercambioJson(['ok'=>false,'mensaje'=>'Selecciona el mismo número de cartas en ambos lados (máximo 20).'],400);
            }

            $conexion->begin_transaction();
            try {
                $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE id = ? FOR UPDATE');
                $stmt->bind_param('i', $otroId);
                $stmt->execute();
                $otroExiste = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$otroExiste) throw new RuntimeException('El otro usuario ya no existe.');

                $todos = array_merge($misIds, $susIds);
                $placeholders = implode(',', array_fill(0, count($todos), '?'));
                $types = str_repeat('i', count($todos));
                $sql = "SELECT id, usuario_id FROM colecciones WHERE id IN ($placeholders) ORDER BY id FOR UPDATE";
                $stmt = $conexion->prepare($sql);
                $refs = [$types];
                foreach ($todos as $i => $id) $refs[] = &$todos[$i];
                call_user_func_array([$stmt, 'bind_param'], $refs);
                $stmt->execute();
                $result = $stmt->get_result();
                $owners = [];
                while ($row = $result->fetch_assoc()) $owners[(int)$row['id']] = (int)$row['usuario_id'];
                $stmt->close();

                if (count($owners) !== count($todos)) throw new RuntimeException('Una o varias cartas ya no están disponibles.');
                foreach ($misIds as $id) if (($owners[$id] ?? 0) !== $usuarioId) throw new RuntimeException('Has seleccionado una carta que no pertenece a tu colección.');
                foreach ($susIds as $id) if (($owners[$id] ?? 0) !== (int)$otroId) throw new RuntimeException('Has seleccionado una carta que no pertenece al otro usuario.');

                $update = $conexion->prepare('UPDATE colecciones SET usuario_id = ? WHERE id = ? AND usuario_id = ?');
                foreach ($misIds as $id) {
                    $update->bind_param('iii', $otroId, $id, $usuarioId);
                    $update->execute();
                    if ($update->affected_rows !== 1) throw new RuntimeException('No se pudo transferir una de tus cartas.');
                }
                foreach ($susIds as $id) {
                    $update->bind_param('iii', $usuarioId, $id, $otroId);
                    $update->execute();
                    if ($update->affected_rows !== 1) throw new RuntimeException('No se pudo transferir una de las cartas del otro usuario.');
                }
                $update->close();
                $conexion->commit();
                intercambioJson(['ok'=>true,'mensaje'=>'¡Intercambio realizado! Las cartas ya están en las nuevas colecciones.']);
            } catch (Throwable $e) {
                $conexion->rollback();
                intercambioJson(['ok'=>false,'mensaje'=>$e->getMessage()],400);
            }
        }
        intercambioJson(['ok'=>false,'mensaje'=>'Acción no reconocida.'],400);
    } catch (Throwable $e) {
        error_log('DinoCards intercambio: '.$e->getMessage());
        intercambioJson(['ok'=>false,'mensaje'=>'Error interno al procesar el intercambio.'],500);
    }
}

$nombre = $usuarioActual['username'] ?? 'Coleccionista';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Intercambio</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= (int)@filemtime(__DIR__.'/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= (int)@filemtime(__DIR__.'/styles/paginas.css') ?>">
    <link rel="stylesheet" href="styles/intercambio.css?v=<?= (int)@filemtime(__DIR__.'/styles/intercambio.css') ?>">
    <script src="script/intercambio.js?v=<?= (int)@filemtime(__DIR__.'/script/intercambio.js') ?>" defer></script>
</head>
<body>
<?php require __DIR__ . '/menu.php'; ?>
<main class="panel-inicio intercambio-pagina" data-csrf="<?= escapar($csrf) ?>">
    <section class="panel-hero">
        <p class="avatar-hero" aria-hidden="true"><i class="bx bx-transfer"></i></p>
        <div class="panel-hero-texto">
            <h2>Intercambia cartas, <span><?= escapar($nombre) ?></span></h2>
            <p>Elige otro coleccionista, revisad vuestras cartas y realizad un intercambio directo.</p>
        </div>
    </section>

    <section class="tarjeta intercambio-selector">
        <h2><i class="bx bx-user-circle"></i> Selecciona otro coleccionista</h2>
        <div class="intercambio-controles">
            <label class="sr-only" for="usuario-destino">Usuario con quien intercambiar</label>
            <select id="usuario-destino"><option value="">Cargando usuarios...</option></select>
            <button type="button" class="boton" id="cargar-cartas">Ver colecciones</button>
        </div>
        <p class="intercambio-ayuda">Para confirmar, cada usuario debe ofrecer el mismo número de cartas.</p>
        <p id="mensaje-intercambio" class="intercambio-mensaje" role="status" aria-live="polite"></p>
    </section>

    <div class="intercambio-columnas">
        <section class="tarjeta">
            <div class="intercambio-titulo"><h2>Mis cartas</h2><span id="contador-mias">0 seleccionadas</span></div>
            <div id="mis-cartas" class="intercambio-cartas"><p class="intercambio-vacio">Selecciona un usuario para ver las cartas.</p></div>
        </section>
        <div class="intercambio-flecha" aria-hidden="true"><i class="bx bx-transfer"></i></div>
        <section class="tarjeta">
            <div class="intercambio-titulo"><h2 id="titulo-cartas-otro">Sus cartas</h2><span id="contador-suyas">0 seleccionadas</span></div>
            <div id="sus-cartas" class="intercambio-cartas"><p class="intercambio-vacio">Las cartas del otro coleccionista aparecerán aquí.</p></div>
        </section>
    </div>

    <section class="tarjeta intercambio-confirmar">
        <div><h2>Confirmar intercambio</h2><p id="resumen-intercambio">Selecciona cartas de ambos usuarios para continuar.</p></div>
        <button type="button" class="boton" id="realizar-intercambio" disabled>Realizar intercambio</button>
    </section>
</main>
</body>
</html>
