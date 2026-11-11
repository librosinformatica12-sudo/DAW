<?php

/*
 * gestionar_citas.php (portal_citasV2 - versión buena)
 *
 * ÍNDICE RÁPIDO (Ctrl+F):
 *   SESION
 *   EDICION: mostrar_zona_edicion
 *   EDICION: actualizar_cita
 *   CARGAR CITAS DE LA BD
 *   VALIDAR DOCTOR
 *   BORRAR TODAS
 *   ALTA DE CITA
 *   CODIGOS DE RESULTADO
 *   RENDER TABLA
 *   RENDER FORM ACTUALIZAR
 *
 * Orden del flujo: POST -> sesión -> base de datos -> HTML.
 */

require_once __DIR__ . '\AccesoDatos.php';

// ===== SESION =====
// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio).
session_start();

// ===== EDICION: mostrar_zona_edicion =====
// Si el usuario pulsa "Modificar Cita", se guarda el id y se muestra el formulario.
$estaEditando = 0;
if(isset($_POST['accion']) && $_POST['accion'] === 'mostrar_zona_edicion')
{    
    $estaEditando = 1;
    $_SESSION['citaModificandose'] = $_POST['idCita'];
}
else
{
    $estaEditando = 0;
}

// ===== EDICION: actualizar_cita =====
// Recoge los campos del formulario de edición y llama al procedure ModificarCita.
if(isset($_POST['accion']) && $_POST['accion'] === 'actualizar_cita')
{

    PA_ActualizarCita($_SESSION['citaModificandose'],
                    $_POST['paciente'],
                    $_POST['especialidad'],
                    $_POST['fecha_hora'],
                    $_POST['doctor_asignado']);
    $estaEditando = 0;
    $idCitaModificandose = 0;
}

// ===== CARGAR CITAS DE LA BD =====
// PASO B: Inicializar el array de citas 'lista_citas' en la sesión.
// IMPORTANTE: se vacía y se vuelve a llenar desde MySQL en cada petición.
$_SESSION['lista_citas'] = [];

//CARGAR LAS CITAS EXISTENTES YA
$citasExistentes = [];
//LLAMADA AL PROCEDURE ListaCitas: devuelve todas las filas de la tabla.
$citasExistentes = PA_ListaCitas();
//Y LOS METO EN $_SESSION
foreach ($citasExistentes as $indice => $cita)
{
    $nueva_cita = [];
    $nueva_cita['id_cita'] = $cita['id_cita'];
    $nueva_cita['paciente'] = $cita['nombre_paciente'];
    $nueva_cita['especialidad'] = $cita['especialidad'];
    $nueva_cita['fecha_hora'] = $cita['fecha'];
    $nueva_cita['doctor_asignado'] = $cita['licencia_doctor'];

    $_SESSION['lista_citas'] [] = $nueva_cita;
}


// ===== VALIDAR DOCTOR =====
// PASO B2: Averiguar si existe el doctor asignado antes de insertar la cita.
if(isset($_POST['doctor_asignado']))
{
    $licenciaBuscar = (int)($_POST['doctor_asignado']);
    $doctorEncontrado = PA_DameDoctor($licenciaBuscar);
    
    if(count($doctorEncontrado) == 0)
    {
        $mensaje = "Doctor no encontrado. Pruebe otro numero de licencia.";
        header('Location: portal_citas.html'); //Vuelta al inicio
    }
}
// ===== BORRAR TODAS =====
// PASO C: Comprobar si se ha solicitado borrar todas las citas de la sesión.
if(isset($_POST['accion']) && $_POST['accion'] === 'borrar_todas') {
    // Borrar todas las citas de la sesión
    unset($_SESSION['lista_citas']);
    $_SESSION['lista_citas'] = [];
    // Mensaje para mostrar
    $mensaje = "Todas las citas han sido canceladas.";
}


// ===== ALTA DE CITA =====
// PASO D: Si se recibe una nueva cita individual por $_POST, se procesa.
if(isset($_POST['paciente']) &&
    isset($_POST['especialidad']) &&
    isset($_POST['fecha_hora']) &&
    isset($_POST['doctor_asignado']) &&
    $_POST['accion'] === 'alta_cita') {
    // 1. Capturamos los datos que viajan en el POST
    $nueva_cita = [];
    $nueva_cita['paciente'] = $_POST['paciente'];
    $nueva_cita['especialidad'] = $_POST['especialidad'];
    $nueva_cita['fecha_hora'] = $_POST['fecha_hora'];
    $nueva_cita['doctor_asignado'] = $_POST['doctor_asignado'];
    
    // 2. Acumulamos la nueva cita dentro del array guardado en la SESIÓN
    $_SESSION['lista_citas'][] = $nueva_cita;

    // 3. Mandar a Base de Datos la nueva Cita
    $resultado = PA_AltaCita($_POST['paciente'],
                            $_POST['especialidad'],
                            $_POST['fecha_hora'],
                            $_POST['doctor_asignado']);
                
    // ===== CODIGOS DE RESULTADO =====
    // El procedure devuelve 0 si todo va bien y negativos si hay error.
    if($resultado === "-1"){
        $mensaje = "Nombre vacio";
    }
    else if($resultado === "-2"){
        $mensaje = "Especialidad vacia";
    }
    else if($resultado === "-3"){
        $mensaje = "Fecha vacía";
    }
    else if($resultado === "-4"){
        $mensaje = "Fecha anterior al presente";
    }
    else{
        $mensaje = "Cita insertada con éxito.";
    }
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

    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <!-- ===== RENDER TABLA ===== -->
    <h3>Citas Registradas en esta Sesión (Total: <?php echo count($_SESSION['lista_citas']); ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen citas guardadas -->
    <?php if (!empty($_SESSION['lista_citas'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>Id Cita</th>
                    <th>Paciente</th>
                    <th>Especialidad</th>
                    <th>Fecha y Hora</th>
                    <th>Doctor asignado</th>
                    <th>Accion</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['lista_citas'] as $indice => $cita): ?>
                    <tr>
                        <td><?php echo $cita['id_cita'] ?></td>
                        <td><?php echo $cita['paciente'] ?></td>
                        <td><?php echo $cita['especialidad'] ?></td>
                        <td><?php echo $cita['fecha_hora'] ?></td>
                        <td><?php echo $cita['doctor_asignado'] ?></td>
                        <td> 
                            <form action="gestionar_citas.php" method="post">
                                <input type="hidden" name="idCita" value=<?php echo $cita['id_cita'] ?> >
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
        <!-- El atributo hidden oculta el formulario hasta pulsar "Modificar Cita". -->
        <div <?= $estaEditando ? '' : 'hidden' ?>>
            <!-- Formulario para actualizar una cita -->
            <form action="" method="post">
                <!-- Campo para el nombre del paciente -->
                <label for="paciente">Nombre del Paciente:</label><br>
                <input type="text" id="paciente" name="paciente">
                
                <br><br>
                <!-- Selección de especialidad -->
                <label for="especialidad">Especialidad:</label><br>
                <select id="especialidad" name="especialidad">
                    <option value="">-- Seleccione una opción --</option>
                    <option value="Medicina General">Medicina General</option>
                    <option value="Pediatría">Pediatría</option>
                    <option value="Dermatología">Dermatología</option>
                    <option value="Traumatología">Traumatología</option>
                </select><br><br>

                <!-- Campo para fecha y hora -->
                <label for="fecha_hora">Fecha y Hora de la Cita:</label><br>
                <input type="datetime-local" id="fecha_hora" name="fecha_hora">
                <br><br>

                <!-- Doctor que atenderá -->
                <label for="n_licencia">Asignar Doctor:</label><br>
                <input type="text" id="n_licencia" name="doctor_asignado">
                <br><br>
                
                <input type="hidden" name="accion" value="actualizar_cita">
                <button type="submit">Actualizar Cita</button>
            </form>
        </div>
        <!-- Formulario para vaciar la sesión: envía accion=borrar_todas -->
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