<?php
// ============================================================
// 1. session_start() SIEMPRE va lo primero de todo, antes de
//    cualquier salida HTML. Sin esto, $_SESSION no funcionaría
//    y perderíamos el número secreto en cada intento.
// ============================================================
session_start();

// ============================================================
// 2. ¿Hay que reiniciar la partida?
//    - Si el usuario pulsa "Jugar de nuevo" (llega $_POST['reiniciar'])
//    - O si es la primera vez que entra (no existe $_SESSION['numero'])
//    En ambos casos, generamos número nuevo y reseteamos todo.
// ============================================================
if (isset($_POST['reiniciar']) || !isset($_SESSION['numero'])) {
    $_SESSION['numero']    = rand(1, 100); // número secreto
    $_SESSION['intentos']  = 0;            // contador de intentos usados
    $_SESSION['historial'] = [];           // array vacío con los números probados
    $_SESSION['ganado']    = false;        // ¿ya acertó?
}

$mensaje = "";          // texto que le mostramos al jugador tras cada intento
$maxIntentos = 10;

// ============================================================
// 3. Procesar un intento normal (el jugador envió un número)
//    Comprobamos isset() porque en la primera carga de la página
//    $_POST['intento'] todavía no existe.
// ============================================================
if (isset($_POST['intento']) && !$_SESSION['ganado']) {

    // Los datos de un formulario siempre llegan como texto (string),
    // así que los convertimos a número entero con (int)
    $intento = (int) $_POST['intento'];

    // Solo contamos el intento si el jugador todavía tiene turnos
    if ($_SESSION['intentos'] < $maxIntentos) {
        $_SESSION['intentos']++;
        $_SESSION['historial'][] = $intento; // añadir al final del array (BONUS: historial)

        if ($intento === $_SESSION['numero']) {
            $_SESSION['ganado'] = true;
            $mensaje = "🎉 ¡Correcto! El número era $intento. Lo adivinaste en {$_SESSION['intentos']} intento(s).";
        } elseif ($intento < $_SESSION['numero']) {
            $mensaje = "Te has quedado corto ⬆️";
        } else {
            $mensaje = "Te has pasado ⬇️";
        }
    }
}

// ¿Se acabaron los intentos sin acertar?
$sinIntentos = ($_SESSION['intentos'] >= $maxIntentos && !$_SESSION['ganado']);
if ($sinIntentos) {
    $mensaje = "😢 Se acabaron tus intentos. El número era {$_SESSION['numero']}.";
}
?>