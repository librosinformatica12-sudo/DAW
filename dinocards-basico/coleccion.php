<?php
/* ============================================================
   coleccion.php
   ------------------------------------------------------------------
   Muestra las cartas que tiene el usuario.
   ============================================================ */

require_once "conexion.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

// 1) Traigo los ids de los dinosaurios que tengo (puede haber repetidos)
$ids = [];
$consulta = $conexion->prepare("SELECT dinosaurio_id FROM colecciones WHERE usuario_id = ?");
$consulta->bind_param("i", $usuario_id);
$consulta->execute();
$resultado = $consulta->get_result();
while ($fila = $resultado->fetch_assoc()) {
    $ids[] = (int) $fila["dinosaurio_id"];
}
$consulta->close();

// 2) Cuento cuantas copias tengo de cada dinosaurio
$copias = [];
$orden  = [];
foreach ($ids as $id) {
    if (!isset($copias[$id])) {
        $copias[$id] = 0;
        $orden[] = $id;
    }
    $copias[$id]++;
}

// 3) Traigo los datos de esos dinosaurios
$dinos = [];
if (count($orden) > 0) {
    $lista = implode(",", $orden); // son numeros, no hay peligro
    $res = $conexion->query("SELECT * FROM dinosaurios WHERE id IN ($lista)");
    while ($d = $res->fetch_assoc()) {
        $dinos[(int) $d["id"]] = $d;
    }
}

// 4) Calculo el progreso
$total_catalogo = (int) $conexion->query("SELECT COUNT(*) AS total FROM dinosaurios")->fetch_assoc()["total"];
$total_especies = count($orden);
$porcentaje     = $total_catalogo > 0 ? round($total_especies / $total_catalogo * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Mi colección</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta">
        <h1>Mi colección</h1>
        <p>Tienes <b><?= $total_especies ?></b> de <b><?= $total_catalogo ?></b> especies (<?= $porcentaje ?>%).</p>

        <?php if ($total_especies === 0): ?>
            <p class="aviso error">Todavía no tienes ninguna carta. Abre tu sobre del día para empezar.</p>
        <?php else: ?>
            <div class="campo">
                <label for="buscador">Buscar</label>
                <input type="text" id="buscador" oninput="filtrarColeccion()" placeholder="Escribe un dinosaurio...">
            </div>

            <ul class="cartas">
                <?php foreach ($orden as $id): ?>
                    <?php if (!isset($dinos[$id])) { continue; } $d = $dinos[$id]; ?>
                    <li class="carta" data-buscar="<?= escapar(strtolower($d["especie"] . " " . $d["nombre"])) ?>">
                        <?php if ($copias[$id] > 1): ?>
                            <span class="badge">x<?= $copias[$id] ?></span>
                        <?php endif; ?>
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
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
