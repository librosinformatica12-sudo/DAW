<?php
/* ============================================================
   dinopedia.php
   ------------------------------------------------------------------
   Album de dinosaurios. Las especies que el usuario ya tiene en su
   coleccion se ven a color; las que le faltan, en gris.
   ============================================================ */

require_once __DIR__ . "/src/AccesoDatos.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

// --- Especies que ya tengo (se ven a color) frente a las que no (en gris) ---
$mios = array_flip(obtenerIdsColeccion($usuario_id));

// --- Traigo todos los dinosaurios del catalogo ---
$dinos = [];
$res = $conexion->query("SELECT * FROM dinosaurios ORDER BY id");
while ($d = $res->fetch_assoc()) {
    $dinos[] = $d;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - DinoPedia</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta panel-oscuro">
        <h1>DinoPedia</h1>
        <p>Tu álbum: ya tienes <b><?= count($mios) ?></b> de <b><?= count($dinos) ?></b> especies. Las que te faltan se ven en gris.</p>

        <ul class="cartas-grid">
            <?php foreach ($dinos as $d): ?>
                <li class="carta-mini<?= isset($mios[$d["id"]]) ? "" : " bloqueada" ?>">
                    <span class="retrato">
                        <?php $img_ok = false; $img_path = $d["imagen_url"] ?? null; if (!empty($img_path)) { $full = __DIR__ . "/" . ltrim($img_path, '/\\'); $img_ok = is_readable($full); } ?>
                        <?php if ($img_ok): ?>
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
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

</body>
</html>
