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
    <link rel="stylesheet" href="style.css?v=3">
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

        <ul class="cartas-grid">
            <?php foreach ($dinos as $d): ?>
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
                    <a class="btn-carta" href="dinopedia.php?id=<?= (int) $d["id"] ?>">
                        <i class="bx bx-plus" aria-hidden="true"></i> Añadir
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

</body>
</html>
