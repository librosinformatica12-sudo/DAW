<?php
/* ============================================================
   php/abrirSobrePrueba.php
   ------------------------------------------------------------------
   Endpoint del sobre de prueba (4 cartas). Antes esta logica estaba
   dentro de inicio.php; ahora vive aqui.

   Flujo: recibe el POST, abre el sobre (o guarda el error) en la
   sesion y vuelve a inicio.php (patron POST -> Redirect -> GET).
   ============================================================ */

require_once __DIR__ . "/../src/AccesoDatos.php";

if (!esta_logueado()) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../inicio.php");
    exit;
}

$usuario_id    = (int) $_SESSION["usuario_id"];
$espera_prueba = 120; // 2 minutos
$cuantas       = 4;   // nº de cartas por sobre de prueba

$usuario = obtenerUsuarioPorId($usuario_id);
// no dar doble carta prueba
$faltan  = segundosRestantes($usuario["carta_prueba"] ?? null, $espera_prueba);

if ($faltan > 0) {
    $_SESSION["sobre_flash"] = [
        "tipo"    => "error",
        "mensaje" => "Aún no puedes abrir el sobre de prueba. Espera a que termine el contador.",
    ];
} else { // dar sobre preua = 4 cartas
    $dinos = dinosauriosAleatorios($cuantas);

    if (count($dinos) >= $cuantas) {
        foreach ($dinos as $d) {
            anadirCarta($usuario_id, (int) $d["id"]);
        }
        marcarCartaPrueba($usuario_id);

        $_SESSION["sobre_flash"] = [
            "tipo"    => "ok",
            "mensaje" => "¡Has conseguido " . count($dinos) . " cartas de prueba!",
            "cartas"  => $dinos,
        ];
    } else {
        $_SESSION["sobre_flash"] = [
            "tipo"    => "error",
            "mensaje" => "No hay dinosaurios suficientes en el catálogo.",
        ];
    }
}

header("Location: ../inicio.php");
exit;
