<?php
/* ============================================================
   php/cartaDiaria.php
   ------------------------------------------------------------------
   Endpoint del sobre del dia.

   Flujo: recibe el POST, abre el sobre (src/SobreService.php), guarda el
   resultado en la sesion y vuelve a inicio.php (POST -> Redirect -> GET).
   ============================================================ */

require_once __DIR__ . "/../src/bootstrap.php";

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

if (!csrf_valido()) {
    $_SESSION["sobre_flash"] = [
        "tipo"    => "error",
        "mensaje" => "La página ha caducado. Inténtalo de nuevo.",
    ];
} else {
    $_SESSION["sobre_flash"] = abrirSobreDelDia((int) $_SESSION["usuario_id"]);
}

header("Location: ../inicio.php");
exit;
