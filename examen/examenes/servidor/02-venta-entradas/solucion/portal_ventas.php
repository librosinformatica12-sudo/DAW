<?php

/*
 * portal_ventas.php
 * Muestra el formulario para comprar entradas.
 * El select se rellena con los conciertos que hay en MySQL.
 */

require_once __DIR__ . '/AccesoDatos.php';

$conciertos = PA_ListaConciertos();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Venta de entradas</title>
</head>
<body>

    <h2>Venta de entradas</h2>

    <?php if (empty($conciertos)): ?>
        <p>No hay conciertos disponibles.</p>
    <?php else: ?>
        <form action="gestionar_ventas.php" method="post">
            <label for="id_concierto">Concierto:</label>
            <select name="id_concierto" id="id_concierto" required>
                <?php foreach ($conciertos as $concierto): ?>
                    <?php
                        $id = (int)$concierto['id_concierto'];
                        $disponibles = (int)$concierto['entradas_disponibles'];
                        $precio = number_format((float)$concierto['precio'], 2, ',', '.');
                    ?>
                    <option value="<?php echo $id; ?>" <?php echo $disponibles <= 0 ? 'disabled' : ''; ?>>
                        <?php echo htmlspecialchars($concierto['nombre_banda']); ?>
                        - <?php echo htmlspecialchars($concierto['fecha_concierto']); ?>
                        - <?php echo $precio; ?> EUR
                        - Disponibles: <?php echo $disponibles; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <br><br>

            <label for="cantidad">Cantidad:</label>
            <input type="number" name="cantidad" id="cantidad" min="1" value="1" required>

            <br><br>

            <button type="submit">Comprar entradas</button>
        </form>
    <?php endif; ?>

    <br><hr>
    <a href="gestionar_ventas.php">Ver ventas realizadas</a>

</body>
</html>
