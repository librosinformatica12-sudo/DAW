<?php
session_start();

$mensaje = "";
$juego_terminado = false;

// 1. Generar número secreto al iniciar por primera vez o al reiniciar
if (!isset($_SESSION['numero_secreto']) || isset($_POST['reiniciar'])) {
    $_SESSION['numero_secreto'] = rand(1, 100);
    $_SESSION['intentos'] = 10;
}

// 2. Procesar el formulario cuando se envía un número
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['numero']) && !isset($_POST['reiniciar'])) {
    $intento = (int)$_POST['numero'];
    $_SESSION['intentos']--;

    if ($intento === $_SESSION['numero_secreto']) {
        $intentos_usados = 10 - $_SESSION['intentos'];
        $mensaje = "<p style='color: green; font-weight: bold;'>¡Correcto! Has acertado el número en $intentos_usados intento(s).</p>";
        $juego_terminado = true;
    } elseif ($_SESSION['intentos'] <= 0) {
        $mensaje = "<p style='color: red; font-weight: bold;'>¡Sin intentos! El número era " . $_SESSION['numero_secreto'] . ".</p>";
        $juego_terminado = true;
    } elseif ($intento < $_SESSION['numero_secreto']) {
        $mensaje = "<p style='color: orange;'>Te has quedado corto. Te quedan " . $_SESSION['intentos'] . " intentos.</p>";
    } else {
        $mensaje = "<p style='color: orange;'>Te has pasado. Te quedan " . $_SESSION['intentos'] . " intentos.</p>";
    }
}
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
    <p>Introduce un número del 1 al 100. Tienes 10 intentos.</p>

    <!-- Mostrar respuestas o avisos -->
    <?php echo $mensaje; ?>

    <?php if (!$juego_terminado): ?>
        <!-- Formulario que envía los datos a esta misma página -->
        <form method="post" action="">
            <label for="numero">Número:</label>
            <input type="number" id="numero" name="numero" min="1" max="100" required><br><br>

            <input type="submit" value="Enviar">
        </form>
    <?php endif; ?>

    <br>
    <!-- Botón para reiniciar la partida -->
    <form method="post" action="">
        <button type="submit" name="reiniciar">Volver a jugar</button>
    </form>
</body>
</html>