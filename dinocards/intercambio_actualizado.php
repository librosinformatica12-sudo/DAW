<?php
/* intercambio.php - Pantalla de intercambios DinoCards */

require_once __DIR__ . "/src/AccesoDatos.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

/* 1. IDs de dinosaurios del usuario (pueden repetirse). */
$ids = [];
$consulta = $conexion->prepare("SELECT dinosaurio_id FROM colecciones WHERE usuario_id = ?");
$consulta->bind_param("i", $usuario_id);
$consulta->execute();
$resultado = $consulta->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $ids[] = (int) $fila["dinosaurio_id"];
}
$consulta->close();

/* 2. Copias por dinosaurio, conservando el orden. */
$copias = [];
$orden = [];
foreach ($ids as $id) {
    if (!isset($copias[$id])) {
        $copias[$id] = 0;
        $orden[] = $id;
    }
    $copias[$id]++;
}

/* 3. Datos de los dinosaurios de la colección. */
$dinos = [];
if (count($orden) > 0) {
    $lista = implode(",", array_map("intval", $orden));
    $res = $conexion->query("SELECT * FROM dinosaurios WHERE id IN ($lista)");
    if ($res) {
        while ($d = $res->fetch_assoc()) {
            $dinos[(int) $d["id"]] = $d;
        }
    }
}

/* 4. Progreso: especies distintas sobre el total del catálogo. */
$total_catalogo = (int) $conexion->query("SELECT COUNT(*) AS total FROM dinosaurios")->fetch_assoc()["total"];
$total_especies = count($orden);
$porcentaje = $total_catalogo > 0 ? round($total_especies / $total_catalogo * 100) : 0;

/* 5. Nombre del usuario de la sesión. */
$usuarioSesion = $_SESSION["usuario"] ?? null;
$nombre = "Coleccionista";
if (is_array($usuarioSesion)) {
    $nombre = (string) ($usuarioSesion["username"] ?? $usuarioSesion["nombre"] ?? $nombre);
} elseif (is_string($usuarioSesion) && $usuarioSesion !== "") {
    $nombre = $usuarioSesion;
}

if (!function_exists("escapar")) {
    function escapar($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Intercambios</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;700;800&family=IBM+Plex+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Primero los estilos generales (menú, fondo...) y al final los de esta página -->
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/intercambio.css?v=<?= @filemtime(__DIR__ . '/styles/intercambio.css') ?>">

    <script src="script/script.js?v=<?= @filemtime(__DIR__ . '/script/script.js') ?>" defer></script>
    <script src="script/intercambio.js?v=<?= @filemtime(__DIR__ . '/script/intercambio.js') ?>" defer></script>
</head>
<body class="pagina-intercambios">

<?php require __DIR__ . "/menu.php"; ?>

<main class="intercambio">

    <!-- ============ COLUMNA 1: MI COLECCIÓN ============ -->
    <section class="columna col-coleccion">

        <div class="columna-cabecera">
            <h1>Mi colección</h1>
            <p>Tienes <strong><?= $total_especies ?></strong> de <strong><?= $total_catalogo ?></strong> especies (<?= $porcentaje ?>%).</p>
        </div>

        <div class="buscador">
            <i class="bx bx-search" aria-hidden="true"></i>
            <input type="search" id="buscador" placeholder="Escribe un dinosaurio..." autocomplete="off" oninput="filtrarColeccion()">
        </div>

        <div class="coleccion-scroll" id="coleccion-scroll">
            <?php if ($total_especies === 0): ?>
                <p class="aviso">Todavía no tienes cartas. Abre tu sobre del día para empezar.</p>
            <?php else: ?>
                <ul class="cartas-grid" id="cartas-grid">
                    <?php foreach ($orden as $id): ?>
                        <?php
                        if (!isset($dinos[$id])) {
                            continue;
                        }
                        $d = $dinos[$id];
                        $nombreCarta = (string) ($d["nombre"] ?? "Dinosaurio");
                        $especieCarta = (string) ($d["especie"] ?? "");
                        $imgPath = !empty($d["image_url"]) ? "img/img-cartas/" . $d["image_url"] : "";
                        ?>
                        <li class="carta-mini" data-buscar="<?= escapar(mb_strtolower($especieCarta . " " . $nombreCarta, "UTF-8")) ?>">

                            <?php if (($copias[$id] ?? 0) > 1): ?>
                                <span class="badge-copias">x<?= (int) $copias[$id] ?></span>
                            <?php endif; ?>

                            <div class="carta-retrato">
                                <?php if ($imgPath !== ""): ?>
                                    <img src="<?= escapar($imgPath) ?>" alt="<?= escapar($nombreCarta) ?>" loading="lazy"
                                         onerror="this.closest('.carta-retrato').classList.add('sin-imagen'); this.remove();">
                                <?php else: ?>
                                    <i class="bx bx-dna" aria-hidden="true"></i>
                                <?php endif; ?>
                            </div>

                            <div class="carta-datos">
                                <h3><?= escapar($nombreCarta) ?></h3>
                                <p class="carta-especie"><?= escapar($especieCarta) ?></p>
                                <div class="mini-stats">
                                    <span><b><?= escapar($d["hp"] ?? "—") ?></b><small>HP</small></span>
                                    <span><b><?= escapar($d["ataque"] ?? "—") ?></b><small>ATQ</small></span>
                                    <span><b><?= escapar($d["defensa"] ?? "—") ?></b><small>DEF</small></span>
                                    <span><b><?= escapar($d["agilidad"] ?? "—") ?></b><small>AGI</small></span>
                                </div>
                            </div>

                            <button type="button" class="btn-intercambiar" data-trade-card
                                    data-id="<?= (int) $id ?>"
                                    data-nombre="<?= escapar($nombreCarta) ?>"
                                    data-especie="<?= escapar($especieCarta) ?>"
                                    data-imagen="<?= escapar($imgPath) ?>"
                                    data-hp="<?= escapar($d["hp"] ?? "—") ?>"
                                    data-ataque="<?= escapar($d["ataque"] ?? "—") ?>"
                                    data-defensa="<?= escapar($d["defensa"] ?? "—") ?>"
                                    data-agilidad="<?= escapar($d["agilidad"] ?? "—") ?>"
                                    aria-label="Añadir <?= escapar($nombreCarta) ?> al intercambio">
                                <i class="bx bx-plus" aria-hidden="true"></i> Intercambiar
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p id="sin-resultados" class="aviso" hidden>No se ha encontrado ningún dinosaurio.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============ COLUMNA 2: INTERCAMBIO ============ -->
    <section class="columna col-intercambio">

        <div class="columna-cabecera">
            <h2>Intercambio</h2>
            <p>Revisad ambas ofertas antes de confirmar.</p>
        </div>

        <div class="columna-cuerpo">

            <!-- Caja del usuario 1 -->
            <div class="caja-usuario" id="panel-usuario1" data-trade-panel="1">
                <div class="caja-cabecera">
                    <h3><?= escapar($nombre) ?></h3>
                    <span class="contador-cartas" data-count="1">0 cartas</span>
                </div>
                <div class="cartas-intercambio" data-cards="1">
                    <p class="panel-vacio">Tus cartas seleccionadas aparecerán aquí.</p>
                </div>
                <label class="confirmacion">
                    <input type="checkbox" id="aceptar-usuario1" data-accept="1">
                    Aceptar intercambio
                </label>
            </div>

            <!-- Caja del usuario 2 -->
            <div class="caja-usuario" id="panel-usuario2" data-trade-panel="2">
                <div class="caja-cabecera">
                    <h3>Esperando participante</h3>
                    <span class="contador-cartas" data-count="2">0 cartas</span>
                </div>
                <div class="cartas-intercambio" data-cards="2">
                    <p class="panel-vacio">Las cartas del otro usuario aparecerán aquí.</p>
                </div>
                <label class="confirmacion">
                    <input type="checkbox" id="aceptar-usuario2" data-accept="2">
                    Aceptar intercambio
                </label>
            </div>

        </div>

        <div class="estado-intercambio" id="estado-intercambio" role="status" aria-live="polite">
            <i class="bx bx-info-circle" aria-hidden="true"></i>
            <span>Selecciona tus cartas para preparar la oferta.</span>
        </div>
    </section>

    <!-- ============ COLUMNA 3: CHAT ============ -->
    <aside class="columna col-chat">

        <div class="columna-cabecera">
            <h2>Chat</h2>
            <p>Habla sobre el intercambio.</p>
        </div>

        <div class="columna-cuerpo">
            <div class="caja-chat">

                <div class="mensajes-chat" id="mensajes-chat" aria-live="polite">
                    <div class="chat-vacio" id="chat-vacio">
                        <i class="bx bx-conversation" aria-hidden="true"></i>
                        <p>¡Comienza la conversación!</p>
                    </div>
                </div>

                <form class="formulario-chat" id="formulario-chat">
                    <input type="text" id="mensaje-input" name="mensaje" placeholder="Escribe un mensaje..." autocomplete="off" maxlength="500" required>
                    <button type="submit" aria-label="Enviar mensaje"><i class="bx bx-send" aria-hidden="true"></i></button>
                </form>

            </div>
        </div>
    </aside>

</main>

</body>
</html>