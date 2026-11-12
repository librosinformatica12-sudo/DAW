<?php
/*
 * gestionar_citas.php (portal_citasV2 - Versión corregida)
 */

require_once __DIR__ . '/AccesoDatos.php';

// ===== SESION =====
session_start();

$mensaje = "";

// ===== EDICION: mostrar_zona_edicion =====
$estaEditando = 0;
if(isset($_POST['accion']) && $_POST['accion'] === 'mostrar_zona_edicion')
{    
    $estaEditando = 1;
    $_SESSION['citaModificandose'] = $_POST['idCita'];
}

// ===== EDICION: actualizar_cita =====
if(isset($_POST['accion']) && $_POST['accion'] === 'actualizar_cita')
{
    // Validar si el doctor existe antes de actualizar
    $licenciaBuscar = (int)($_POST['doctor_asignado']);
    $doctorEncontrado = PA_DameDoctor($licenciaBuscar);
    
    if(count($doctorEncontrado) == 0) {
        $mensaje = "Error: El número de licencia del doctor no existe.";
        $estaEditando = 1; // Mantiene el formulario abierto para corregir
    } else {
        $resultado = PA_ActualizarCita(
            (int)$_SESSION['citaModificandose'],
            $_POST['paciente'],
            $_POST['especialidad'],
            $_POST['fecha_hora'],
            (int)$_POST['doctor_asignado']
        );

        if($resultado === -1) {
            $mensaje = "Nombre vacío";
            $estaEditando = 1;
        } else if($resultado === -2) {
            $mensaje = "Especialidad vacía";
            $estaEditando = 1;
        } else if($resultado === -3) {
            $mensaje = "Fecha vacía";
            $estaEditando = 1;
        } else if($resultado === -4) {
            $mensaje = "La fecha no puede ser anterior al presente";
            $estaEditando = 1;
        } else {
            $mensaje = "Cita actualizada con éxito.";
            $estaEditando = 0;
            unset($_SESSION['citaModificandose']);
        }
    }
}

// ===== BORRAR TODAS =====
if(isset($_POST['accion']) && $_POST['accion'] === 'borrar_todas') {
    unset($_SESSION['lista_citas']);
    $_SESSION['lista_citas'] = [];
    $mensaje = "Todas las citas han sido canceladas.";
}

// ===== ALTA DE CITA =====
if(isset($_POST['accion']) && $_POST['accion'] === 'alta_cita') {
    
    // Validar primero si el doctor existe
    $licenciaBuscar = (int)($_POST['doctor_asignado']);
    $doctorEncontrado = PA_DameDoctor($licenciaBuscar);
    
    if(count($doctorEncontrado) == 0) {
        $mensaje = "Doctor no encontrado. Pruebe otro número de licencia.";
    } else {
        $resultado = PA_AltaCita(
            $_POST['paciente'],
            $_POST['especialidad'],
            $_POST['fecha_hora'],
            (int)$_POST['doctor_asignado']
        );
                
        // Códigos de resultado
        if($resultado === -1) {
            $mensaje = "Nombre vacío";
        } else if($resultado === -2) {
            $mensaje = "Especialidad vacía";
        } else if($resultado === -3) {
            $mensaje = "Fecha vacía";
        } else if($resultado === -4) {
            $mensaje = "Fecha anterior al presente";
        } else {
            $mensaje = "Cita insertada con éxito.";
        }
    }
}

// ===== CARGAR CITAS DE LA BD =====
$_SESSION['lista_citas'] = [];
$citasExistentes = PA_ListaCitas();

foreach ($citasExistentes as $cita)
{
    $nueva_cita = [];
    $nueva_cita['id_cita'] = $cita['id_cita'];
    $nueva_cita['paciente'] = $cita['nombre_paciente'];
    $nueva_cita['especialidad'] = $cita['especialidad'];
    $nueva_cita['fecha_hora'] = $cita['fecha'];
    $nueva_cita['doctor_asignado'] = $cita['licencia_doctor'];

    $_SESSION['lista_citas'][] = $nueva_cita;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Mis Citas</title>
</head>
<body>

    <h2>Gestión de Citas Médicas</h2>

    <?php if (!empty($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <!-- ===== RENDER TABLA ===== -->
    <h3>Citas Registradas en la Base de Datos (Total: <?php echo count($_SESSION['lista_citas']); ?>)</h3>

    <?php if (!empty($_SESSION['lista_citas'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Id Cita</th>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha y Hora</th>
                    <th>Doctor asignado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_citas'] as $cita): ?>
                    <tr>
                        <td><?php echo $cita['id_cita']; ?></td>
                        <td><?php echo $cita['paciente']; ?></td>
                        <td><?php echo $cita['especialidad']; ?></td>
                        <td><?php echo $cita['fecha_hora']; ?></td>
                        <td><?php echo $cita['doctor_asignado']; ?></td>
                        <td> 
                            <form action="gestionar_citas.php" method="post" style="margin:0;">
                                <input type="hidden" name="idCita" value="<?php echo $cita['id_cita']; ?>">
                                <input type="hidden" name="accion" value="mostrar_zona_edicion">
                                <button type="submit">Modificar Cita</button>
                            </form>  
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>

        <!-- ===== RENDER FORM ACTUALIZAR ===== -->
        <div <?= $estaEditando ? '' : 'hidden' ?>>
            <form action="gestionar_citas.php" method="post">
                <h4>Actualizar Cita ID: <?php echo isset($_SESSION['citaModificandose']) ? $_SESSION['citaModificandose'] : ''; ?></h4>
                
                <label for="paciente">Nombre del Paciente:</label><br>
                <input type="text" id="paciente" name="paciente" required><br><br>
                
                <label for="especialidad">Especialidad:</label><br>
                <select id="especialidad" name="especialidad" required>
                    <option value="">-- Seleccione una opción --</option>
                    <option value="Medicina General">Medicina General</option>
                    <option value="Pediatría">Pediatría</option>
                    <option value="Dermatología">Dermatología</option>
                    <option value="Traumatología">Traumatología</option>
                </select><br><br>

                <label for="fecha_hora">Fecha y Hora de la Cita:</label><br>
                <input type="datetime-local" id="fecha_hora" name="fecha_hora" required><br><br>

                <label for="n_licencia">Asignar Doctor (Nº Licencia):</label><br>
                <input type="text" id="n_licencia" name="doctor_asignado" required><br><br>
                
                <input type="hidden" name="accion" value="actualizar_cita">
                <button type="submit">Actualizar Cita</button>
            </form>
        </div>
        <br>

        <!-- Formulario para vaciar la sesión/citas -->
        <form action="gestionar_citas.php" method="post">
            <input type="hidden" name="accion" value="borrar_todas">
            <button type="submit">Cancelar todas las citas (Memoria)</button>
        </form>

    <?php else: ?>
        <p>No hay citas agendadas actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html">+ Pedir otra cita</a>

</body>
</html>