<?php
    session_start(); // tiene que ir lo primero de todo, antes de cualquier HTML

    $maxIntentos = 10;

    // Empezar partida nueva (primera vez o al pulsar "Volver a jugar")
    if (!isset($_SESSION["numero_secreto"]) || isset($_POST["reiniciar"])) {
        $_SESSION["numero_secreto"] = random_int(1, 100);
        $_SESSION["intentos"]       = 0;
        $_SESSION["historial"]      = [];
        $_SESSION["ganado"]         = false;
    }

    $mensaje = "";

    // Procesar el intento del jugador
    if (isset($_POST["numero"]) && !$_SESSION["ganado"] && $_SESSION["intentos"] < $maxIntentos) {

        $intento = (int) $_POST["numero"];
        $_SESSION["intentos"]++;
        $_SESSION["historial"][] = $intento;

        if ($intento === $_SESSION["numero_secreto"]) {
            $_SESSION["ganado"] = true;
            $mensaje = "¡Correcto! Lo has adivinado en {$_SESSION['intentos']} intentos.";
        } elseif ($intento < $_SESSION["numero_secreto"]) {
            $mensaje = "Te has quedado corto.";
        } else {
            $mensaje = "Te has pasado.";
        }

        if (!$_SESSION["ganado"] && $_SESSION["intentos"] >= $maxIntentos) {
            $mensaje .= " Se te acabaron los intentos. El número era {$_SESSION['numero_secreto']}.";
        }
    }

    // ¿La partida ha terminado?
    $terminado = $_SESSION["ganado"] || $_SESSION["intentos"] >= $maxIntentos;
    
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adivina tu número</title>
</head>
<body>
    <h2>Adivina tu número</h2>
    <p>Introduce un número del 1 al 100. Tienes <?= $maxIntentos ?> intentos.</p>

    <!-- Mensaje del resultado -->
    <?php if ($mensaje !== ""): ?>
        <p><strong><?= $mensaje ?></strong></p>
    <?php endif; ?>

    <!-- Formulario: solo se muestra si la partida sigue en juego -->
    <?php if (!$terminado): ?>
        <form method="post" action="">
            <label for="numero">Número:</label>
            <input type="number" id="numero" name="numero" min="1" max="100" required autofocus><br><br>
            <input type="submit" value="Enviar">
        </form>
        <p>Intentos usados: <?= $_SESSION["intentos"] ?> de <?= $maxIntentos ?></p>
    <?php endif; ?>

    <!-- Números ya probados -->
    <?php if (count($_SESSION["historial"]) > 0): ?>
        <p>Números probados: <?= implode(", ", $_SESSION["historial"]) ?></p>
    <?php endif; ?>

    <br>
    <!-- Botón para reiniciar la partida -->
    <form method="post" action="">
        <button type="submit" name="reiniciar">Volver a jugar</button>
    </form>
</body>
</html>