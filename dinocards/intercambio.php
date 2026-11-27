<?php
/* ============================================================
   inicio.php
   ------------------------------------------------------------------
   Panel principal. Solo presentacion: los datos los pide a los
   servicios de src/ y la logica de abrir los sobres esta en los
   endpoints php/cartaDiaria.php y php/abrirSobrePrueba.php.
   ============================================================ */

require_once __DIR__ . "/src/AccesoDatos.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

// 1) Traigo los ids de los dinosaurios que tengo (puede haber repetidos)
$ids = [];
$consulta = $conexion->prepare("SELECT dinosaurio_id FROM colecciones WHERE usuario_id = ?");
$consulta->bind_param("i", $usuario_id);
$consulta->execute();
$resultado = $consulta->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $ids[] = (int) $fila["dinosaurio_id"];
}
$consulta->close();

// 2) Cuento cuantas copias tengo de cada dinosaurio
$copias = [];
$orden  = [];
foreach ($ids as $id) {
    if (!isset($copias[$id])) {
        $copias[$id] = 0;
        $orden[] = $id;
    }
    $copias[$id]++;
}

// 3) Traigo los datos de esos dinosaurios
$dinos = [];
if (count($orden) > 0) {
    $lista = implode(",", $orden); // son numeros, no hay peligro
    $res = $conexion->query("SELECT * FROM dinosaurios WHERE id IN ($lista)");
    while ($d = $res->fetch_assoc()) {
        $dinos[(int) $d["id"]] = $d;
    }
}

// 4) Calculo el progreso
$total_catalogo = (int) $conexion->query("SELECT COUNT(*) AS total FROM dinosaurios")->fetch_assoc()["total"];
$total_especies = count($orden);
$porcentaje     = $total_catalogo > 0 ? round($total_especies / $total_catalogo * 100) : 0;

$usuarioSesion = $_SESSION["usuario"] ?? null;
$nombre = "Coleccionista";
if (is_array($usuarioSesion)) {
    $nombre = (string) ($usuarioSesion["username"] ?? $usuarioSesion["nombre"] ?? $nombre);
} elseif (is_string($usuarioSesion) && $usuarioSesion !== "") {
    $nombre = $usuarioSesion;
}
$inicial = strtoupper(mb_substr($nombre, 0, 1, "UTF-8"));
if ($inicial === "") {
    $inicial = "?";
}

$dias_semana = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
$meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
$diaSemana = $dias_semana[(int) date("w")];
$mesActual = $meses[(int) date("n") - 1];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Intercambios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">

    <link rel="stylesheet" href="styles/intercambio.css?v=<?= @filemtime(__DIR__ . '/styles/intercambio.css') ?>">
    <script src="script/script.js?v=<?= @filemtime(__DIR__ . '/script/script.js') ?>"></script>
</head>
<body>

<?php require "menu.php"; ?>

<main class="panel-inicio">

    <div class="main-container">
        <div class="tarjeta tarjeta-coleccion">
            <h1>Mi colección</h1>
            <p>Tienes <b><?= $total_especies ?></b> de <b><?= $total_catalogo ?></b> especies (<?= $porcentaje ?>%).</p>

            <?php if ($total_especies === 0): ?>
                <p class="aviso error">Todavía no tienes ninguna carta. Abre tu sobre del día para empezar.</p>
            <?php else: ?>
            <div class="campo">
                <input type="text" id="buscador" oninput="filtrarColeccion()" placeholder="Escribe un dinosaurio...">
            </div>

            <div class="coleccion-scroll">
                <ul class="cartas-grid">
                    <?php foreach ($orden as $id): ?>
                        <?php if (!isset($dinos[$id])) { continue; } $d = $dinos[$id]; ?>
                        <li class="carta-mini" data-buscar="<?= escapar(strtolower($d["especie"] . " " . $d["nombre"])) ?>">
                            <?php if ($copias[$id] > 1): ?>
                                <span class="badge">x<?= $copias[$id] ?></span>
                            <?php endif; ?>
                            <span class="retrato">
                                <?php $img_path = !empty($d["image_url"]) ? "img/img-cartas/" . $d["image_url"] : ""; ?>
                                <?php if ($img_path !== ""): ?>
                                    <img class="fondo" src="<?= escapar($img_path) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                    <img src="<?= escapar($img_path) ?>" alt="<?= escapar($d["nombre"]) ?>" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                <?php else: ?>
                                    <i class="bx bx-dna" aria-hidden="true"></i>
                                <?php endif; ?>
                            </span>
                            <h3><?= escapar($d["nombre"]) ?></h3>
                            <p class="apodo">«<?= escapar($d["especie"]) ?>»</p>
                            <div class="mini-stats">
                                <span><b><?= escapar($d["hp"]) ?></b><small>HP</small></span>
                                <span><b><?= escapar($d["ataque"]) ?></b><small>ATQ</small></span>
                                <span><b><?= escapar($d["defensa"]) ?></b><small>DEF</small></span>
                                <span><b><?= escapar($d["agilidad"]) ?></b><small>AGI</small></span>
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
            </div>

        <?php endif; ?>
        </div>

        <div class="tarjeta panel-intercambio">
            <div class="contenedores-usuario">
                <h2>Intercambio</h2>
                <div id="panel-usuario1" class="sub-panel1">
                
                </div>
                <div id="panel-usuario2" class="sub-panel1">

                </div>
            </div>
        </div>


        <div class="tarjeta panel-chat">

            <h2>Chat</h2>

            <div class="contenedor-chat">

                <div class="mensajes-chat" id="mensajes-chat">
                </div>

                <form class="formulario-chat" id="formulario-chat">

                    <input
                        type="text"
                        id="mensaje-input"
                        placeholder="Escribe un mensaje..."
                        autocomplete="off"
                        required
                    >

                    <button type="submit">
                        <i class="bx bx-send"></i>
                    </button>

                </form>

            </div>

        </div>
</main>


</body>
</html>
