<?php
/* ============================================================
   coleccion.php
   ------------------------------------------------------------------
   Muestra las cartas que tiene el usuario.
   ============================================================ */

require_once __DIR__ . "/src/AccesoDatos.php";
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
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">
    <script src="script/script.js?v=<?= @filemtime(__DIR__ . '/script/script.js') ?>" defer></script>
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta panel-oscuro">
        <h1>Mi colección</h1>
        <p>Tienes <b><?= $total_especies ?></b> de <b><?= $total_catalogo ?></b> especies (<?= $porcentaje ?>%).</p>

        <?php if ($total_especies === 0): ?>
            <p class="aviso error">Todavía no tienes ninguna carta. Abre tu sobre del día para empezar.</p>
        <?php else: ?>
            <div class="campo">
                <input type="text" id="buscador" oninput="filtrarColeccion()" placeholder="Escribe un dinosaurio...">
            </div>

            <ul class="cartas-grid">
                <?php foreach ($orden as $id): ?>
                    <?php if (!isset($dinos[$id])) { continue; } $d = $dinos[$id]; ?>
                    <li class="carta-mini" data-buscar="<?= escapar(strtolower($d["especie"] . " " . $d["nombre"])) ?>">
                        <?php if ($copias[$id] > 1): ?>
                            <span class="badge">x<?= $copias[$id] ?></span>
                        <?php endif; ?>
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
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</main>

</body>
</html>
