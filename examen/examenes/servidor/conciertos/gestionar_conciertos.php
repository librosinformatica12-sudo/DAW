<?php

/*
 * gestionar_conciertos.php
 * Esta página:
 *   1. Recibe los datos del formulario del portal.
 *   2. Valida los datos en PHP.
 *   3. Llama a PA_AltaConcierto() para insertar en MySQL.
 *   4. Vuelve a leer la lista desde MySQL y la muestra en una tabla.
 */

// require_once carga AccesoDatos.php una sola vez, aunque la página se incluya varias veces.
require_once __DIR__ . '\AccesoDatos.php';

// Variable que guarda el mensaje que se muestra al usuario.
$mensaje = '';

// isset($_POST['accion']) comprueba que el formulario se ha enviado.
if (isset($_POST['accion']) && $_POST['accion'] === 'alta_concierto') {
    // ?? devuelve '' si el campo no existe; trim() quita espacios al principio y al final.
    $nombre = trim($_POST['nombre_concierto'] ?? '');
    $fechaEntrada = trim($_POST['fecha_hora'] ?? '');

    // (float) convierte el precio recibido a número decimal.
    $precio = (float)($_POST['precio_concierto'] ?? 0);

    // Validaciones en PHP antes de llamar a MySQL.
    if ($nombre === '') {
        $mensaje = 'El nombre no puede estar vacío.';
    } elseif ($fechaEntrada === '') {
        $mensaje = 'La fecha no puede estar vacía.';
    } elseif ($precio <= 0) {
        $mensaje = 'El precio debe ser mayor que 0.';
    } else {
        // datetime-local envía 2026-11-12T21:00; lo pasamos a formato MySQL.
        $fecha = date('Y-m-d H:i:s', strtotime($fechaEntrada));

        // Llama a la función que ejecuta el procedure AltaConcierto.
        $resultado = PA_AltaConcierto($nombre, $fecha, $precio);

        // El procedure devuelve un código; (int) lo convierte a entero.
        switch ((int)$resultado) {
            case 0:
                $mensaje = 'Concierto insertado con éxito.';
                break;
            case -1:
                $mensaje = 'El nombre no puede estar vacío.';
                break;
            case -2:
                $mensaje = 'La fecha no puede estar vacía.';
                break;
            case -3:
                $mensaje = 'La fecha debe ser futura.';
                break;
            case -4:
                $mensaje = 'El precio debe ser mayor que 0.';
                break;
            default:
                $mensaje = 'No se pudo insertar el concierto.';
        }
    }
}

// Siempre se lee la lista desde MySQL, así la página coincide con la base de datos.
$conciertos = PA_ListaConcierto();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de conciertos</title>
</head>
<body>

    <h2>Gestión de Conciertos</h2>

    <?php if ($mensaje !== ''): ?>
        <!-- htmlspecialchars evita que el texto se interprete como HTML. -->
        <p style="color: green; font-weight: bold;"><?php echo htmlspecialchars($mensaje); ?></p>
    <?php endif; ?>

    <h3>Conciertos registrados (Total: <?php echo count($conciertos) ?>)</h3>

    <?php if (!empty($conciertos)): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Nombre Concierto</th>
                    <th>Fecha y Hora</th>
                    <th>Precio Concierto</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conciertos as $concierto): ?>
                    <tr>
                        <!-- Estas claves son las columnas que devuelve ListaConcierto. -->
                        <td><?php echo htmlspecialchars($concierto['nombre_banda']) ?></td>
                        <td><?php echo htmlspecialchars($concierto['fecha_concierto']) ?></td>
                        <td><?php echo htmlspecialchars($concierto['precio']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
    <?php else: ?>
        <p>No hay conciertos dados de alta actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_conciertos.html"> Volver al Portal Conciertos</a>

</body>
</html>
