<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/src/AccesoDatos.php';

function e(mixed $v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$usuarioId    = (int) $_SESSION['usuario_id'];
$usuarioDatos = $_SESSION['usuario_datos'] ?? [];

$usuarioActual = obtenerUsuarioPorId($usuarioId);
$nombre        = $usuarioActual['username'] ?? $usuarioDatos['username'] ?? 'Coleccionista';
$inicial       = mb_strtoupper(mb_substr((string) $nombre, 0, 1, 'UTF-8'), 'UTF-8');

$error     = null;
$ids       = [];
$mapa      = [];
$totalDino = 0;

try {
    $ids       = PA_ObtenerColeccion($usuarioId);
    $mapa      = dinosauriosPorIds($ids);
    $totalDino = contarDinosaurios();
} catch (Throwable $ex) {
    $error = $ex->getMessage();
}

// Agrupar (con repetidos) y calcular el progreso usando las reglas.
$agrupado       = agruparColeccion($ids);
$conteo         = $agrupado['conteo'];
$orden          = $agrupado['orden'];

$totalCartas    = count($ids);
$especiesUnicas = count($conteo);
$progreso       = calcularProgreso($especiesUnicas, $totalDino);
$faltantes      = $progreso['faltan'];
$porcentaje     = $progreso['porcentaje'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Mi colección</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">
    <script src="script/coleccion.js?v=<?= @filemtime(__DIR__ . '/script/coleccion.js') ?>"></script>
</head>
<body>
    <?php require __DIR__ . '/partials/cabecera.php'; ?>

    <main class="panel panel-coleccion">
        <section class="tarjeta" aria-labelledby="progreso-titulo">
            <h2 id="progreso-titulo"><i class="bx bx-line-chart" aria-hidden="true"></i> Progreso</h2>

            <p class="barra-progreso">
                <span><?= e($especiesUnicas) ?> de <?= e($totalDino) ?> especies · te faltan <?= e($faltantes) ?></span>
                <b><?= e($porcentaje) ?>%</b>
            </p>
            <span class="barra" role="progressbar" aria-valuenow="<?= e($porcentaje) ?>" aria-valuemin="0" aria-valuemax="100">
                <span style="width: <?= e($porcentaje) ?>%"></span>
            </span>

            <?php if ($error !== null): ?>
                <p class="aviso error">No se pudo cargar tu colección: <?= e($error) ?></p>
            <?php elseif ($totalCartas === 0): ?>
                <section class="vacio">
                    <i class="bx bx-sad" aria-hidden="true"></i>
                    <h3>Todavía no tienes ninguna carta</h3>
                    <p>Abre tu sobre del día y empieza a reunir dinosaurios.</p>
                    <a class="btn-carta" href="inicio.php"><i class="bx bx-gift" aria-hidden="true"></i> Ir a por mi carta diaria</a>
                </section>
            <?php else: ?>
                <br>
                <div class="toolbar">
                    <input type="search" id="filtro-coleccion" placeholder="Buscar por dinosaurio o apodo..." autocomplete="off" aria-label="Buscar en la colección">
                    <span class="contador" id="contador-coleccion"><?= e($especiesUnicas) ?> de <?= e($especiesUnicas) ?> especies</span>
                </div>
                
                <ul class="cartas-grid" id="cartas-grid">
                    <?php foreach ($orden as $id): ?>
                        <?php $d = $mapa[$id] ?? null; if (!$d) { continue; } ?>
                        <?php $rep = esRepetida($conteo[$id]); ?>
                        <li class="carta-mini <?= $rep ? 'es-repetida' : '' ?>" data-especie="<?= e($d['especie']) ?>" data-apodo="<?= e($d['nombre']) ?>">
                            <?php if ($rep): ?>
                                <span class="badge" title="Tienes <?= e($conteo[$id]) ?> copias">
                                    <i class="bx bx-copy-alt" aria-hidden="true"></i>&times;<?= e($conteo[$id]) ?>
                                </span>
                            <?php endif; ?>
                            <span class="retrato">
<<<<<<< HEAD
                                <?php if (!empty($d['imagen_url'])): ?>
                                    <img class="fondo" src="<?= e($d['imagen_url']) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                    <img src="<?= e($d['imagen_url']) ?>" alt="<?= e($d['especie']) ?>" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22266%22%3E%3Crect width=%22200%22 height=%22266%22 fill=%22%23121219%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%239aa3b2%22 font-size=%2240%22 text-anchor=%22middle%22 dy=%22.3em%22%3EDNA%3C/text%3E%3C/svg%3E'; this.style.objectFit='contain'">
                                <?php else: ?>
                                    <i class="bx bx-dna" aria-hidden="true"></i>
                                <?php endif; ?>
=======
                                 <?php if (!empty($d['imagen_url'])): ?>
                                     <img class="fondo" src="<?= e($d['imagen_url']) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                     <img src="<?= e($d['imagen_url']) ?>" alt="<?= e($d['especie']) ?>" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22266%22%3E%3Crect width=%22200%22 height=%22266%22 fill=%22%23121219%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%239aa3b2%22 font-size=%2240%22 text-anchor=%22middle%22 dy=%22.3em%22%3EDNA%3C/text%3E%3C/svg%3E'; this.style.objectFit='contain'">
                                 <?php else: ?>
                                     <i class="bx bx-dna" aria-hidden="true"></i>
                                 <?php endif; ?>
>>>>>>> 969003c3d7af802e71ef37618f3386313940b831
                            </span>
                            <h3><?= e($d['especie']) ?></h3>
                            <p class="apodo">«<?= e($d['nombre']) ?>»</p>
                            <span class="mini-stats">
                                <span><b><?= e($d['hp']) ?></b><small>HP</small></span>
                                <span><b><?= e($d['ataque']) ?></b><small>ATQ</small></span>
                                <span><b><?= e($d['defensa']) ?></b><small>DEF</small></span>
                                <span><b><?= e($d['agilidad']) ?></b><small>AGI</small></span>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
