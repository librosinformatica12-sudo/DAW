<?php
/* ============================================================
   perfil.php
   ------------------------------------------------------------
   Muestra los datos del usuario y sus estadísticas.
   (Adaptado al estilo de dino-prueba manteniendo funciones de dinocards-basico)
   ============================================================ */

require_once __DIR__ . "/src/bootstrap.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

// --- Datos del usuario (dal/UsuarioDAL.php) ---
$usuario = obtenerUsuarioPorId($usuario_id);

// Si no se encuentra el usuario, evitamos continuar.
if (!$usuario) {
    header("Location: index.php");
    exit;
}

// --- Inicial del avatar ---
$inicial = strtoupper(
    mb_substr($usuario["username"] ?? "", 0, 1, "UTF-8")
);

if ($inicial === "") {
    $inicial = "?";
}

// --- Estadísticas de la colección (dal/ColeccionDAL.php y dal/DinosaurioDAL.php) ---
$resumen        = resumenColeccion($usuario_id);
$total_cartas   = $resumen["cartas"];
$total_especies = $resumen["especies"];
$total_catalogo = contarDinosaurios();

// --- Progreso del catálogo y nivel de la barra (src/ReglasColeccion.php) ---
$progreso   = calcularProgreso($total_especies, $total_catalogo);
$porcentaje = $progreso["porcentaje"];
$faltan     = $progreso["faltan"];
$nivel      = calcularNivel($porcentaje);

// --- Fechas de la cuenta ---
$registro = $usuario["fecha_registro"] ?? null;
$ultima   = $usuario["ultima_obtencion"] ?? null;

// --- Comprobar si ya se obtuvo la carta diaria ---
$ya_obtenida_hoy = (
    $ultima !== null
    && substr((string) $ultima, 0, 10) === date("Y-m-d")
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DinoCards - Mi perfil</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/perfil.css?v=<?= @filemtime(__DIR__ . '/styles/perfil.css') ?>">
</head>
<body>

<?php require "menu.php"; ?>

<main class="panel panel-perfil">

    <!-- Banner del perfil -->
    <header class="panel-hero">

        <div class="avatar-hero" aria-hidden="true">
            <?= escapar($inicial) ?>
        </div>

        <section class="panel-hero-texto">
            <h2><?= escapar($usuario["username"]) ?></h2>

            <p>
                <i class="bx bx-envelope" aria-hidden="true"></i>
                <?= escapar($usuario["email"]) ?>
            </p>

            <span class="etiqueta">
                <i class="bx bx-medal" aria-hidden="true"></i>
                Miembro desde
                <?= $registro !== null
                    ? date("d/m/Y", strtotime($registro))
                    : "—" ?>
            </span>
        </section>

        <p
            class="perfil-id"
            title="Tu identificador de coleccionista"
        >
            #<?= sprintf("%04d", $usuario_id) ?>
        </p>

    </header>

    <!-- Rejilla con el contenido -->
    <div class="perfil-grid">

        <!-- Estadísticas -->
        <section
            class="tarjeta perfil-stats"
            aria-labelledby="stats-titulo"
        >
            <h2 id="stats-titulo">
                <i class="bx bx-bar-chart-alt-2" aria-hidden="true"></i>
                Estadísticas
            </h2>

            <ul class="stats-grid">

                <li class="stat">
                    <i class="bx bx-cube" aria-hidden="true"></i>
                    <div>
                        <strong><?= $total_cartas ?></strong>
                        <small>cartas guardadas</small>
                    </div>
                </li>

                <li class="stat">
                    <i class="bx bx-layer" aria-hidden="true"></i>
                    <div>
                        <strong><?= $total_especies ?></strong>
                        <small>especies distintas</small>
                    </div>
                </li>

                <li class="stat">
                    <i class="bx bx-target-lock" aria-hidden="true"></i>
                    <div>
                        <strong><?= $porcentaje ?>%</strong>
                        <small>
                            del catálogo (<?= $total_catalogo ?>)
                        </small>
                    </div>
                </li>

                <li class="stat">
                    <i class="bx bx-gift" aria-hidden="true"></i>
                    <div>
                        <strong>
                            <?= $ya_obtenida_hoy
                                ? "Abierta"
                                : "Disponible" ?>
                        </strong>
                        <small>carta diaria hoy</small>
                    </div>
                </li>

            </ul>

            <!-- Barra de progreso -->
            <div class="contenedor-barra">

                <p class="barra-progreso">
                    <span>Progreso del catálogo</span>
                    <b><?= $porcentaje ?>%</b>
                </p>

                <div
                    class="barra"
                    role="progressbar"
                    aria-valuenow="<?= $porcentaje ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-label="Progreso del catálogo"
                >
                    <span
                        class="barra-relleno nivel-<?= escapar($nivel) ?>"
                        style="width: <?= $porcentaje ?>%;"
                    ></span>
                </div>

                <p class="barra-detalle">
                    <?php if ($porcentaje >= 100): ?>

                        <i class="bx bx-trophy" aria-hidden="true"></i>
                        ¡Colección completa!
                        Tienes las <?= $total_catalogo ?> especies.

                    <?php else: ?>

                        <i class="bx bx-collection" aria-hidden="true"></i>
                        <?= $total_especies ?> de
                        <?= $total_catalogo ?> especies.
                        Te faltan <?= $faltan ?>.

                    <?php endif; ?>
                </p>

            </div>
        </section>

        <!-- Datos de la cuenta -->
        <section
            class="tarjeta perfil-datos"
            aria-labelledby="datos-titulo"
        >
            <h2 id="datos-titulo">
                <i class="bx bx-id-card" aria-hidden="true"></i>
                Datos de la cuenta
            </h2>

            <ul class="datos-lista">

                <li>
                    <i class="bx bx-user" aria-hidden="true"></i>
                    <p>
                        <small>Usuario</small>
                        <b><?= escapar($usuario["username"]) ?></b>
                    </p>
                </li>

                <li>
                    <i class="bx bx-envelope" aria-hidden="true"></i>
                    <p>
                        <small>Correo electrónico</small>
                        <b><?= escapar($usuario["email"]) ?></b>
                    </p>
                </li>

                <li>
                    <i class="bx bx-purchase-tag" aria-hidden="true"></i>
                    <p>
                        <small>Identificador</small>
                        <b>#<?= sprintf("%04d", $usuario_id) ?></b>
                    </p>
                </li>

                <li>
                    <i class="bx bx-calendar" aria-hidden="true"></i>
                    <p>
                        <small>Miembro desde</small>
                        <b>
                            <?= $registro !== null
                                ? date("d/m/Y H:i", strtotime($registro))
                                : "—" ?>
                        </b>
                    </p>
                </li>

                <li>
                    <i class="bx bx-time" aria-hidden="true"></i>
                    <p>
                        <small>Última carta obtenida</small>
                        <b>
                            <?= $ultima !== null
                                ? date("d/m/Y H:i", strtotime($ultima))
                                : "Todavía ninguna" ?>
                        </b>
                    </p>
                </li>

            </ul>
        </section>

    </div>

</main>

</body>
</html>
