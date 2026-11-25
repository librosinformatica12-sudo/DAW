<?php
/* ============================================================
   php/cartaDiaria.php
   ------------------------------------------------------------------
   Endpoint del sobre del dia. Antes esta logica estaba dentro de
   inicio.php; ahora vive aqui para que inicio.php solo pinte HTML.

   Flujo: recibe el POST, abre el sobre (o guarda el error) en la
   sesion y vuelve a inicio.php (patron POST -> Redirect -> GET).
   ============================================================ */

require_once __DIR__ . "/../src/AccesoDatos.php";

// Solo tiene sentido si hay sesion; si no, a la pagina de entrada.
if (!esta_logueado()) {
    header("Location: ../index.php");
    exit;
}

// Solo por POST.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inicio.php");
    exit;
}

$usuario_id = (int) $_SESSION["usuario_id"];
$espera_dia = 24 * 60 * 60;

$usuario = obtenerUsuarioPorId($usuario_id);
$faltan  = segundosRestantes($usuario["ultima_obtencion"] ?? null, $espera_dia);

if ($faltan > 0) {
    $_SESSION["sobre_flash"] = [
        "tipo"    => "error",
        "mensaje" => "Ya has abierto tu sobre del día. Vuelve en unas horas.",
    ];
} else {
    $azar = dinosaurioAleatorio();

    if ($azar) {
        anadirCarta($usuario_id, (int) $azar["id"]);
        marcarUltimaObtencion($usuario_id);

        $_SESSION["sobre_flash"] = [
            "tipo"    => "ok",
            "mensaje" => "¡Has conseguido una carta nueva!",
            "carta"   => $azar,
        ];
    } else {
        $_SESSION["sobre_flash"] = [
            "tipo"    => "error",
            "mensaje" => "No hay dinosaurios disponibles.",
        ];
    }
}

header("Location: ../inicio.php");
exit;
