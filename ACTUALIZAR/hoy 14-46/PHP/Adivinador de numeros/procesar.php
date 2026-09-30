<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adivina tu número</title>
</head>
<body>
    <h2>Adivina tu número</h2>
    <p>Introduce un número del 1 al 100. Tienes 10 intentos.</p>
        <!-- Formulario que envía los datos a esta misma página -->
        <form method="post" action="">
            <label for="numero">Número:</label>
            <input type="number" id="numero" name="numero" min="1" max="100" required><br><br>

            <input type="submit" value="Enviar">
        </form>
    <br>
    <!-- Botón para reiniciar la partida -->
    <form method="post" action="">
        <button type="submit" name="reiniciar">Volver a jugar</button>
    </form>

<?php
    session_start(); // debe ir lo primero de todo, antes del DOCTYPE

    if (!isset($_SESSION["numero_secreto"]) || isset($_POST['reiniciar'])) {
        $_SESSION["numero_secreto"] = random_int(1, 100);
        $_SESSION["intentos"]       = 0;
        $_SESSION["historial"]      = [];
        $_SESSION["ganado"]         = false;
    }

    $mensaje = "";
    $maxIntentos = 10;

    echo $_SESSION["numero_secreto"];

    if (isset($_POST['numero']) && !$_SESSION["ganado"] && $_SESSION["intentos"] < $maxIntentos) {

    $intento = (int) $_POST["numero"];
    $_SESSION["intentos"]++;
    $_SESSION["historial"][] = $intento;

    if ($intento === $_SESSION["numero_secreto"]) {
        $_SESSION["ganado"] = true;
        $mensaje = "¡Correcto!! Lo has adivinado en {$_SESSION['intentos']} intentos.";
    } elseif ($intento < $_SESSION["numero_secreto"]) {
        $mensaje = "Te has quedado corto";
    } else {
        $mensaje = "Te has pasado";
    }

    if (!$_SESSION["ganado"] && $_SESSION["intentos"] >= $maxIntentos) {
        $mensaje .= " Se te acabaron los intentos. El número era {$_SESSION['numero_secreto']}.";
    }
}
?>


</body>
</html>