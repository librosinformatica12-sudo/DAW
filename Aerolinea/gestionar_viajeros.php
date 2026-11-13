<?php

require_once __DIR__ . '\AccesoDatos.php';

session_start();

$_SESSION['lista_viajeros'] = [];
foreach (PA_ListaViajeros() as $viajero) {
    $_SESSION['lista_viajeros'][] = [
        'documento' => $viajero['documento'],
        'nombre' => $viajero['nombre'],
        'apellidos' => $viajero['apellidos'],
    ];
}

if (isset($_POST['accion']) && $_POST['accion'] === 'alta_viajero') {
    $documento = trim($_POST['documento'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellidos = trim($_POST['apellidos'] ?? '');

    $resultado = PA_AltaViajero($documento, $nombre, $apellidos);
    if ($resultado === '-1') {
        $mensaje = 'Nombre vacío.';
    } elseif ($resultado === '-2') {
        $mensaje = 'Apellidos vacíos.';
    } elseif ($resultado === '-3') {
        $mensaje = 'Documento de viajero duplicado.';
    } elseif ($resultado === '-4') {
        $mensaje = 'El documento del viajero está vacío.';
    } else {
        $mensaje = 'Viajero dado de alta con éxito.';
        $_SESSION['lista_viajeros'] = [];
        $viajeros = PA_ListaViajeros();
        foreach ($viajeros as $viajero) {
            $_SESSION['lista_viajeros'][] = [
                'documento' => $viajero['documento'],
                'nombre' => $viajero['nombre'],
                'apellidos' => $viajero['apellidos'],
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aerolínea - Viajeros</title>
</head>
<body>
    <h2>Gestión de Viajeros</h2>

    <?php if (isset($mensaje)): ?>
        <p><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h3>Viajeros dados de alta (Total: <?php echo count($_SESSION['lista_viajeros']); ?>)</h3>

    <?php if (!empty($_SESSION['lista_viajeros'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_viajeros'] as $viajero): ?>
                    <tr>
                        <td><?php echo $viajero['documento']; ?></td>
                        <td><?php echo $viajero['nombre']; ?></td>
                        <td><?php echo $viajero['apellidos']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No hay viajeros dados de alta.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_aerolinea.html">Volver al portal</a>
</body>
</html>
