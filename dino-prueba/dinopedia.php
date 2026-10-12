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

$usuarioId = (int) $_SESSION['usuario_id'];
$aviso    = '';
$avisoOk  = true;

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    try {
        $dino = dinosauriosPorIds([$id])[$id] ?? null;
        if (!$dino) {
            $aviso   = 'Ese dinosaurio no existe.';
            $avisoOk = false;
        } else {
            $codigo = PA_AñadirCarta($usuarioId, $id);
            if ($codigo === 0) {
                $aviso = 'Añadido a tu colección: ' . $dino['especie'] . ' «' . $dino['nombre'] . '»';
            } else {
                $aviso   = 'No se pudo añadir (código ' . $codigo . ').';
                $avisoOk = false;
            }
        }
    } catch (Throwable $ex) {
        $aviso   = 'Error: ' . $ex->getMessage();
        $avisoOk = false;
    }
}

$dinos = [];
$res = $connection->query('SELECT * FROM dinosaurios ORDER BY id');
while ($fila = $res->fetch_assoc()) {
    $dinos[] = $fila;
}
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
    <link rel="stylesheet" href="styles/style.css?v=<?= @filemtime(__DIR__ . '/styles/style.css') ?>">
</head>
<body>
    <?php require __DIR__ . '/partials/cabecera.php'; ?>

    <main class="panel panel-coleccion">
        <section class="tarjeta">
            <h2><i class="bx bx-plus-circle" aria-hidden="true"></i> Añadir dinosaurios</h2>
            <p class="sub">Pulsa «Añadir» en el dinosaurio que quieras y se sumará a tu colección.</p>

            <?php if ($aviso !== ''): ?>
                <p class="aviso <?= $avisoOk ? 'ok' : 'error' ?>"><?= e($aviso) ?></p>
            <?php endif; ?>

            <ul class="cartas-grid">
                <?php foreach ($dinos as $d): ?>
                    <li class="carta-mini">
                        <span class="retrato">
                            <?php if (!empty($d['imagen_url'])): ?>
                                <img src="<?= e($d['imagen_url']) ?>" alt="<?= e($d['especie']) ?>">
                            <?php else: ?>
                                <i class="bx bx-dna" aria-hidden="true"></i>
                            <?php endif; ?>
                        </span>
                        <h3><?= e($d['especie']) ?></h3>
                        <p class="apodo">«<?= e($d['nombre']) ?>»</p>
                            <div class="mini-stats">
                                <span><b><?= e($d['hp']) ?></b><small>HP</small></span>
                                <span><b><?= e($d['ataque']) ?></b><small>ATQ</small></span>
                                <span><b><?= e($d['defensa']) ?></b><small>DEF</small></span>
                                <span><b><?= e($d['agilidad']) ?></b><small>AGI</small></span>
                            </div>
                        <a class="btn-carta" href="dinopedia.php?id=<?= e($d['id']) ?>">
                            <i class="bx bx-plus" aria-hidden="true"></i> Añadir
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    </main>
</body>
</html>
