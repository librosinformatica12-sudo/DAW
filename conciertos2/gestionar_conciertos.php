<?php
session_start();
require_once 'AccesoDatos.php';

$db = AccesoDatos::getModelo();

// Mensajes según el código _res devuelto por cada procedure
const MSG_ALTA_CONCIERTO = [
    0  => 'Concierto dado de alta correctamente.',
    -1 => 'El nombre de la banda no puede estar vacío.',
    -2 => 'Debes indicar la fecha del concierto.',
    -3 => 'La fecha del concierto debe ser posterior a hoy.',
    -4 => 'El precio es obligatorio y no puede ser negativo.',
    -5 => 'Ya existe un concierto de esa banda en esa fecha.',
];
const MSG_ALTA_ENTRADA = [
    0  => 'Entrada registrada correctamente.',
    -1 => 'Debes seleccionar un concierto.',
    -2 => 'El concierto seleccionado no existe.',
    -3 => 'Ese concierto ya se ha celebrado.',
];

/**
 * Sincroniza la SESSION con lo que hay REALMENTE en la BD.
 * Se llama tras cada escritura correcta y en cada carga de página.
 */
function sincronizarSesion(AccesoDatos $db): void
{
    $_SESSION['conciertos'] = $db->getConciertos();
    $_SESSION['entradas']   = $db->getEntradas();
}

function h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// ---------- PROCESAR FORMULARIOS (POST) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    try {
        if ($accion === 'alta_concierto') {
            $banda  = trim($_POST['nombre_banda'] ?? '');
            $fecha  = $_POST['fecha_concierto'] ?? '';
            $precio = $_POST['precio'] ?? '';

            $f = DateTime::createFromFormat('Y-m-d', $fecha);
            $fecha = ($f && $f->format('Y-m-d') === $fecha) ? $fecha : null;
            $precio = is_numeric($precio) ? (float) $precio : null;

            // 1º BD (el procedure valida), 2º sesión solo si res == 0
            $res = $db->addConcierto($banda, $fecha, $precio);
            if ($res === 0) {
                sincronizarSesion($db);
            }
            $_SESSION['flash'] = [$res === 0 ? 'ok' : 'error',
                                  MSG_ALTA_CONCIERTO[$res] ?? 'Error desconocido.'];

        } elseif ($accion === 'vender_entrada') {
            $cid = filter_var($_POST['concierto_id'] ?? '', FILTER_VALIDATE_INT);
            $res = $db->addEntrada($cid === false ? null : $cid);
            if ($res === 0) {
                sincronizarSesion($db);
            }
            $_SESSION['flash'] = [$res === 0 ? 'ok' : 'error',
                                  MSG_ALTA_ENTRADA[$res] ?? 'Error desconocido.'];
        }
    } catch (PDOException $e) {
        $_SESSION['flash'] = ['error', 'Error de base de datos. Inténtalo de nuevo.'];
    }
    // Post/Redirect/Get: evita reenviar el formulario al pulsar F5
    header('Location: gestionar_conciertos.php');
    exit;
}

// ---------- GET: refrescar sesión desde la BD ----------
sincronizarSesion($db);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$conciertos = $_SESSION['conciertos'];
$entradas   = $_SESSION['entradas'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de conciertos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main>
    <h1>Gestión de conciertos</h1>
    <p><a href="index.html">&larr; Volver al inicio</a></p>

    <?php if ($flash): ?>
        <p class="msg <?= h($flash[0]) ?>"><?= h($flash[1]) ?></p>
    <?php endif; ?>

    <section>
        <h2>Alta de concierto</h2>
        <form method="post" action="gestionar_conciertos.php">
            <input type="hidden" name="accion" value="alta_concierto">
            <label>Banda
                <input type="text" name="nombre_banda" maxlength="100">
            </label>
            <label>Fecha
                <input type="date" name="fecha_concierto">
            </label>
            <label>Precio (€)
                <input type="number" name="precio" min="0" step="0.01">
            </label>
            <button type="submit">Guardar concierto</button>
        </form>
    </section>

    <section>
        <h2>Venta de entrada</h2>
        <?php if (!$conciertos): ?>
            <p>No hay conciertos todavía.</p>
        <?php else: ?>
        <form method="post" action="gestionar_conciertos.php">
            <input type="hidden" name="accion" value="vender_entrada">
            <label>Concierto
                <select name="concierto_id">
                    <?php foreach ($conciertos as $c): ?>
                        <option value="<?= (int) $c['id_concierto'] ?>">
                            <?= h($c['nombre_banda']) ?> — <?= h($c['fecha_concierto']) ?>
                            (<?= number_format((float) $c['precio'], 2, ',', '.') ?> €)
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Registrar venta</button>
        </form>
        <?php endif; ?>
    </section>

    <section>
        <h2>Conciertos (<?= count($conciertos) ?>)</h2>
        <table>
            <tr><th>ID</th><th>Banda</th><th>Fecha</th><th>Precio</th></tr>
            <?php foreach ($conciertos as $c): ?>
                <tr>
                    <td><?= (int) $c['id_concierto'] ?></td>
                    <td><?= h($c['nombre_banda']) ?></td>
                    <td><?= h($c['fecha_concierto']) ?></td>
                    <td><?= number_format((float) $c['precio'], 2, ',', '.') ?> €</td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>

    <section>
        <h2>Entradas vendidas (<?= count($entradas) ?>)</h2>
        <table>
            <tr><th>ID</th><th>Adquirida</th><th>Concierto</th><th>Fecha concierto</th><th>Precio</th></tr>
            <?php foreach ($entradas as $e): ?>
                <tr>
                    <td><?= (int) $e['id_entrada'] ?></td>
                    <td><?= h($e['fecha_adquisicion']) ?></td>
                    <td><?= h($e['nombre_banda']) ?></td>
                    <td><?= h($e['fecha_concierto']) ?></td>
                    <td><?= number_format((float) $e['precio'], 2, ',', '.') ?> €</td>
                </tr>
            <?php endforeach; ?>
        </table>
    </section>
</main>
</body>
</html>
