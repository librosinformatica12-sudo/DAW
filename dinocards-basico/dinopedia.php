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
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta">
        <h1>DinoPedia</h1>
        <p>Catálogo completo. Pulsa «Añadir» para sumar un dinosaurio a tu colección.</p>

        <?php if ($aviso !== ""): ?>
            <p class="aviso ok"><?= escapar($aviso) ?></p>
        <?php endif; ?>
    </div>

    <div class="tarjeta">
        <ul class="cartas">
            <?php foreach ($dinos as $d): ?>
                <li class="carta">
                    <?php if (!empty($d["imagen_url"])): ?>
                        <img src="<?= escapar($d["imagen_url"]) ?>" alt="<?= escapar($d["especie"]) ?>">
                    <?php endif; ?>
                    <h3><?= escapar($d["especie"]) ?></h3>
                    <p class="apodo">«<?= escapar($d["nombre"]) ?>»</p>
                    <div class="stats">
                        <span><b><?= escapar($d["hp"]) ?></b>HP</span>
                        <span><b><?= escapar($d["ataque"]) ?></b>ATQ</span>
                        <span><b><?= escapar($d["defensa"]) ?></b>DEF</span>
                        <span><b><?= escapar($d["agilidad"]) ?></b>AGI</span>
                    </div>
                    <a class="boton" href="dinopedia.php?id=<?= (int) $d["id"] ?>">Añadir</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>

</body>
</html>
