<?php
// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio)

// PASO B: Inicializar el array de citas 'lista_citas'en la sesión si no existe aún

// PASO C: Comprobar si se ha solicitado borrar todas las citas

// PASO D: Si se recibe una nueva cita individual por $_POST, se procesa

    // 1. Capturamos los datos que viajan en el POST
    
    // 2. Acumulamos la nueva cita dentro del array guardado en la SESIÓN


// Mensaje para mostrar
$mensaje = "El código de esta página no está completo.";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Mis Citas</title>
</head>
<body>

    <h2>Gestión de Citas Médicas</h2>

    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h3>Citas Registradas en esta Sesión (Total: <?php echo count($_SESSION['lista_citas']); ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen citas guardadas -->
    <?php if (!empty($_SESSION['lista_citas'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha y Hora</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_citas'] as $indice => $cita): ?>
                    <tr>
                        <td><?php echo "algo" ?></td>
                        <td><?php echo "algo" ?></td>
                        <td><?php echo "algo" ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>

        <!-- Formulario para vaciar la sesión -->
        <form action="gestionar_citas.php" method="post">
            <input type="hidden" name="accion" value="borrar_todas">
            <button type="submit">
                Cancelar todas las citas
            </button>
        </form>

    <?php else: ?>
        <p>No hay citas agendadas actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> +Pedir otra cita</a>

</body>
</html>