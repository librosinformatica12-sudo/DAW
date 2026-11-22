<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/src/AccesoDatos.php';

if (!function_exists('e')) {
    function e(mixed $v): string {
        return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    }
}

function slugifyFoto(string $s): string
{
    $s = trim($s);
    if (function_exists('iconv')) {
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
        if ($t !== false) {
            $s = $t;
        }
    }
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '_', $s);
    return trim($s, '_');
}

function enlazarFotos(): array
{
    $dirRel = 'img/img-cartas';
    $dirAbs = __DIR__ . '/img/img-cartas';
    $permitidas = ['png', 'jpg', 'jpeg', 'webp', 'gif', 'avif', 'bmp'];

    $res = ['ok' => 0, 'archivos' => 0, 'sobrantes' => []];
    if (!is_dir($dirAbs)) {
        return $res;
    }

    $porSlug = [];
    $porId = [];
    $archivos = [];
    foreach (scandir($dirAbs) as $f) {
        if ($f === '.' || $f === '..') {
            continue;
        }
        $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidas, true)) {
            continue;
        }
        $archivos[] = $f;
        $base = pathinfo($f, PATHINFO_FILENAME);
        $porSlug[slugifyFoto($base)] = $f;
        if (ctype_digit($base)) {
            $porId[(int) $base] = $f;
        }
    }
    $res['archivos'] = count($archivos);

    $usados = [];
    foreach (PA_ListarDinosaurios()['datos'] as $d) {
        $id = (int) $d['id'];
        $file = $porId[$id]
            ?? ($porSlug[slugifyFoto($d['especie'])] ?? null)
            ?? ($porSlug[slugifyFoto($d['nombre'])] ?? null);
        if ($file !== null) {
            $url = $dirRel . '/' . rawurlencode($file);
            if (PA_ActualizarImagenDinosaurio($id, $url)['codigo'] === 0) {
                $usados[$file] = true;
                $res['ok']++;
            }
        }
    }
    $res['sobrantes'] = array_values(array_diff($archivos, array_keys($usados)));
    return $res;
}

$usuarioId = (int) $_SESSION['usuario_id'];
$aviso    = '';
$avisoOk  = true;

if (isset($_GET['enlazar'])) {
    try {
        $info = enlazarFotos();
        $msg  = $info['ok'] . ' de ' . $info['archivos'] . ' foto(s) enlazada(s)';
        if ($info['sobrantes']) {
            $msg .= '. Sin coincidencia: ' . implode(', ', $info['sobrantes']);
        }
        $aviso   = 'Enlazar fotos: ' . $msg . '.';
        $avisoOk = true;
    } catch (Throwable $ex) {
        $aviso   = 'Error al enlazar: ' . $ex->getMessage();
        $avisoOk = false;
    }
}

$dinos = PA_ListarDinosaurios()['datos'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Añadir dinosaurios</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/comun.css?v=<?= @filemtime(__DIR__ . '/css/comun.css') ?>">
    <link rel="stylesheet" href="css/paginas.css?v=<?= @filemtime(__DIR__ . '/css/paginas.css') ?>">
    <script src="js/dinopedia.js?v=<?= @filemtime(__DIR__ . '/js/dinopedia.js') ?>" defer></script>
</head>
<body>
    <?php require __DIR__ . '/partials/cabecera.php'; ?>

    <main class="panel panel-coleccion">
        <section class="tarjeta">
            <h2><i class="bx bx-plus-circle" aria-hidden="true"></i> Añadir dinosaurios</h2>
            <p class="sub">Pulsa «Añadir» en el dinosaurio que quieras y se sumará a tu colección.</p>
            <p style="margin:.5rem 0 1.2rem">
                <a class="btn-carta" href="dinopedia.php?enlazar=1">
                    <i class="bx bx-image-add" aria-hidden="true"></i> Enlazar fotos de la carpeta
                </a>
            </p>

            <?php if ($aviso !== ''): ?>
                <p class="aviso <?= $avisoOk ? 'ok' : 'error' ?>" id="aviso-dinopedia" role="status"><?= e($aviso) ?></p>
            <?php endif; ?>

            <ul class="cartas-grid">
                <?php foreach ($dinos as $d): ?>
                    <li class="carta-mini">
                        <span class="retrato">
                            <?php if (!empty(imagenCarta($d))): ?>
                                <img class="fondo" src="<?= e(imagenCarta($d)) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                <img src="<?= e(imagenCarta($d)) ?>" alt="<?= e($d['nombre']) ?>" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22266%22%3E%3Crect width=%22200%22 height=%22266%22 fill=%22%23121219%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%239aa3b2%22 font-size=%2240%22 text-anchor=%22middle%22 dy=%22.3em%22%3EDNA%3C/text%3E%3C/svg%3E'; this.style.objectFit='contain'">
                            <?php else: ?>
                                <i class="bx bx-dna" aria-hidden="true"></i>
                            <?php endif; ?>
                        </span>
                        <h3><?= e($d['nombre']) ?></h3>
                        <p class="apodo">«<?= e($d['especie']) ?>»</p>
                            <div class="mini-stats">
                                <span><b><?= e($d['hp']) ?></b><small>HP</small></span>
                                <span><b><?= e($d['ataque']) ?></b><small>ATQ</small></span>
                                <span><b><?= e($d['defensa']) ?></b><small>DEF</small></span>
                                <span><b><?= e($d['agilidad']) ?></b><small>AGI</small></span>
                            </div>
                        <form class="form-anadir" method="POST" action="php/gestionar_anadirCarta.php" data-dino="<?= e($d['especie']) ?>">
                            <input type="hidden" name="dinosaurio_id" value="<?= e($d['id']) ?>">
                            <button type="submit" class="btn-carta">
                                <i class="bx bx-plus" aria-hidden="true"></i> Añadir
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>
