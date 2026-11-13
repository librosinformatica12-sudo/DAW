<?php

require_once __DIR__ . '\AccesoDatos.php';

session_start();

$estaEditando = false;
if (isset($_POST['accion']) && $_POST['accion'] === 'mostrar_zona_edicion') {
    $_SESSION['billete_modificandose'] = (int)($_POST['idBillete'] ?? 0);
    $estaEditando = true;
} elseif (
    isset($_POST['accion']) &&
    $_POST['accion'] === 'actualizar_billete' &&
    isset($_SESSION['billete_modificandose'])
) {
    $destino = trim($_POST['destino'] ?? '');
    $fecha = $_POST['fecha_hora'] ?? '';
    $documento = trim($_POST['documento_viajero'] ?? '');

    if (count(PA_DameViajero($documento)) === 0) {
        $mensaje = 'Viajero no encontrado. Compruebe el documento.';
        $estaEditando = true;
    } else {
        $resultado = PA_ActualizarBillete(
            (int)$_SESSION['billete_modificandose'],
            $destino,
            $fecha,
            $documento
        );
        if ($resultado === '-1') {
            $mensaje = 'El destino está vacío.';
        } elseif ($resultado === '-2') {
            $mensaje = 'La fecha del vuelo está vacía.';
        } elseif ($resultado === '-3') {
            $mensaje = 'La fecha del vuelo debe ser posterior al momento actual.';
        } elseif ($resultado === '-4') {
            $mensaje = 'Viajero no encontrado. Compruebe el documento.';
        } else {
            $mensaje = 'Billete actualizado con éxito.';
        }
        unset($_SESSION['billete_modificandose']);
    }
} elseif (isset($_POST['accion']) && $_POST['accion'] === 'borrar_todas') {
    PA_CancelarTodosBilletes();
    $mensaje = 'Todos los billetes han sido cancelados.';
}
/*PREGUNTA 6 - Implementa aquí la lógica de PHP que permita borrar UN SOLO BILLETE*/
elseif (
    isset($_POST['accion']) &&
    $_POST['accion'] === 'borrar_billete' &&
    isset($_POST['idBillete'])
) {
    PA_CancelarBillete((int)$_POST['idBillete']);
    $mensaje = 'Billete cancelado con éxito.';
}


if (
    isset($_POST['accion']) &&
    $_POST['accion'] === 'alta_billete'
) {
    $destino = trim($_POST['destino'] ?? '');
    $fecha = $_POST['fecha_hora'] ?? '';
    $documento = trim($_POST['documento_viajero'] ?? '');

    if (count(PA_DameViajero($documento)) === 0) {
        $mensaje = 'Viajero no encontrado. Compruebe el documento.';
    } else {
        $resultado = PA_AltaBillete($destino, $fecha, $documento);
        if ($resultado === '-1') {
            $mensaje = 'El destino está vacío.';
        } elseif ($resultado === '-2') {
            $mensaje = 'La fecha del vuelo está vacía.';
        } elseif ($resultado === '-3') {
            $mensaje = 'La fecha del vuelo debe ser posterior al momento actual.';
        } elseif ($resultado === '-4') {
            $mensaje = 'Viajero no encontrado. Compruebe el documento.';
        } else {
            $mensaje = 'Billete dado de alta con éxito.';
        }
    }
}

$_SESSION['lista_billetes'] = [];
$billetes = PA_ListaBilletes();

foreach ($billetes as $billete) {
    /** PREGUNTA 1 - Consigue guardar en el objeto SESSION la colección de billetes
     *              - que devuelve la llamada a datos.
     */
    $_SESSION['lista_billetes'][] = [
        'id_billete' => $billete['id_billete'],
        'destino' => $billete['destino'],
        'fecha' => $billete['fecha'],
        'documento_viajero' => $billete['documento_viajero'],
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aerolínea - Billetes</title>
</head>
<body>
    <h2>Gestión de Billetes de Vuelo</h2>

    <?php if (isset($mensaje)): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h3>Billetes registrados (Total: <?php echo count($_SESSION['lista_billetes']); ?>)</h3>

    <?php if (!empty($_SESSION['lista_billetes'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Id billete</th>
                    <th>Destino</th>
                    <th>Fecha y hora</th>
                    <th>Documento del viajero</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_billetes'] as $billete): ?>
                    <tr>
                        <td><?php echo (int)$billete['id_billete']; ?></td>
                        <!-- PREGUNTA 2 - Logra que salga la informacion de los campos que faltan de los billetes -->
                        <td><?php echo htmlspecialchars($billete['destino']); ?></td>
                        <td><?php echo htmlspecialchars($billete['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($billete['documento_viajero']); ?></td>
                        <td>
                            <form action="gestionar_billetes.php" method="post">
                                <input type="hidden" name="idBillete" value="<?php echo (int)$billete['id_billete']; ?>">
                                <input type="hidden" name="accion" value="mostrar_zona_edicion">
                                <button type="submit">Modificar billete</button>
                            </form>
                            <!-- PREGUNTA 5 - Implementa otro botón dentro de otro miniformulario que permita eliminar
                                            el billete de su línea correspondiente y SOLO ese billete --> 
                            <form action="gestionar_billetes.php" method="post">
                                <input type="hidden" name="idBillete" value="<?php echo (int)$billete['id_billete']; ?>">
                                <input type="hidden" name="accion" value="borrar_billete">
                                <button type="submit">Eliminar billete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <br>
        <div <?php echo $estaEditando ? '' : 'hidden'; ?>>
            <h3>Modificar billete</h3>
            <form action="gestionar_billetes.php" method="post">
                <label for="destino_edicion">Destino:</label><br>
                <input type="text" id="destino_edicion" name="destino" maxlength="80" required>
                <br><br>

                <label for="fecha_edicion">Fecha y hora del vuelo:</label><br>
                <input type="datetime-local" id="fecha_edicion" name="fecha_hora" required>
                <br><br>

                <label for="viajero_edicion">Documento del viajero:</label><br>
                <input type="text" id="viajero_edicion" name="documento_viajero" maxlength="20" required>
                <br><br>

                <input type="hidden" name="accion" value="actualizar_billete">
                <button type="submit">Actualizar billete</button>
            </form>
        </div>

        <form action="gestionar_billetes.php" method="post">
            <input type="hidden" name="accion" value="borrar_todas">
            <button type="submit">Cancelar todos los billetes</button>
        </form>
    <?php else: ?>
        <p>No hay billetes registrados actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_aerolinea.html">Dar de alta otro billete o viajero</a>
</body>
</html>
