<?php
/* ============================================================
   dinopedia.php
   ------------------------------------------------------------------
   Catalogo completo de dinosaurios. Desde aqui se pueden añadir
   dinosaurios a la coleccion.
   ============================================================ */

require_once "conexion.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];
$aviso = "";

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

function enlazarFotos($connection): array
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
    $q = $connection->query('SELECT id, nombre, especie FROM dinosaurios ORDER BY id');
    while ($d = $q->fetch_assoc()) {
        $id = (int) $d['id'];
        $file = $porId[$id]
            ?? ($porSlug[slugifyFoto($d['especie'])] ?? null)
            ?? ($porSlug[slugifyFoto($d['nombre'])] ?? null);
        if ($file !== null) {
            $url = $dirRel . '/' . rawurlencode($file);
            $stmt = $connection->prepare('UPDATE dinosaurios SET imagen_url = ? WHERE id = ?');
            $stmt->bind_param('si', $url, $id);
            $stmt->execute();
            $stmt->close();
            $usados[$file] = true;
            $res['ok']++;
        }
    }
    $res['sobrantes'] = array_values(array_diff($archivos, array_keys($usados)));
    return $res;
}


// --- Añadir un dinosaurio a mi coleccion ---
if (isset($_GET["id"])) {
    $id = (int) $_GET["id"];

    $consulta = $conexion->prepare("SELECT * FROM dinosaurios WHERE id = ?");
    $consulta->bind_param("i", $id);
    $consulta->execute();
    $dino = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    if ($dino) {
        anadir_a_coleccion($conexion, $usuario_id, $id);
        $aviso = "Añadido a tu colección: " . $dino["especie"] . " «" . $dino["nombre"] . "»";
    } else {
        $aviso = "Ese dinosaurio no existe.";
    }
}

// --- Traigo todos los dinosaurios ---
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
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta panel-oscuro">
        <h1>DinoPedia</h1>
        <p>Catálogo completo. Pulsa «Añadir» para sumar un dinosaurio a tu colección.</p>

        <?php if ($aviso !== ""): ?>
            <p class="aviso ok"><?= escapar($aviso) ?></p>
        <?php endif; ?>
    </div>

    <div class="tarjeta panel-oscuro">
            <ul class="cartas-grid">
                <?php foreach ($dinos as $d): ?>
                    <li class="carta-mini">
                        <span class="retrato">
                            <?php if (!empty($d['imagen_url'])): ?>
                                <img class="fondo" src="<?= e($d['imagen_url']) ?>" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.style.display='none'">
                                <img src="<?= e($d['imagen_url']) ?>" alt="<?= e($d['nombre']) ?>" loading="lazy" decoding="async" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22266%22%3E%3Crect width=%22200%22 height=%22266%22 fill=%22%23121219%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 fill=%22%239aa3b2%22 font-size=%2240%22 text-anchor=%22middle%22 dy=%22.3em%22%3EDNA%3C/text%3E%3C/svg%3E'; this.style.objectFit='contain'">
                            <?php else: ?>
                                <i class="bx bx-dna" aria-hidden="true"></i>
                            <?php endif; ?>
                        </span>
                        <h3><?= e($d['nombre']) ?></h3>
                        <p class="apodo">«<?= e($d['especie']) ?>»</p>
                            <div class="mini-stats">
                                <span><b><?= escapar($d['hp']) ?></b><small>HP</small></span>
                                <span><b><?= escapar($d['ataque']) ?></b><small>ATQ</small></span>
                                <span><b><?= escapar($d['defensa']) ?></b><small>DEF</small></span>
                                <span><b><?= escapar($d['agilidad']) ?></b><small>AGI</small></span>
                            </div>
                        <a class="btn-carta" href="dinopedia.php?id=<?= e($d['id']) ?>">
                            <i class="bx bx-plus" aria-hidden="true"></i> Añadir
                        </a>
                    </li>
                <?php endforeach; ?>
        </ul>
    </div>
</main>

</body>
</html>
