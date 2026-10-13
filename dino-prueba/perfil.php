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

$usuarioId = (int) $_SESSION['usuario_id'];
$usuario   = obtenerUsuarioPorId($usuarioId);

if (!$usuario) {
    session_destroy();
    header('Location: index.php');
    exit;
}

$nombre      = $usuario['username'] ?? 'Coleccionista';
$email       = $usuario['email'] ?? '';
$registro    = $usuario['fecha_registro'] ?? null;
$ultimaCarta = $usuario['ultima_obtencion'] ?? null;
$inicial     = mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8');

$yaObtenidaHoy = $ultimaCarta !== null
    && date('Y-m-d', strtotime((string) $ultimaCarta)) === date('Y-m-d');

$resumen       = resumenColeccion($usuarioId);
$totalCartas   = $resumen['cartas'];
$totalEspecies = $resumen['especies'];
$ultimas       = ultimasCartas($usuarioId, 6);

$totalCatalogo = contarDinosaurios();
$porcentaje    = $totalCatalogo > 0 ? (int) round(($totalEspecies / $totalCatalogo) * 100) : 0;
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
    
    <?php require __DIR__ . '/partials/cabecera.php'; ?>

    <main class="panel panel-perfil">
        <!-- Banner del Perfil -->
        <header class="panel-hero">
            <div class="avatar-hero" aria-hidden="true"><?= e($inicial) ?></div>
            <section class="panel-hero-texto">
                <h2><?= e($nombre) ?></h2>
                <p><i class="bx bx-envelope" aria-hidden="true"></i> <?= e($email !== '' ? $email : '—') ?></p>
                <span class="etiqueta">
                    <i class="bx bx-medal" aria-hidden="true"></i>
                    Miembro desde <?= e($registro !== null ? date('d/m/Y', strtotime((string) $registro)) : '—') ?>
                </span>
            </section>

            <p class="perfil-id" title="Tu identificador de coleccionista"><?= e(sprintf('#%04d', $usuarioId)) ?></p>
        </header>

        <!-- Rejilla con el contenido del perfil -->
        <div class="perfil-grid">
            
            <!-- Estadísticas principales -->
            <section class="tarjeta perfil-stats" aria-labelledby="stats-titulo">
                <h2 id="stats-titulo"><i class="bx bx-bar-chart-alt-2" aria-hidden="true"></i> Estadísticas</h2>
                <ul class="stats-grid">
                    <li class="stat">
                        <i class="bx bx-cube" aria-hidden="true"></i>
                        <div>
                            <strong><?= e($totalCartas) ?></strong>
                            <small>cartas guardadas</small>
                        </div>
                    </li>
                    <li class="stat">
                        <i class="bx bx-layer" aria-hidden="true"></i>
                        <div>
                            <strong><?= e($totalEspecies) ?></strong>
                            <small>especies distintas</small>
                        </div>
                    </li>
                    <li class="stat">
                        <i class="bx bx-target-lock" aria-hidden="true"></i>
                        <div>
                            <strong><?= e($porcentaje) ?>%</strong>
                            <small>del catálogo (<?= e($totalCatalogo) ?>)</small>
                        </div>
                    </li>
                    <li class="stat">
                        <i class="bx bx-gift" aria-hidden="true"></i>
                        <div>
                            <strong><?= $yaObtenidaHoy ? 'Abierta' : 'Disponible' ?></strong>
                            <small>carta diaria hoy</small>
                        </div>
                    </li>
                </ul>

                <div class="contenedor-barra">
                    <p class="barra-progreso"><span>Progreso del catálogo</span><b><?= e($porcentaje) ?>%</b></p>
                    <div class="barra" role="progressbar" aria-valuenow="<?= e($porcentaje) ?>" aria-valuemin="0" aria-valuemax="100">
                        <span style="width: <?= e($porcentaje) ?>%"></span>
                    </div>
                </div>
            </section>

            <!-- Datos de la cuenta -->
            <section class="tarjeta perfil-datos" aria-labelledby="datos-titulo">
                <h2 id="datos-titulo"><i class="bx bx-id-card" aria-hidden="true"></i> Datos de la cuenta</h2>
                <ul class="datos-lista">
                    <li>
                        <i class="bx bx-user" aria-hidden="true"></i>
                        <p><small>Usuario</small><b><?= e($nombre) ?></b></p>
                    </li>
                    <li>
                        <i class="bx bx-envelope" aria-hidden="true"></i>
                        <p><small>Correo electrónico</small><b><?= e($email !== '' ? $email : '—') ?></b></p>
                    </li>
                    <li>
                        <i class="bx bx-purchase-tag" aria-hidden="true"></i>
                        <p><small>Identificador</small><b><?= e(sprintf('#%04d', $usuarioId)) ?></b></p>
                    </li>
                    <li>
                        <i class="bx bx-calendar" aria-hidden="true"></i>
                        <p><small>Miembro desde</small><b><?= e($registro !== null ? date('d/m/Y H:i', strtotime((string) $registro)) : '—') ?></b></p>
                    </li>
                    <li>
                        <i class="bx bx-time" aria-hidden="true"></i>
                        <p><small>Última carta obtenida</small><b><?= e($ultimaCarta !== null ? date('d/m/Y H:i', strtotime((string) $ultimaCarta)) : 'Todavía ninguna') ?></b></p>
                    </li>
                </ul>
            </section>

            <!-- Últimas cartas conseguidas -->
            <section class="tarjeta perfil-ultimas" aria-labelledby="ultimas-titulo">
                <h2 id="ultimas-titulo"><i class="bx bx-time-five" aria-hidden="true"></i> Últimas cartas</h2>

                <?php if (empty($ultimas)): ?>
                    <div class="vacio">
                        <i class="bx bx-sad" aria-hidden="true"></i>
                        <h3>Aún no has conseguido cartas</h3>
                        <p>Abre tu sobre del día para empezar.</p>
                        <a class="btn-carta" href="inicio.php"><i class="bx bx-gift" aria-hidden="true"></i> Ir a por mi carta</a>
                    </div>
                <?php else: ?>
                    <ul class="cartas-grid">
                        <?php foreach ($ultimas as $d): ?>
                            <li class="carta-mini">
                                <div class="retrato">
                                     <?php if (!empty($d['imagen_url'])): ?>
                                         <img class="fondo" src="<?= e($d['imagen_url']) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                         <img src="<?= e($d['imagen_url']) ?>" alt="<?= e($d['especie']) ?>" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22266%22%3E%3Crect width=%22200%22 height=%22266%22 fill=%22%23121219%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%239aa3b2%22 font-size=%2240%22 text-anchor=%22middle%22 dy=%22.3em%22%3EDNA%3C/text%3E%3C/svg%3E'; this.style.objectFit='contain'">
                                     <?php else: ?>
                                         <i class="bx bx-dna" aria-hidden="true"></i>
                                     <?php endif; ?>
                                </div>
                                <h3><?= e($d['especie']) ?></h3>
                                <p class="apodo">«<?= e($d['nombre']) ?>»</p>
                                <div class="mini-stats">
                                    <span><b><?= e($d['hp']) ?></b><small>HP</small></span>
                                    <span><b><?= e($d['ataque']) ?></b><small>ATQ</small></span>
                                    <span><b><?= e($d['defensa']) ?></b><small>DEF</small></span>
                                    <span><b><?= e($d['agilidad']) ?></b><small>AGI</small></span>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

        </div>

        <br>

        <!-- Acciones rápidas -->
        <nav class="tarjeta perfil-acciones" aria-label="Acciones de la cuenta">
            <h2><i class="bx bx-grid-alt" aria-hidden="true"></i> Acciones</h2>
            <ul class="tiles">
                <li>
                    <a class="tile" href="inicio.php">
                        <i class="bx bx-home" aria-hidden="true"></i>
                        <span class="tile-texto"><b>Ir a mi panel</b><small>Abre tu sobre del día</small></span>
                    </a>
                </li>
                <li>
                    <a class="tile" href="coleccion.php">
                        <i class="bx bx-collection" aria-hidden="true"></i>
                        <span class="tile-texto"><b>Ver mi colección</b><small><?= e($totalCartas) ?> cartas reunidas</small></span>
                    </a>
                </li>
                <li>
                    <a class="tile" href="php/logout.php">
                        <i class="bx bx-log-out" aria-hidden="true"></i>
                        <span class="tile-texto"><b>Cerrar sesión</b><small>Salir de tu cuenta</small></span>
                    </a>
                </li>
            </ul>
        </nav>

    </main>
</body>
</html>