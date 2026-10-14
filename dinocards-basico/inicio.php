<?php
/* ============================================================
   inicio.php
   ------------------------------------------------------------------
   Panel principal. Aqui se abren los sobres y se ve el resumen
   de la coleccion.
   ============================================================ */

require_once "conexion.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

$mensaje       = "";
$carta_nueva   = null;
$cartas_prueba = [];

// --- Datos del usuario ---
$consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$consulta->bind_param("i", $usuario_id);
$consulta->execute();
$usuario = $consulta->get_result()->fetch_assoc();
$consulta->close();

// --- ¿Ya ha cogido la carta de hoy? (miramos solo la fecha) ---
$hoy = date("Y-m-d");
$ya_cogida_hoy = ($usuario["ultima_obtencion"] !== null
    && substr($usuario["ultima_obtencion"], 0, 10) === $hoy);

// --- Abrir el sobre del día ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "diario") {
    if ($ya_cogida_hoy) {
        $mensaje = "Ya has abierto tu sobre hoy. Vuelve mañana.";
    } else {
        // Cogemos un dinosaurio al azar
        $azar = $conexion->query("SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 1")->fetch_assoc();
        if ($azar) {
            anadir_a_coleccion($conexion, $usuario_id, (int) $azar["id"]);
            $conexion->query("UPDATE usuarios SET ultima_obtencion = NOW() WHERE id = " . $usuario_id);
            $carta_nueva = $azar;
            $ya_cogida_hoy = true;
            $mensaje = "¡Has conseguido una carta nueva!";
        } else {
            $mensaje = "No hay dinosaurios disponibles.";
        }
    }
}

// --- Abrir el sobre de prueba (4 cartas) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["accion"] ?? "") === "prueba") {
    $azar = $conexion->query("SELECT * FROM dinosaurios ORDER BY RAND() LIMIT 4");
    while ($d = $azar->fetch_assoc()) {
        anadir_a_coleccion($conexion, $usuario_id, (int) $d["id"]);
        $cartas_prueba[] = $d;
    }
    $mensaje = "¡Has conseguido " . count($cartas_prueba) . " cartas de prueba!";
}

// --- Resumen de la coleccion ---
$resumen = $conexion->query(
    "SELECT COUNT(*) AS total, COUNT(DISTINCT dinosaurio_id) AS especies
     FROM colecciones WHERE usuario_id = " . $usuario_id
)->fetch_assoc();

$total_cartas   = (int) $resumen["total"];
$total_especies = (int) $resumen["especies"];
$total_catalogo = (int) $conexion->query("SELECT COUNT(*) AS total FROM dinosaurios")->fetch_assoc()["total"];
$porcentaje     = $total_catalogo > 0 ? round($total_especies / $total_catalogo * 100) : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Inicio</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta">
        <h1>Hola, <?= escapar($usuario["username"]) ?></h1>
        <p>Hoy es <?= date("d/m/Y") ?>. Abre tu sobre del día y sigue completando tu colección.</p>
    </div>

    <?php if ($mensaje !== ""): ?>
        <p class="aviso ok"><?= escapar($mensaje) ?></p>
    <?php endif; ?>

    <!-- Sobre del día -->
    <div class="tarjeta">
        <h2>Sobre del día</h2>
        <p>Consigue un dinosaurio nuevo gratis cada día.</p>
        <form method="POST" action="inicio.php">
            <input type="hidden" name="accion" value="diario">
            <button type="submit" class="boton" <?= $ya_cogida_hoy ? "disabled" : "" ?>>
                <?= $ya_cogida_hoy ? "Ya abierto hoy" : "Abrir sobre del día" ?>
            </button>
        </form>
    </div>

    <!-- Carta conseguida hoy -->
    <?php if ($carta_nueva): ?>
        <div class="tarjeta">
            <h2>Tu carta de hoy</h2>
            <ul class="cartas">
                <li class="carta">
                    <?php if (!empty($carta_nueva["imagen_url"])): ?>
                        <img src="<?= escapar($carta_nueva["imagen_url"]) ?>" alt="<?= escapar($carta_nueva["especie"]) ?>">
                    <?php endif; ?>
                    <h3><?= escapar($carta_nueva["especie"]) ?></h3>
                    <p class="apodo">«<?= escapar($carta_nueva["nombre"]) ?>»</p>
                    <div class="stats">
                        <span><b><?= escapar($carta_nueva["hp"]) ?></b>HP</span>
                        <span><b><?= escapar($carta_nueva["ataque"]) ?></b>ATQ</span>
                        <span><b><?= escapar($carta_nueva["defensa"]) ?></b>DEF</span>
                        <span><b><?= escapar($carta_nueva["agilidad"]) ?></b>AGI</span>
                    </div>
                </li>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Sobre de prueba -->
    <div class="tarjeta">
        <h2>Sobre de prueba</h2>
        <p>Ábrelo las veces que quieras para conseguir 4 cartas de golpe.</p>
        <form method="POST" action="inicio.php">
            <input type="hidden" name="accion" value="prueba">
            <button type="submit" class="boton secundario">Abrir sobre de prueba</button>
        </form>
    </div>

    <!-- Cartas de prueba conseguidas -->
    <?php if (count($cartas_prueba) > 0): ?>
        <div class="tarjeta">
            <h2>Cartas de prueba</h2>
            <ul class="cartas">
                <?php foreach ($cartas_prueba as $d): ?>
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
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Resumen -->
    <div class="tarjeta">
        <h2>Tu colección</h2>
        <ul class="resumen">
            <li><b><?= $total_cartas ?></b>cartas guardadas</li>
            <li><b><?= $total_especies ?></b>especies distintas</li>
            <li><b><?= $porcentaje ?>%</b>del catálogo (<?= $total_catalogo ?>)</li>
        </ul>
    </div>
</main>

</body>
</html>
