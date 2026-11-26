<?php
declare(strict_types=1);

require_once __DIR__ . '/src/AccesoDatos.php';
pedir_login();

$usuarioId = (int) $_SESSION['usuario_id'];
$nombreUsuario = (string) ($_SESSION['usuario'] ?? 'Coleccionista');

if (empty($_SESSION['csrf_intercambio'])) {
    $_SESSION['csrf_intercambio'] = bin2hex(random_bytes(32));
}
$csrf = (string) $_SESSION['csrf_intercambio'];

/** Responde a las peticiones AJAX y termina la ejecución. */
function responderIntercambio(array $datos, int $estado = 200): never
{
    http_response_code($estado);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

/** Convierte una lista recibida en IDs positivos, únicos y válidos. */
function validarIdsIntercambio(mixed $ids): array
{
    if (!is_array($ids) || $ids === []) {
        return [];
    }

    $resultado = [];
    foreach ($ids as $id) {
        if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
            return [];
        }
        $id = (int) $id;
        if ($id < 1 || isset($resultado[$id])) {
            return [];
        }
        $resultado[$id] = $id;
    }
    return array_values($resultado);
}

/** Obtiene cartas físicas de la tabla colecciones (no solo especies). */
function obtenerCartasIntercambio(mysqli $db, int $idUsuario): array
{
    $sql = 'SELECT c.id AS coleccion_id, d.id AS dinosaurio_id, d.nombre,
                   d.especie, d.periodo, d.image_url, d.hp, d.vigor,
                   d.ataque, d.defensa, d.agilidad
            FROM colecciones c
            INNER JOIN dinosaurios d ON d.id = c.dinosaurio_id
            WHERE c.usuario_id = ?
            ORDER BY c.id';

    $stmt = $db->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('No se pudo preparar la consulta de cartas.');
    }
    $stmt->bind_param('i', $idUsuario);
    if (!$stmt->execute()) {
        $stmt->close();
        throw new RuntimeException('No se pudo consultar la colección.');
    }
    $resultado = $stmt->get_result();
    $cartas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    if ($resultado) {
        $resultado->free();
    }
    $stmt->close();
    return $cartas;
}

/* API AJAX de la propia página: no requiere una segunda conexión ni otra sesión. */
if (isset($_GET['ajax'])) {
    try {
        if (!$connection instanceof mysqli || $connection->connect_errno) {
            responderIntercambio(['ok' => false, 'mensaje' => 'No se pudo conectar con la base de datos.'], 500);
        }

        $accion = (string) $_GET['ajax'];

        if ($accion === 'usuarios' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $stmt = $connection->prepare(
                'SELECT id, username FROM usuarios WHERE id <> ? ORDER BY username'
            );
            if (!$stmt) {
                throw new RuntimeException('No se pudo preparar la lista de usuarios.');
            }
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $usuarios = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
            if ($resultado) $resultado->free();
            $stmt->close();
            responderIntercambio(['ok' => true, 'usuarios' => $usuarios]);
        }

        if ($accion === 'cartas' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $otroUsuario = filter_input(INPUT_GET, 'usuario', FILTER_VALIDATE_INT);
            if (!$otroUsuario || $otroUsuario === $usuarioId) {
                responderIntercambio(['ok' => false, 'mensaje' => 'Selecciona otro coleccionista.'], 400);
            }

            $stmt = $connection->prepare('SELECT username FROM usuarios WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $otroUsuario);
            $stmt->execute();
            $otro = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$otro) {
                responderIntercambio(['ok' => false, 'mensaje' => 'El coleccionista seleccionado ya no existe.'], 404);
            }

            responderIntercambio([
                'ok' => true,
                'misCartas' => obtenerCartasIntercambio($connection, $usuarioId),
                'susCartas' => obtenerCartasIntercambio($connection, (int) $otroUsuario),
                'otroUsuario' => (string) $otro['username']
            ]);
        }

        if ($accion === 'intercambiar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
            if ($token === '' || !hash_equals($csrf, $token)) {
                responderIntercambio(['ok' => false, 'mensaje' => 'La sesión ha caducado. Recarga la página e inténtalo de nuevo.'], 403);
            }

            $datos = json_decode((string) file_get_contents('php://input'), true);
            if (!is_array($datos)) {
                responderIntercambio(['ok' => false, 'mensaje' => 'La petición no contiene datos válidos.'], 400);
            }

            $otroUsuario = filter_var($datos['usuario'] ?? null, FILTER_VALIDATE_INT);
            $misIds = validarIdsIntercambio($datos['mis_cartas'] ?? null);
            $susIds = validarIdsIntercambio($datos['sus_cartas'] ?? null);

            if (!$otroUsuario || $otroUsuario === $usuarioId ||
                $misIds === [] || $susIds === [] ||
                count($misIds) !== count($susIds) ||
                count($misIds) !== count($datos['mis_cartas'] ?? []) ||
                count($susIds) !== count($datos['sus_cartas'] ?? [])) {
                responderIntercambio([
                    'ok' => false,
                    'mensaje' => 'Selecciona la misma cantidad de cartas, sin repetir ninguna.'
                ], 400);
            }

            $stmt = $connection->prepare('SELECT id FROM usuarios WHERE id = ?');
            $stmt->bind_param('i', $otroUsuario);
            $stmt->execute();
            $otroExiste = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$otroExiste) {
                responderIntercambio(['ok' => false, 'mensaje' => 'El otro coleccionista ya no existe.'], 404);
            }

            /* Bloqueamos las cartas seleccionadas y comprobamos su propietario real. */
            $todosIds = array_merge($misIds, $susIds);
            $placeholders = implode(',', array_fill(0, count($todosIds), '?'));
            $tipos = str_repeat('i', count($todosIds));
            $sql = "SELECT id, usuario_id FROM colecciones WHERE id IN ($placeholders) FOR UPDATE";

            $connection->begin_transaction();
            try {
                $stmt = $connection->prepare($sql);
                if (!$stmt) throw new RuntimeException('No se pudo validar la selección.');
                $stmt->bind_param($tipos, ...$todosIds);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $propietarios = [];
                while ($fila = $resultado->fetch_assoc()) {
                    $propietarios[(int) $fila['id']] = (int) $fila['usuario_id'];
                }
                $resultado->free();
                $stmt->close();

                if (count($propietarios) !== count($todosIds)) {
                    throw new DomainException('Una o varias cartas ya no están disponibles.');
                }
                foreach ($misIds as $id) {
                    if (($propietarios[$id] ?? 0) !== $usuarioId) {
                        throw new DomainException('Una de tus cartas ya no pertenece a tu colección.');
                    }
                }
                foreach ($susIds as $id) {
                    if (($propietarios[$id] ?? 0) !== $otroUsuario) {
                        throw new DomainException('Una de las cartas seleccionadas ya no pertenece al otro coleccionista.');
                    }
                }

                /* Se actualizan los registros físicos de colecciones, no las especies. */
                $sqlUpdate = 'UPDATE colecciones SET usuario_id = ? WHERE id IN (' .
                    implode(',', array_fill(0, count($misIds), '?')) . ')';
                $stmt = $connection->prepare($sqlUpdate);
                $params = array_merge([$otroUsuario], $misIds);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();
                if ($stmt->affected_rows !== count($misIds)) {
                    $stmt->close();
                    throw new RuntimeException('No se pudieron transferir todas tus cartas.');
                }
                $stmt->close();

                $sqlUpdate = 'UPDATE colecciones SET usuario_id = ? WHERE id IN (' .
                    implode(',', array_fill(0, count($susIds), '?')) . ')';
                $stmt = $connection->prepare($sqlUpdate);
                $params = array_merge([$usuarioId], $susIds);
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();
                if ($stmt->affected_rows !== count($susIds)) {
                    $stmt->close();
                    throw new RuntimeException('No se pudieron recibir todas las cartas.');
                }
                $stmt->close();

                $connection->commit();
            } catch (Throwable $e) {
                $connection->rollback();
                throw $e;
            }

            responderIntercambio(['ok' => true, 'mensaje' => '¡Intercambio realizado! Las cartas ya están en las nuevas colecciones.']);
        }

        responderIntercambio(['ok' => false, 'mensaje' => 'Acción no reconocida.'], 400);
    } catch (DomainException $e) {
        responderIntercambio(['ok' => false, 'mensaje' => $e->getMessage()], 409);
    } catch (Throwable $e) {
        error_log('[DinoCards intercambio] ' . $e->__toString());
        responderIntercambio(['ok' => false, 'mensaje' => 'No se pudo completar el intercambio. Comprueba la base de datos e inténtalo de nuevo.'], 500);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Intercambio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= (int) @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= (int) @filemtime(__DIR__ . '/styles/paginas.css') ?>">
    <link rel="stylesheet" href="styles/intercambio.css?v=<?= (int) @filemtime(__DIR__ . '/styles/intercambio.css') ?>">
    <script src="script/intercambio.js?v=<?= (int) @filemtime(__DIR__ . '/script/intercambio.js') ?>" defer></script>
</head>
<body>
<?php require __DIR__ . '/menu.php'; ?>

<main class="panel-inicio intercambio-pagina">
    <section class="intercambio-cabecera">
        <div>
            <p class="intercambio-eyebrow">DINOCARDS · MERCADO DE COLECCIONISTAS</p>
            <h1>Intercambio de <span>cartas</span></h1>
            <p>Elige otro coleccionista, selecciona las cartas y realiza el intercambio.</p>
        </div>
        <a class="intercambio-volver" href="coleccion.php"><i class="bx bx-collection"></i> Mi colección</a>
    </section>

    <section class="intercambio-selector tarjeta">
        <label for="explorador">Selecciona al coleccionista</label>
        <div class="intercambio-selector-fila">
            <select id="explorador">
                <option value="">Cargando coleccionistas...</option>
            </select>
            <button type="button" id="cargar-cartas" class="btn-intercambio-secundario">Ver sus cartas</button>
        </div>
        <small>No puedes intercambiar cartas contigo mismo. Se intercambian cartas físicas de la colección.</small>
    </section>

    <div class="intercambio-columnas">
        <section class="tarjeta intercambio-panel">
            <header class="intercambio-panel-titulo">
                <div><small>01 · TU OFERTA</small><h2>Tus cartas</h2></div>
                <span id="contador-mias">0 seleccionadas</span>
            </header>
            <div class="intercambio-cartas" id="mis-cartas"><p class="intercambio-vacio">Selecciona un coleccionista para cargar las cartas.</p></div>
        </section>

        <div class="intercambio-simbolo" aria-hidden="true"><i class="bx bx-transfer-alt"></i></div>

        <section class="tarjeta intercambio-panel">
            <header class="intercambio-panel-titulo">
                <div><small>02 · SU OFERTA</small><h2 id="titulo-sus-cartas">Sus cartas</h2></div>
                <span id="contador-suyas">0 seleccionadas</span>
            </header>
            <div class="intercambio-cartas" id="sus-cartas"><p class="intercambio-vacio">Las cartas del otro coleccionista aparecerán aquí.</p></div>
        </section>
    </div>

    <section class="intercambio-confirmacion tarjeta">
        <div><small>03 · CONFIRMACIÓN</small><h2>¿Preparamos el intercambio?</h2><p id="resumen-intercambio">Selecciona la misma cantidad de cartas en ambos lados.</p></div>
        <button type="button" id="realizar-intercambio" class="btn-intercambio-principal" disabled>Realizar intercambio <i class="bx bx-right-arrow-alt"></i></button>
    </section>
    <p id="feedback-intercambio" class="intercambio-feedback" role="status" aria-live="polite"></p>
</main>

<script>
window.DINOCARDS_INTERCAMBIO = {
    endpoint: 'intercambio.php',
    csrf: <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    imagenes: 'img/img-cartas/'
};
</script>
</body>
</html>
