<?php

/*
 * gestionar_ventas.php
 * Procesa la compra de entradas y muestra:
 *   - el mensaje de resultado,
 *   - el estado de los conciertos,
 *   - las ventas realizadas.
 */

require_once __DIR__ . '/AccesoDatos.php';

$mensaje = '';
$tipoMensaje = '';

// Solo se procesa la compra cuando el formulario se envia por POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // (int) convierte el valor recibido a numero entero.
    $idConcierto = (int)($_POST['id_concierto'] ?? 0);
    $cantidad = (int)($_POST['cantidad'] ?? 0);

    if ($idConcierto <= 0) {
        $mensaje = 'Selecciona un concierto valido.';
        $tipoMensaje = 'error';
    } elseif ($cantidad <= 0) {
        $mensaje = 'La cantidad debe ser mayor que 0.';
        $tipoMensaje = 'error';
    } else {
        try {
            // La funcion devuelve el codigo OUT del procedure.
            $resultado = PA_ComprarEntradas($idConcierto, $cantidad);

            switch ((int)$resultado) {
                case 0:
                    $mensaje = 'Compra realizada correctamente.';
                    $tipoMensaje = 'ok';
                    break;
                case -1:
                    $mensaje = 'El concierto no existe.';
                    $tipoMensaje = 'error';
                    break;
                case -2:
                    $mensaje = 'La cantidad debe ser mayor que 0.';
                    $tipoMensaje = 'error';
                    break;
                case -3:
                    $mensaje = 'No hay entradas suficientes para ese concierto.';
                    $tipoMensaje = 'error';
                    break;
                case -4:
                    $mensaje = 'El concierto ya ha pasado.';
                    $tipoMensaje = 'error';
                    break;
                default:
                    $mensaje = 'No se pudo realizar la compra.';
                    $tipoMensaje = 'error';
            }
        } catch (RuntimeException $e) {
            $mensaje = 'Error: ' . $e->getMessage();
            $tipoMensaje = 'error';
        }
    }
}

// Siempre se leen los datos actuales desde MySQL.
$conciertos = PA_ListaConciertos();
$entradas = PA_ListaEntradas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestion de ventas</title>
</head>
<body>

    <h2>Gestion de ventas</h2>

    <?php if ($mensaje !== ''): ?>
        <p style="font-weight: bold; color: <?php echo $tipoMensaje === 'ok' ? 'green' : 'red'; ?>;">
            <?php echo htmlspecialchars($mensaje); ?>
        </p>
    <?php endif; ?>

    <h3>Estado de los conciertos</h3>

    <?php if (empty($conciertos)): ?>
        <p>No hay conciertos.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Banda</th>
                    <th>Fecha</th>
                    <th>Precio</th>
                    <th>Aforo</th>
                    <th>Vendidas</th>
                    <th>Disponibles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($conciertos as $concierto): ?>
                    <tr>
                        <td><?php echo (int)$concierto['id_concierto']; ?></td>
                        <td><?php echo htmlspecialchars($concierto['nombre_banda']); ?></td>
                        <td><?php echo htmlspecialchars($concierto['fecha_concierto']); ?></td>
                        <td><?php echo number_format((float)$concierto['precio'], 2, ',', '.'); ?> EUR</td>
                        <td><?php echo (int)$concierto['aforo']; ?></td>
                        <td><?php echo (int)$concierto['entradas_vendidas']; ?></td>
                        <td><?php echo (int)$concierto['entradas_disponibles']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h3>Ventas realizadas</h3>

    <?php if (empty($entradas)): ?>
        <p>Todavia no hay ventas.</p>
    <?php else: ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID venta</th>
                    <th>Banda</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entradas as $entrada): ?>
                    <tr>
                        <td><?php echo (int)$entrada['id_entrada']; ?></td>
                        <td><?php echo htmlspecialchars($entrada['nombre_banda']); ?></td>
                        <td><?php echo (int)$entrada['cantidad']; ?></td>
                        <td><?php echo number_format((float)$entrada['total'], 2, ',', '.'); ?> EUR</td>
                        <td><?php echo htmlspecialchars($entrada['fecha_compra']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <br><hr>
    <a href="portal_ventas.php">Volver a comprar entradas</a>

</body>
</html>
