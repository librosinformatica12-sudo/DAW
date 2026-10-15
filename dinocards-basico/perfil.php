<?php
/* ============================================================
   perfil.php
   ------------------------------------------------------------------
   Muestra los datos del usuario y sus estadisticas.
   ============================================================ */

require_once "conexion.php";
pedir_login();

$usuario_id = (int) $_SESSION["usuario_id"];

// --- Datos del usuario ---
$consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$consulta->bind_param("i", $usuario_id);
$consulta->execute();
$usuario = $consulta->get_result()->fetch_assoc();
$consulta->close();

// --- Estadisticas ---
$resumen = $conexion->query(
    "SELECT COUNT(*) AS total, COUNT(DISTINCT dinosaurio_id) AS especies
     FROM colecciones WHERE usuario_id = " . $usuario_id
)->fetch_assoc();

$total_cartas   = (int) $resumen["total"];
$total_especies = (int) $resumen["especies"];
$total_catalogo = (int) $conexion->query("SELECT COUNT(*) AS total FROM dinosaurios")->fetch_assoc()["total"];
$porcentaje     = $total_catalogo > 0 ? round($total_especies / $total_catalogo * 100) : 0;

$registro    = $usuario["fecha_registro"];
$ultima      = $usuario["ultima_obtencion"];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Mi perfil</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>

<?php require "menu.php"; ?>

<main>
    <div class="tarjeta">
        <h1><?= escapar($usuario["username"]) ?></h1>
        <p>Correo: <?= escapar($usuario["email"]) ?></p>
        <p>Miembro desde: <?= $registro !== null ? date("d/m/Y", strtotime($registro)) : "—" ?></p>
    </div>

    <div class="tarjeta">
        <h2>Estadísticas</h2>
        <ul class="resumen">
            <li><b><?= $total_cartas ?></b>cartas guardadas</li>
            <li><b><?= $total_especies ?></b>especies distintas</li>
            <li><b><?= $porcentaje ?>%</b>del catálogo (<?= $total_catalogo ?>)</li>
        </ul>
    </div>

    <div class="tarjeta">
        <h2>Datos de la cuenta</h2>
        <ul class="datos">
            <li><b>Usuario:</b> <?= escapar($usuario["username"]) ?></li>
            <li><b>Correo:</b> <?= escapar($usuario["email"]) ?></li>
            <li><b>Identificador:</b> #<?= $usuario_id ?></li>
            <li><b>Miembro desde:</b> <?= $registro !== null ? date("d/m/Y H:i", strtotime($registro)) : "—" ?></li>
            <li><b>Última carta obtenida:</b> <?= $ultima !== null ? date("d/m/Y H:i", strtotime($ultima)) : "Todavía ninguna" ?></li>
        </ul>
        <p>
            <a class="boton" href="inicio.php">Ir a mi panel</a>
            <a class="boton secundario" href="cerrar.php">Cerrar sesión</a>
        </p>
    </div>
</main>

</body>
</html>
