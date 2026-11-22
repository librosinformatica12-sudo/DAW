<?php
/* ============================================================
   php/abrirSobrePrueba.php
   ------------------------------------------------------------------
   Endpoint del sobre de prueba (4 cartas).

   Flujo: recibe el POST, abre el sobre (src/SobreService.php), guarda el
   resultado en la sesion y vuelve a inicio.php (POST -> Redirect -> GET).
   ============================================================ */

require_once __DIR__ . "/../src/bootstrap.php";

if (!esta_logueado()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inicio.php");
    exit;
}

if (!csrf_valido()) {
    $_SESSION["sobre_flash"] = [
        "tipo"    => "error",
        "mensaje" => "La página ha caducado. Inténtalo de nuevo.",
    ];
} else {
    $_SESSION["sobre_flash"] = abrirSobrePrueba((int) $_SESSION["usuario_id"]);
}

header("Location: ../inicio.php");
exit;
