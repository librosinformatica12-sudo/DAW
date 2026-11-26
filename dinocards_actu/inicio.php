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

// --- Resultado del ultimo sobre abierto (lo deja el endpoint en sesion) ---
$mensaje       = "";
$carta_nueva   = null;
$cartas_prueba = [];

if (isset($_SESSION["sobre_flash"])) {
    $flash = $_SESSION["sobre_flash"];
    unset($_SESSION["sobre_flash"]);

    $mensaje       = $flash["mensaje"] ?? "";
    $carta_nueva   = $flash["carta"]   ?? null;
    $cartas_prueba = $flash["cartas"]  ?? [];
}

// --- Datos del usuario ---
$usuario = obtenerUsuarioPorId($usuario_id);
if (!$usuario) {
    header("Location: index.php");
    exit;
}

// --- Espera del sobre del día (24 horas desde la última apertura) ---
$espera_dia    = 24 * 60 * 60;
$faltan_dia    = segundosRestantes($usuario["ultima_obtencion"] ?? null, $espera_dia);
$ya_cogida_hoy = ($faltan_dia > 0);

// --- Espera del sobre de prueba (2 minutos) ---
$espera_prueba = 120;
$faltan_prueba = segundosRestantes($usuario["carta_prueba"] ?? null, $espera_prueba);
$prueba_listo  = ($faltan_prueba === 0);

// --- Resumen de la coleccion ---
$resumen        = resumenColeccion($usuario_id);
$total_cartas   = $resumen["cartas"];
$total_especies = $resumen["especies"];
$total_catalogo = contarDinosaurios();
$porcentaje     = $total_catalogo > 0 ? round($total_especies / $total_catalogo * 100) : 0;

// --- Datos para el banner de bienvenida (estilo dino-prueba) ---
$nombre = $usuario["username"] ?? "Coleccionista";

$inicial = strtoupper(mb_substr($nombre, 0, 1, "UTF-8"));
if ($inicial === "") {
    $inicial = "?";
}

$dias_semana = [
    "Domingo", "Lunes", "Martes", "Miércoles",
    "Jueves", "Viernes", "Sábado",
];
$meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre",
];
$diaSemana = $dias_semana[(int) date("w")];
$mesActual = $meses[(int) date("n") - 1];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Inicio</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">
</head>
<body>

<?php require "menu.php"; ?>

<main class="panel-inicio">

    <!-- Banner de bienvenida (estilo dino-prueba) -->
    <header class="panel-hero">
        <p class="avatar-hero" aria-hidden="true"><?= escapar($inicial) ?></p>

        <div class="panel-hero-texto">
            <h2>Hola, <span><?= escapar($nombre) ?></span></h2>
            <p>Abre tu sobre del día y sigue completando tu colección de dinosaurios.</p>
        </div>

        <time class="panel-fecha" datetime="<?= escapar(date("Y-m-d")) ?>">
            <small><?= escapar($diaSemana ?? "") ?></small>
            <b><?= escapar(date("j")) ?></b>
            <span><?= escapar($mesActual ?? "") ?> <?= escapar(date("Y")) ?></span>
        </time>
    </header>

    <?php if ($mensaje !== ""): ?>
        <p class="aviso ok"><?= escapar($mensaje) ?></p>
    <?php endif; ?>
    
    <!-- Sobres: el del día a la izquierda y el de prueba a la derecha -->
    <div class="sobres-fila">

        <!-- Sobre del día -->
        <div class="tarjeta sobre-card">
            <figure class="sobre-card-figura">
                <img src="img/img-sobres/sobre.png" alt="Sobre del día" loading="lazy" decoding="async">
            </figure>
            <div class="sobre-card-texto">
                <h2>Sobre del día</h2>
                <p>Consigue un dinosaurio nuevo gratis. Se puede abrir cada 24 horas.</p>
                <form method="POST" action="php/cartaDiaria.php">
                    <button type="submit" class="boton"
                            data-espera="<?= (int) $faltan_dia ?>"
                            data-label="Abrir sobre del día"
                            <?= $ya_cogida_hoy ? "disabled" : "" ?>>
                        <?= $ya_cogida_hoy ? "Sobre en espera" : "Abrir sobre del día" ?>
                    </button>
                </form>
            </div>
        </div>

        <!-- Sobre de prueba -->
        <div class="tarjeta sobre-card">
            <figure class="sobre-card-figura">
                <img src="img/img-sobres/sobre2.png" alt="Sobre de prueba" loading="lazy" decoding="async">
            </figure>
            <div class="sobre-card-texto">
                <h2>Sobre de prueba</h2>
                <p>Consigue 4 cartas de golpe. Debes esperar 2 minutos entre aperturas.</p>
                <form method="POST" action="php/abrirSobrePrueba.php">
                    <button type="submit" class="boton secundario"
                            data-espera="<?= (int) $faltan_prueba ?>"
                            data-label="Abrir sobre de prueba"
                            <?= $prueba_listo ? "" : "disabled" ?>>
                        <?= $prueba_listo ? "Abrir sobre de prueba" : "En espera" ?>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Carta conseguida hoy -->
    <?php if ($carta_nueva): ?>
        <div class="tarjeta">
            <h2>Tu carta de hoy</h2>
            <ul class="cartas-grid">
                <li class="carta-mini">
                    <span class="retrato">
                        <?php if (!empty($carta_nueva["imagen_url"])): ?>
                            <img class="fondo" src="<?= escapar($carta_nueva["imagen_url"]) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                            <img src="<?= escapar($carta_nueva["imagen_url"]) ?>" alt="<?= escapar($carta_nueva["nombre"]) ?>" loading="lazy" decoding="async" onerror="this.style.display='none'">
                        <?php else: ?>
                            <i class="bx bx-dna" aria-hidden="true"></i>
                        <?php endif; ?>
                    </span>
                    <h3><?= escapar($carta_nueva["nombre"]) ?></h3>
                    <p class="apodo">«<?= escapar($carta_nueva["especie"]) ?>»</p>
                    <div class="mini-stats">
                        <span><b><?= escapar($carta_nueva["hp"]) ?></b><small>HP</small></span>
                        <span><b><?= escapar($carta_nueva["ataque"]) ?></b><small>ATQ</small></span>
                        <span><b><?= escapar($carta_nueva["defensa"]) ?></b><small>DEF</small></span>
                        <span><b><?= escapar($carta_nueva["agilidad"]) ?></b><small>AGI</small></span>
                    </div>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Cartas de prueba conseguidas -->
    <?php if (count($cartas_prueba) > 0): ?>
        <div class="tarjeta">
            <h2>Cartas de prueba</h2>
            <ul class="cartas-grid">
                <?php foreach ($cartas_prueba as $d): ?>
                    <li class="carta-mini">
                        <span class="retrato">
                            <?php if (!empty($d["imagen_url"])): ?>
                                <img class="fondo" src="<?= escapar($d["imagen_url"]) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                <img src="<?= escapar($d["imagen_url"]) ?>" alt="<?= escapar($d["nombre"]) ?>" loading="lazy" decoding="async" onerror="this.style.display='none'">
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
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="tarjeta">
        <h2>Tu colección</h2>
        <ul class="resumen">
            <li><b><?= $total_cartas ?></b>cartas guardadas</li>
            <li><b><?= $total_especies ?></b>especies distintas</li>
            <li><b><?= $porcentaje ?>%</b>del catálogo (<?= $total_catalogo ?>)</li>
        </ul>
    </div>
</main>

<script src="script/script.js?v=<?= @filemtime(__DIR__ . '/script/script.js') ?>"></script>

</body>
</html>
