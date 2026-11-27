<?php
session_start();
require_once __DIR__ . '/config_trade.php';

/*
 * El index.php compartido guarda el usuario en $_SESSION['usuario'].
 * Intentamos recuperar el ID de las claves habituales. Si solo existe username,
 * buscamos su ID en la tabla usuarios.
 */
$sesionUsuario = $_SESSION['usuario'] ?? null;
if (!is_array($sesionUsuario)) {
    http_response_code(401);
    exit('Debes iniciar sesión en DinoCards para intercambiar cartas.');
}

$usuarioId = (int)($sesionUsuario['id'] ?? $sesionUsuario['usuario_id'] ?? $sesionUsuario['user_id'] ?? 0);
if ($usuarioId <= 0 && !empty($sesionUsuario['username'])) {
    $stmt = $db->prepare('SELECT id FROM usuarios WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $sesionUsuario['username']);
    $stmt->execute();
    $filaUsuario = $stmt->get_result()->fetch_assoc();
    $usuarioId = (int)($filaUsuario['id'] ?? 0);
    $stmt->close();
}
if ($usuarioId <= 0) {
    http_response_code(401);
    exit('No se ha podido identificar tu usuario. Comprueba que $_SESSION["usuario"] contiene id o username.');
}

$nombreActual = htmlspecialchars((string)($sesionUsuario['username'] ?? 'Explorador'), ENT_QUOTES, 'UTF-8');

function responder(array $datos, int $codigo = 200): never {
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

function obtenerCartas(mysqli $db, int $idUsuario): array {
    $sql = 'SELECT c.id AS coleccion_id, d.nombre, d.especie, d.periodo, d.image_url,
                   d.hp, d.vigor, d.ataque, d.defensa, d.agilidad
            FROM colecciones c
            INNER JOIN dinosaurios d ON d.id = c.dinosaurio_id
            WHERE c.usuario_id = ?
            ORDER BY c.id';
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $cartas = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $cartas;
}

if (isset($_GET['ajax'])) {
    try {
        $accion = (string)$_GET['ajax'];

        if ($accion === 'usuarios') {
            $stmt = $db->prepare('SELECT id, username FROM usuarios WHERE id <> ? ORDER BY username');
            $stmt->bind_param('i', $usuarioId);
            $stmt->execute();
            $usuarios = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            responder(['ok' => true, 'usuarios' => $usuarios]);
        }

        if ($accion === 'cartas') {
            $otroUsuario = filter_input(INPUT_GET, 'usuario', FILTER_VALIDATE_INT);
            if (!$otroUsuario || $otroUsuario === $usuarioId) {
                responder(['ok' => false, 'mensaje' => 'Selecciona otro explorador.'], 400);
            }

            $stmt = $db->prepare('SELECT id, username FROM usuarios WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $otroUsuario);
            $stmt->execute();
            $existe = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$existe) {
                responder(['ok' => false, 'mensaje' => 'Ese explorador no existe.'], 404);
            }

            responder([
                'ok' => true,
                'misCartas' => obtenerCartas($db, $usuarioId),
                'susCartas' => obtenerCartas($db, (int)$otroUsuario),
                'otroUsuario' => $existe['username']
            ]);
        }

        if ($accion === 'intercambiar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = json_decode(file_get_contents('php://input'), true) ?? [];
            $otroUsuario = (int)($datos['usuario'] ?? 0);
            $misIds = $datos['mis_cartas'] ?? [];
            $susIds = $datos['sus_cartas'] ?? [];

            if ($otroUsuario <= 0 || $otroUsuario === $usuarioId ||
                !is_array($misIds) || !is_array($susIds) ||
                count($misIds) < 1 || count($misIds) !== count($susIds)) {
                responder(['ok' => false, 'mensaje' => 'Debes seleccionar la misma cantidad de cartas en ambos lados.'], 400);
            }

            // Solo aceptamos IDs enteros positivos y evitamos listas manipuladas.
            $misIds = array_values(array_unique(array_filter(array_map('intval', $misIds), fn($id) => $id > 0)));
            $susIds = array_values(array_unique(array_filter(array_map('intval', $susIds), fn($id) => $id > 0)));
            if (count($misIds) !== count($susIds) || count($misIds) !== count($datos['mis_cartas']) ||
                count($susIds) !== count($datos['sus_cartas'])) {
                responder(['ok' => false, 'mensaje' => 'Hay identificadores repetidos o no válidos.'], 400);
            }

            // Verifica que el usuario destinatario existe.
            $stmt = $db->prepare('SELECT id FROM usuarios WHERE id = ?');
            $stmt->bind_param('i', $otroUsuario);
            $stmt->execute();
            $existeOtro = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$existeOtro) {
                responder(['ok' => false, 'mensaje' => 'El otro explorador ya no existe.'], 404);
            }

            // El procedimiento Trade valida también la propiedad real de cada carta
            // y realiza el cambio dentro de una transacción.
            $listaA = implode('_', $misIds);
            $listaB = implode('_', $susIds);
            $stmt = $db->prepare('CALL Trade(?, ?, @resultado_trade)');
            $stmt->bind_param('ss', $listaA, $listaB);
            $stmt->execute();
            while ($stmt->more_results()) {
                $stmt->next_result();
                $extra = $stmt->get_result();
                if ($extra instanceof mysqli_result) {
                    $extra->free();
                }
            }
            $stmt->close();

            $resultado = $db->query('SELECT @resultado_trade AS resultado')->fetch_assoc();
            $codigo = (int)($resultado['resultado'] ?? -9);

            $mensajes = [
                0 => '¡Intercambio realizado! Las cartas ya están en las nuevas colecciones.',
                -1 => 'La selección no es válida o las cantidades no coinciden.',
                -2 => 'Has seleccionado la misma carta en ambos lados.',
                -3 => 'Los usuarios seleccionados no son válidos.',
                -4 => 'Una o varias cartas no pertenecen al usuario indicado.',
                -9 => 'MySQL no pudo completar el intercambio. Revisa el procedimiento Trade.'
            ];
            responder([
                'ok' => $codigo === 0,
                'codigo' => $codigo,
                'mensaje' => $mensajes[$codigo] ?? 'No se pudo realizar el intercambio.'
            ], $codigo === 0 ? 200 : 400);
        }

        responder(['ok' => false, 'mensaje' => 'Acción no reconocida.'], 400);
    } catch (Throwable $e) {
        responder(['ok' => false, 'mensaje' => 'Error del servidor. Comprueba la conexión y el procedimiento Trade.'], 500);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#123b2d">
    <title>DinoCards | Intercambiar cartas</title>
    <link rel="stylesheet" href="trading.css">
    <link rel="stylesheet" href="styles/estilos.css">
</head>
<body>
<div class="trade-shell">
    <header class="topbar">
        <a class="brand" href="index.php"><span class="brand-mark">D</span><span>Dino<span class="brand-light">Cards</span></span></a>
        <div class="topbar-right"><span class="online-dot"></span> EXPLORADOR: <?= $nombreActual ?></div>
    </header>

    <main>
        <a class="back-link" href="index.php">← Volver a mi colección</a>
        <section class="intro">
            <p class="eyebrow">ARCHIVO DE EXPEDICIÓN · 02 / TRADING</p>
            <h1>Mercado de <em>cartas</em></h1>
            <p>Encuentra otro explorador, elige tus cartas y propone un intercambio justo.</p>
        </section>

        <section class="select-panel">
            <label for="explorador">01 / Selecciona al explorador</label>
            <div class="select-row">
                <select id="explorador">
                    <option value="">Cargando exploradores...</option>
                </select>
                <button id="cargar-cartas" class="secondary-button" type="button">Ver sus cartas</button>
            </div>
            <p class="hint">No puedes intercambiar cartas contigo mismo.</p>
        </section>

        <div class="trade-columns">
            <section class="side-panel">
                <div class="panel-heading">
                    <div><span class="step">02 / TU OFERTA</span><h2>Tus cartas</h2></div>
                    <span class="counter" id="contador-mias">0 seleccionadas</span>
                </div>
                <div class="card-grid" id="mis-cartas"><p class="empty">Selecciona un explorador para cargar las cartas.</p></div>
            </section>

            <div class="exchange-mark" aria-hidden="true">⇄</div>

            <section class="side-panel">
                <div class="panel-heading">
                    <div><span class="step">03 / SU OFERTA</span><h2 id="titulo-sus-cartas">Sus cartas</h2></div>
                    <span class="counter" id="contador-suyas">0 seleccionadas</span>
                </div>
                <div class="card-grid" id="sus-cartas"><p class="empty">Las cartas del otro explorador aparecerán aquí.</p></div>
            </section>
        </div>

        <section class="confirm-panel">
            <div>
                <span class="step">04 / CONFIRMACIÓN</span>
                <h2>¿Preparamos el intercambio?</h2>
                <p id="resumen">Selecciona cartas en ambos lados. Debes ofrecer la misma cantidad.</p>
            </div>
            <button id="realizar-intercambio" class="primary-button" type="button" disabled>Realizar intercambio <span>↗</span></button>
        </section>
        <p class="feedback" id="feedback" role="status" aria-live="polite"></p>
    </main>

    <footer>DINOCARDS <span>/</span> EL JURÁSICO NUNCA TERMINÓ</footer>
</div>
<script>
const endpoint = 'intercambiar.php';
const selector = document.getElementById('explorador');
const misCartasEl = document.getElementById('mis-cartas');
const susCartasEl = document.getElementById('sus-cartas');
const btnCargar = document.getElementById('cargar-cartas');
const btnTrade = document.getElementById('realizar-intercambio');
const feedback = document.getElementById('feedback');
let misCartas = [];
let susCartas = [];
let seleccionMia = new Set();
let seleccionSuya = new Set();

function escapar(valor) {
    return String(valor ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function mostrarMensaje(texto, tipo='') {
    feedback.textContent = texto;
    feedback.className = 'feedback ' + tipo;
}
async function pedir(url, opciones={}) {
    const respuesta = await fetch(url, opciones);
    const datos = await respuesta.json();
    if (!respuesta.ok || !datos.ok) throw new Error(datos.mensaje || 'No se pudo completar la petición.');
    return datos;
}
function renderCartas(cartas, contenedor, seleccion, lado) {
    if (!cartas.length) {
        contenedor.innerHTML = '<p class="empty">Este explorador todavía no tiene cartas.</p>';
        return;
    }
    contenedor.innerHTML = cartas.map(carta => {
        const id = Number(carta.coleccion_id);
        const activa = seleccion.has(id);
        const imagen = carta.image_url
            ? `<img src="res/${encodeURIComponent(carta.image_url)}" alt="${escapar(carta.nombre)}">`
            : `<div class="dino-placeholder">D</div>`;
        return `<button type="button" class="dino-card ${activa ? 'selected' : ''}" data-id="${id}" aria-pressed="${activa}">
            <span class="card-number">#${id}</span>${imagen}
            <span class="dino-name">${escapar(carta.nombre)}</span>
            <span class="dino-species">${escapar(carta.especie || 'Especie desconocida')}</span>
            <span class="card-select">${activa ? '✓ Seleccionada' : '＋ Seleccionar'}</span>
        </button>`;
    }).join('');
    contenedor.querySelectorAll('.dino-card').forEach(boton => {
        boton.addEventListener('click', () => {
            const id = Number(boton.dataset.id);
            if (seleccion.has(id)) seleccion.delete(id); else seleccion.add(id);
            renderCartas(cartas, contenedor, seleccion, lado);
            actualizarResumen();
        });
    });
}
function actualizarResumen() {
    document.getElementById('contador-mias').textContent = `${seleccionMia.size} seleccionadas`;
    document.getElementById('contador-suyas').textContent = `${seleccionSuya.size} seleccionadas`;
    const iguales = seleccionMia.size > 0 && seleccionMia.size === seleccionSuya.size;
    btnTrade.disabled = !iguales || !selector.value;
    document.getElementById('resumen').textContent = iguales
        ? `Ofreces ${seleccionMia.size} carta(s) y recibirás ${seleccionSuya.size}.`
        : 'Selecciona la misma cantidad de cartas en ambos lados.';
}
async function cargarUsuarios() {
    try {
        const datos = await pedir(`${endpoint}?ajax=usuarios`);
        selector.innerHTML = '<option value="">Elige un explorador...</option>' +
            datos.usuarios.map(u => `<option value="${Number(u.id)}">${escapar(u.username)}</option>`).join('');
        if (!datos.usuarios.length) selector.innerHTML = '<option value="">No hay otros exploradores</option>';
    } catch (e) {
        selector.innerHTML = '<option value="">No se pudieron cargar los exploradores</option>';
        mostrarMensaje(e.message, 'error');
    }
}
async function cargarCartas() {
    seleccionMia.clear(); seleccionSuya.clear();
    misCartasEl.innerHTML = '<p class="empty">Cargando...</p>';
    susCartasEl.innerHTML = '<p class="empty">Cargando...</p>';
    actualizarResumen();
    if (!selector.value) {
        mostrarMensaje('Selecciona primero a otro explorador.', 'error');
        return;
    }
    try {
        const datos = await pedir(`${endpoint}?ajax=cartas&usuario=${encodeURIComponent(selector.value)}`);
        misCartas = datos.misCartas;
        susCartas = datos.susCartas;
        document.getElementById('titulo-sus-cartas').textContent = `Cartas de ${datos.otroUsuario}`;
        renderCartas(misCartas, misCartasEl, seleccionMia, 'mias');
        renderCartas(susCartas, susCartasEl, seleccionSuya, 'suyas');
        mostrarMensaje('');
        actualizarResumen();
    } catch (e) {
        misCartasEl.innerHTML = '<p class="empty">No se pudieron cargar tus cartas.</p>';
        susCartasEl.innerHTML = '<p class="empty">No se pudieron cargar sus cartas.</p>';
        mostrarMensaje(e.message, 'error');
    }
}
btnCargar.addEventListener('click', cargarCartas);
selector.addEventListener('change', () => {
    misCartas = []; susCartas = [];
    seleccionMia.clear(); seleccionSuya.clear();
    misCartasEl.innerHTML = '<p class="empty">Pulsa «Ver sus cartas».</p>';
    susCartasEl.innerHTML = '<p class="empty">Las cartas aparecerán aquí.</p>';
    actualizarResumen();
    mostrarMensaje('');
});
btnTrade.addEventListener('click', async () => {
    if (btnTrade.disabled) return;
    btnTrade.disabled = true;
    mostrarMensaje('Procesando el intercambio...', '');
    try {
        const datos = await pedir(`${endpoint}?ajax=intercambiar`, {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
                usuario: Number(selector.value),
                mis_cartas: [...seleccionMia],
                sus_cartas: [...seleccionSuya]
            })
        });
        mostrarMensaje(datos.mensaje, 'success');
        await cargarCartas();
    } catch (e) {
        mostrarMensaje(e.message, 'error');
    } finally {
        actualizarResumen();
    }
});
cargarUsuarios();
</script>
</body>
</html>
