<?php
/* ============================================================
   inicio.php · Panel principal del coleccionista
   ------------------------------------------------------------
   ESTRUCTURA DE LA PÁGINA
   .panel-layout
     ├─ .col-principal  → Bienvenida · Sobres (carrusel) · Carta de hoy
     └─ .col-lateral    → Resumen de la colección · Accesos rápidos
   ============================================================ */

session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/src/AccesoDatos.php';

function e(mixed $v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

/* ============================================================
   1. DATOS
   ============================================================ */

/* ---------- Usuario ---------- */
$usuarioId = (int) $_SESSION['usuario_id'];
$datos     = $_SESSION['usuario_datos'] ?? [];

$usuarioActual = obtenerUsuarioPorId($usuarioId);
$nombre        = $usuarioActual['username'] ?? $datos['username'] ?? 'Coleccionista';
$ultimaCarta   = $usuarioActual['ultima_obtencion'] ?? ($datos['ultima_obtencion'] ?? null);
$inicial       = mb_strtoupper(mb_substr((string) $nombre, 0, 1, 'UTF-8'), 'UTF-8');

$yaObtenidaHoy = $ultimaCarta !== null
    && date('Y-m-d', strtotime((string) $ultimaCarta)) === date('Y-m-d');

/* ---------- Colección ---------- */
$resumen       = resumenColeccion($usuarioId);
$totalCartas   = $resumen['cartas'];
$totalEspecies = $resumen['especies'];
$totalCatalogo = contarDinosaurios();
$porcentaje    = $totalCatalogo > 0 ? (int) round($totalEspecies / $totalCatalogo * 100) : 0;

/* ---------- Fecha ---------- */
$diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
$meses      = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
$diaSemana  = $diasSemana[(int) date('w')];
$mesActual  = $meses[(int) date('n') - 1];

/* ---------- Sobre de prueba: espera entre aperturas ----------
   120 = 2 minutos. Cámbialo aquí y solo aquí. */
$esperaPrueba  = 120;
$ultimaPrueba  = $usuarioActual['carta_prueba'] ?? null;
$pasadosPrueba = $ultimaPrueba ? (time() - strtotime((string) $ultimaPrueba)) : PHP_INT_MAX;
$faltanPrueba  = max(0, $esperaPrueba - $pasadosPrueba);
$pruebaListo   = $faltanPrueba === 0;

/* ---------- Sobres ----------
   'disponible' => true activa el sobre.
   'contiene'   => lista que se muestra al ver el sobre en el carrusel. */
$sobres = [
    [
        'nombre'     => 'Sobre del día',
        'icono'      => 'bx-sun',
        'imagen'     => 'img/img-sobres/sobre.png',
        'disponible' => true,
        'texto'      => $yaObtenidaHoy
            ? 'Ya abriste tu sobre hoy. Vuelve mañana por otra carta.'
            : 'Ábrelo y consigue un dinosaurio nuevo para tu colección.',
        'estado'     => $yaObtenidaHoy ? 'Abierto hoy' : 'Disponible',
        'contiene'   => [
            ['bx-cube',   '1 carta de dinosaurio'],
            ['bx-time',   'Uno gratis cada día'],
            ['bx-shuffle', 'Dinosaurio aleatorio del catálogo'],
        ],
    ],
    [
        'id'         => 'prueba',
        'nombre'     => 'Sobre de Prueba',
        'icono'      => 'bx-test-tube',
        'imagen'     => 'img/img-sobres/sobre3.png',
        'disponible' => true, // siempre activo para hacer pruebas
        'texto'      => $pruebaListo
            ? 'Ábrelo y consigue 4 cartas. Puedes repetir cada 2 minutos.'
            : 'Aún no puedes abrirlo. Vuelve en unos minutos.',
        'estado'     => $pruebaListo ? 'Disponible' : 'En espera',
        'boton'      => $pruebaListo ? 'Abrir sobre de prueba' : 'En espera',
        'bloqueado'  => !$pruebaListo,
        'espera'     => $faltanPrueba, // segundos que faltan (lo usará el JS)
        'cartas'     => 4,
        'contiene'   => [
            ['bx-cube',    '4 cartas de dinosaurio'],
            ['bx-time',    'Se repite cada 2 minutos'],
            ['bx-shuffle', 'Dinosaurios aleatorios'],
        ],
    ],
    /* 
    [
        'nombre'     => 'Sobre semanal',
        'icono'      => 'bx-calendar-week',
        'imagen'     => 'img/img-sobres/sobre2.png',
        'disponible' => true,
        'texto'      => 'Ábrelo al completar 7 días seguidos de coleccionista.',
        'estado'     => 'Próximamente',
        'contiene'   => [
            ['bx-cube',     '3 cartas de dinosaurio'],
            ['bx-calendar', 'Se desbloquea con 7 días seguidos'],
            ['bx-star',     'Más opciones de cartas poco comunes'],
        ],
    ],
    */
    [
        'nombre'     => 'Sobre de evento',
        'icono'      => 'bx-party',
        'imagen'     => '',
        'disponible' => false,
        'texto'      => 'Cartas exclusivas durante los eventos temporales.',
        'estado'     => 'Próximamente',
        'contiene'   => [
            ['bx-cube',  '1 carta exclusiva del evento'],
            ['bx-timer', 'Solo durante el evento'],
            ['bx-crown', 'Cartas que no salen en otros sobres'],
        ],
    ],

];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards · Mi panel</title>

    <!-- Tipografías e iconos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <!-- Estilos -->
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">

    <!-- Scripts (defer: se ejecutan cuando el HTML ya está cargado) -->
    <script src="script/inicio.js?v=<?= @filemtime(__DIR__ . '/script/inicio.js') ?>" defer></script>
    <script src="script/carrusel.js?v=<?= @filemtime(__DIR__ . '/script/carrusel.js') ?>" defer></script>
</head>

<body>
    <?php require __DIR__ . '/partials/cabecera.php'; ?>

    <main class="panel panel-inicio">
        <div class="panel-layout">

            <!-- ====================================================
                 COLUMNA PRINCIPAL
                 ==================================================== -->
            <div class="col-principal">

                <!-- 1 · BIENVENIDA -->
                <div class="bloque bloque-hero">
                    <header class="panel-hero">
                        <p class="avatar-hero" aria-hidden="true"><?= e($inicial) ?></p>

                        <div class="panel-hero-texto">
                            <h2>Hola, <span><?= e($nombre) ?></span></h2>
                            <p>Abre tu sobre del día y sigue completando tu colección de dinosaurios.</p>
                        </div>

                        <time class="panel-fecha" datetime="<?= e(date('Y-m-d')) ?>">
                            <small><?= e($diaSemana) ?></small>
                            <b><?= e(date('j')) ?></b>
                            <span><?= e($mesActual) ?> <?= e(date('Y')) ?></span>
                        </time>
                    </header>
                </div>

                <!-- 2 · SOBRES (carrusel) -->
                <div class="bloque bloque-sobres">
                    <section class="sobres" aria-labelledby="sobres-titulo">

                        <div class="sobres-cab-wrap">
                            <header class="sobres-cab">
                                <h2 id="sobres-titulo"><i class="bx bx-package" aria-hidden="true"></i> Sobres</h2>
                                <span class="contador" id="sobres-contador">1 / <?= e(count($sobres)) ?></span>
                            </header>
                        </div>

                        <div class="carrusel" id="carrusel">
                            <button type="button" class="carrusel-nav" id="sobre-prev" aria-label="Sobre anterior">
                                <i class="bx bx-chevron-left" aria-hidden="true"></i>
                            </button>

                            <ul class="carrusel-pista" id="carrusel-pista">
                            <?php foreach ($sobres as $i => $sobre): ?>
                            <?php
                            // Solo el sobre del día no tiene 'id': conserva id="btn-sobre" para no romper inicio.js
                            $esDiario = empty($sobre['id']);
                            ?>
                            <li class="sobre-slide<?= $i === 0 ? ' activo' : '' ?>" data-indice="<?= e($i) ?>">

                                <!-- Imagen y descripción -->
                                <div class="sobre-principal">
                                    <figure class="sobre-box">
                                        <?php if (!empty($sobre['imagen'])): ?>
                                            <img class="sobre-img" src="<?= e($sobre['imagen']) ?>" alt="<?= e($sobre['nombre']) ?>">
                                        <?php else: ?>
                                            <span class="sobre-icono" aria-hidden="true">
                                                <i class="bx <?= e($sobre['icono']) ?>"></i>
                                            </span>
                                        <?php endif; ?>
                                        <figcaption>
                                            <h3><?= e($sobre['nombre']) ?></h3>
                                            <!-- id="sobre-texto" solo en el sobre del día (un id no puede repetirse) -->
                                            <p<?= ($sobre['disponible'] && $esDiario) ? ' id="sobre-texto"' : '' ?>><?= e($sobre['texto']) ?></p>
                                        </figcaption>
                                    </figure>
                                </div>

                                <!-- Qué contiene este sobre -->
                                <div class="sobre-detalle">
                                    <p class="sobre-estado<?= $sobre['disponible'] ? ' ok' : '' ?>">
                                        <i class="bx <?= e($sobre['icono']) ?>" aria-hidden="true"></i>
                                        <?= e($sobre['estado']) ?>
                                    </p>

                                    <ul class="sobre-contiene">
                                        <?php foreach ($sobre['contiene'] as [$icono, $linea]): ?>
                                            <li>
                                                <i class="bx <?= e($icono) ?>" aria-hidden="true"></i>
                                                <?= e($linea) ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>

                                <!-- Acción -->
                                <div class="sobre-accion">
                                    <?php if ($sobre['disponible']): ?>
                                        <?php
                                        $bloqueado = $esDiario ? $yaObtenidaHoy : !empty($sobre['bloqueado']);
                                        $textoBtn  = $esDiario
                                            ? ($yaObtenidaHoy ? 'Ya abierto hoy' : 'Abrir sobre del día')
                                            : ($sobre['boton'] ?? 'Abrir sobre');
                                        ?>
                                        <button type="button"
                                                class="btn-carta"
                                                <?= $esDiario ? 'id="btn-sobre"' : 'data-sobre="' . e($sobre['id']) . '"' ?>
                                                <?= !empty($sobre['espera']) ? 'data-espera="' . e($sobre['espera']) . '"' : '' ?>
                                                <?= $bloqueado ? 'disabled' : '' ?>>
                                            <i class="bx bx-gift" aria-hidden="true"></i>
                                            <?= e($textoBtn) ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn-carta" disabled>
                                            <i class="bx <?= e($sobre['icono']) ?>" aria-hidden="true"></i> Próximamente
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </li>
                        <?php endforeach; ?>
                            </ul>

                            <button type="button" class="carrusel-nav" id="sobre-next" aria-label="Sobre siguiente">
                                <i class="bx bx-chevron-right" aria-hidden="true"></i>
                            </button>
                        </div>

                        <!-- Los puntos los genera script/carrusel.js -->
                        <ol class="carrusel-puntos" id="carrusel-puntos" aria-label="Seleccionar sobre"></ol>
                    </section>
                </div>

                <!-- 3 · TU CARTA DE HOY -->
                <div class="bloque bloque-resultado">
                    <section class="resultado" aria-labelledby="resultado-titulo" aria-live="polite">
                        <h2 id="resultado-titulo"><i class="bx bx-image" aria-hidden="true"></i> Tu carta de hoy</h2>

                        <output id="resultado-carta" class="resultado-salida">
                            <span class="resultado-vacio">
                                <i class="bx bx-gift" aria-hidden="true"></i>
                                Abre el sobre del día y verás aquí tu nueva carta.
                            </span>
                        </output>
                    </section>
                </div>

            </div><!-- /.col-principal -->


            <!-- ====================================================
                 COLUMNA LATERAL
                 ==================================================== -->
            <div class="col-lateral">

                <!-- 4 · RESUMEN DE LA COLECCIÓN -->
                <div class="bloque bloque-resumen">
                    <aside class="panel-resumen2" aria-label="Resumen de tu colección">
                        <h3>Tu colección</h3>

                        <ul class="resumen-grid">
                            <li class="resumen-item">
                                <i class="bx bx-cube" aria-hidden="true"></i>
                                <strong><?= e($totalCartas) ?></strong>
                                <small>cartas guardadas</small>
                            </li>
                            <li class="resumen-item">
                                <i class="bx bx-layer" aria-hidden="true"></i>
                                <strong><?= e($totalEspecies) ?></strong>
                                <small>especies distintas</small>
                            </li>
                            <li class="resumen-item">
                                <i class="bx bx-target-lock" aria-hidden="true"></i>
                                <strong><?= e($porcentaje) ?>%</strong>
                                <small>del catálogo (<?= e($totalCatalogo) ?>)</small>
                            </li>
                            <li class="resumen-item">
                                <i class="bx bx-gift" aria-hidden="true"></i>
                                <strong><?= $yaObtenidaHoy ? 'Abierta' : 'Disponible' ?></strong>
                                <small>carta de hoy</small>
                            </li>
                        </ul>

                        <p class="resumen-progreso">
                            <span>Progreso del catálogo</span><b><?= e($porcentaje) ?>%</b>
                        </p>
                        <span class="resumen-barra" role="progressbar" aria-valuenow="<?= e($porcentaje) ?>" aria-valuemin="0" aria-valuemax="100">
                            <span style="width: <?= e($porcentaje) ?>%"></span>
                        </span>
                    </aside>
                </div>

                <!-- 5 · ACCESOS RÁPIDOS -->
                <div class="bloque bloque-accesos">
                    <nav class="accesos-panel" aria-label="Accesos rápidos">
                        <h3>Explora</h3>

                        <ul>
                            <li>
                                <a href="coleccion.php">
                                    <i class="bx bx-collection" aria-hidden="true"></i>
                                    <b>Mi colección</b>
                                    <small>Revisa tus <?= e($totalCartas) ?> cartas</small>
                                </a>
                            </li>
                            <li>
                                <a href="perfil.php">
                                    <i class="bx bx-user-circle" aria-hidden="true"></i>
                                    <b>Mi perfil</b>
                                    <small>Tus datos y estadísticas</small>
                                </a>
                            </li>
                            <li>
                                <a href="dinopedia.php">
                                    <i class="bx bx-book-open" aria-hidden="true"></i>
                                    <b>DinoPedia</b>
                                    <small>Próximamente</small>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

            </div><!-- /.col-lateral -->

        </div><!-- /.panel-layout -->
    </main>
</body>

</html>