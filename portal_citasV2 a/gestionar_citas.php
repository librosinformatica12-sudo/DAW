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
 // =====================================================================
 // EXPLICACIÓN GENERAL (ampliada para estudiar)
 // Página que gestiona las citas: lista, edita, cancela todas y da de alta.
 // Todo se decide con el campo oculto $_POST['accion'] que envía cada formulario:
 //    mostrar_zona_edicion -> botón "Modificar Cita" de cada fila
 //    actualizar_cita      -> botón "Actualizar Cita" del formulario de edición
 //    borrar_todas         -> botón "Cancelar todas las citas"
 //    alta_cita            -> alta de una cita nueva (la comprueba el PASO D)
 //
 // FLUJO (de arriba abajo, en cada petición):
 //   1) session_start  2) edición  3) RECARGAR las citas desde la BD a la sesión
 //   4) validar el doctor  5) borrar_todas  6) alta  7) HTML que pinta la tabla.
 //
 // SESIÓN: $_SESSION es un array que PHP conserva entre peticiones (mediante una
 // cookie). Aquí se usa para guardar la lista de citas y qué cita se está editando.
 //
 // AVISOS (fallos que conviene conocer):
 //   - El formulario de cita de portal_citas.html NO envía accion=alta_cita, así que
 //     el PASO D no se ejecutaría (ver el aviso junto a ese bloque).
 //   - Tras header('Location: ...') falta exit(), y el script sigue ejecutándose.
 //
 // [EXAMEN] Típico: completar session_start, el isset de alguna rama, la llamada
 // PA_ con sus argumentos, el mensaje de algún código, o el foreach de la tabla.
 // =====================================================================

// Incluye AccesoDatos.php (conexión + funciones PA_). require_once = no se
// carga dos veces y, si falta el archivo, detiene el script.
// OJO: la barra invertida solo funciona en Windows; portable sería
// __DIR__ . '/AccesoDatos.php'.
require_once __DIR__ . '\AccesoDatos.php';

// ===== SESION =====
// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio).
// session_start() crea la sesión o reanuda la existente (cookie PHPSESSID).
// Debe ir ANTES de cualquier salida HTML y antes de usar $_SESSION.
session_start();

// ===== EDICION: mostrar_zona_edicion =====
// Si el usuario pulsa "Modificar Cita", se guarda el id y se muestra el formulario.
// $estaEditando funciona como un interruptor: 1 = mostrar el formulario de edición,
// 0 = ocultarlo. Más abajo en el HTML decide si se pone el atributo hidden.
$estaEditando = 0;
// isset() evita el aviso si no existe la clave; === compara valor y tipo.
// Esta rama se activa al pulsar "Modificar Cita" en una fila de la tabla.
if(isset($_POST['accion']) && $_POST['accion'] === 'mostrar_zona_edicion')
{    
    $estaEditando = 1;
    // Se guarda en SESIÓN qué cita se edita. Es necesario porque en la petición
    // siguiente (al pulsar "Actualizar Cita") el id ya no viaja en el POST: el
    // formulario de edición no tiene un campo idCita.
    $_SESSION['citaModificandose'] = $_POST['idCita'];
}
else
{
    $estaEditando = 0;
}

// ===== EDICION: actualizar_cita =====
// Recoge los campos del formulario de edición y llama al procedure ModificarCita.
// Se ejecuta ANTES de recargar la lista desde la BD, para que la tabla ya
// muestre el cambio recién guardado.
if(isset($_POST['accion']) && $_POST['accion'] === 'actualizar_cita')
{

    // Llama al procedure ModificarCita con el id guardado en sesión y los campos
    // del formulario. OJO: no se guarda el valor devuelto, así que el usuario no
    // ve ningún mensaje de error. Para mostrarlo harías, como en el alta:
    //   $resultado = PA_ActualizarCita(...);  y comparar con "-1", "-2"...
    PA_ActualizarCita($_SESSION['citaModificandose'],
                    $_POST['paciente'],
                    $_POST['especialidad'],
                    $_POST['fecha_hora'],
                    $_POST['doctor_asignado']);
    $estaEditando = 0;
    // Esta variable no se usa en ningún otro sitio del archivo (sobra).
    $idCitaModificandose = 0;
}

// ===== CARGAR CITAS DE LA BD =====
// PASO B: Inicializar el array de citas 'lista_citas' en la sesión.
// IMPORTANTE: se vacía y se vuelve a llenar desde MySQL en cada petición.
// Se vacía la lista de la sesión y se vuelve a llenar desde MySQL EN CADA
// petición. Así la tabla siempre refleja lo que hay realmente en la BD.
$_SESSION['lista_citas'] = [];

//CARGAR LAS CITAS EXISTENTES YA
$citasExistentes = [];
//LLAMADA AL PROCEDURE ListaCitas: devuelve todas las filas de la tabla.
// Devuelve un array de citas con las claves: id_cita, nombre_paciente,
// especialidad, fecha y licencia_doctor (ver PA_ListaCitas en AccesoDatos.php).
$citasExistentes = PA_ListaCitas();
//Y LOS METO EN $_SESSION
// foreach recorre el array: $indice = posición (no se usa), $cita = cada cita.
// Aquí se RENOMBRAN las claves para la vista: nombre_paciente -> paciente,
// fecha -> fecha_hora, licencia_doctor -> doctor_asignado.
foreach ($citasExistentes as $indice => $cita)
{
    $nueva_cita = [];
    $nueva_cita['id_cita'] = $cita['id_cita'];
    $nueva_cita['paciente'] = $cita['nombre_paciente'];
    $nueva_cita['especialidad'] = $cita['especialidad'];
    $nueva_cita['fecha_hora'] = $cita['fecha'];
    $nueva_cita['doctor_asignado'] = $cita['licencia_doctor'];

    // [] añade el elemento al FINAL del array de la sesión.
    $_SESSION['lista_citas'] [] = $nueva_cita;
}


// ===== VALIDAR DOCTOR =====
// PASO B2: Averiguar si existe el doctor asignado antes de insertar la cita.
// Se ejecuta siempre que el POST traiga un doctor (alta o actualización):
// comprueba que exista llamando a PA_DameDoctor (SELECT por licencia).
if(isset($_POST['doctor_asignado']))
{
    // (int) convierte el texto del formulario en entero (PA_DameDoctor pide int).
    // Si el usuario escribe letras, el resultado es 0.
    $licenciaBuscar = (int)($_POST['doctor_asignado']);
    $doctorEncontrado = PA_DameDoctor($licenciaBuscar);
    
    if(count($doctorEncontrado) == 0)
    {
        $mensaje = "Doctor no encontrado. Pruebe otro numero de licencia.";
        // header('Location: ...') redirige el navegador a otra página.
        // OJO 1: falta exit() justo después; sin él el script SIGUE y todavía ejecutaría
        // el alta o la actualización (la FOREIGN KEY haría fallar el INSERT).
        // OJO 2: al redirigir se empieza una petición nueva y $mensaje se pierde.
        header('Location: portal_citas.html'); //Vuelta al inicio
    }
}
// ===== BORRAR TODAS =====
// PASO C: Comprobar si se ha solicitado borrar todas las citas de la sesión.
// OJO: esto vacía solo la lista de la SESIÓN, no la base de datos. Como en
// cada petición se recarga desde la BD, las citas volverían a aparecer.
if(isset($_POST['accion']) && $_POST['accion'] === 'borrar_todas') {
    // Borrar todas las citas de la sesión
    // unset() elimina la variable; después se vuelve a crear como array vacío para
    // que count() y empty() más abajo funcionen sin errores.
    unset($_SESSION['lista_citas']);
    $_SESSION['lista_citas'] = [];
    // Mensaje para mostrar
    $mensaje = "Todas las citas han sido canceladas.";
}


// ===== ALTA DE CITA =====
// PASO D: Si se recibe una nueva cita individual por $_POST, se procesa.
// ALTA DE CITA: se ejecuta si llegan los 4 campos Y accion vale 'alta_cita'.
// AVISO IMPORTANTE: en portal_citas.html el campo oculto accion=alta_cita está
// en el formulario del DOCTOR, y el formulario de la CITA no lo tiene. Por eso
// esta condición no se cumple al reservar una cita (además PHP avisa de que
// $_POST['accion'] no existe). [EXAMEN] Hay que ponerlo en el formulario de cita.
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
    // OJO: se añade a la sesión SIEMPRE, aunque luego la BD devuelva un error
    // (-1 ... -4). Además esta cita no tiene clave 'id_cita' (la genera MySQL con
    // auto_increment), así que la tabla mostraría "Undefined array key". Se
    // corrige solo en la petición siguiente, cuando se recarga desde la BD.
    $_SESSION['lista_citas'][] = $nueva_cita;

    // 3. Mandar a Base de Datos la nueva Cita
    // Llama al procedure AltaCita. $resultado es un STRING ("0", "-1", ...).
    // Orden de argumentos: paciente, especialidad, fecha, doctor.
    $resultado = PA_AltaCita($_POST['paciente'],
                            $_POST['especialidad'],
                            $_POST['fecha_hora'],
                            $_POST['doctor_asignado']);
                
    // ===== CODIGOS DE RESULTADO =====
    // El procedure devuelve 0 si todo va bien y negativos si hay error.
    // Se compara con === y con COMILLAS porque PA_AltaCita devuelve texto. Con
    // el entero -1 y === nunca se cumpliría (int no es igual a string).
    // Los códigos son los de AltaCita en el SQL: -1 nombre, -2 especialidad,
    // -3 fecha vacía, -4 fecha pasada.
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
    // else = cualquier otro valor, normalmente "0" -> la cita se insertó bien.
    else{
        $mensaje = "Cita insertada con éxito.";
    }
}
// Fin del bloque PHP. Lo que sigue es HTML con trozos de PHP incrustados.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Mis Citas</title>
</head>
<body>

    <h2>Gestión de Citas Médicas</h2>

    <!-- Sintaxis alternativa de PHP en HTML: if ... endif. $mensaje solo existe si -->
    <!-- alguna rama de arriba la definió; isset() evita el aviso si no existe. -->
    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <!-- ===== RENDER TABLA ===== -->
    <h3>Citas Registradas en esta Sesión (Total: <?php echo count($_SESSION['lista_citas']); ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen citas guardadas -->
    <!-- empty() es true si la variable no existe, es null, 0, cadena vacía o un array -->
    <!-- vacío. El ! lo niega: solo se pinta la tabla si hay alguna cita. -->
    <!-- Termina en el else y el endif del final del archivo. -->
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
                <!-- Una fila de la tabla por cada cita. Las claves usadas ($cita['paciente'], etc.) -->
                <!-- son las que se crearon al cargar de la BD en la parte de arriba. -->
                <?php foreach ($_SESSION['lista_citas'] as $indice => $cita): ?>
                    <tr>
                        <!-- echo imprime el valor (el punto y coma final es opcional antes del cierre). -->
                        <!-- Aquí no se escapa el texto: lo seguro sería htmlspecialchars($cita['paciente']). -->
                        <td><?php echo $cita['id_cita'] ?></td>
                        <td><?php echo $cita['paciente'] ?></td>
                        <td><?php echo $cita['especialidad'] ?></td>
                        <td><?php echo $cita['fecha_hora'] ?></td>
                        <td><?php echo $cita['doctor_asignado'] ?></td>
                        <td> 
                            <!-- Un mini-formulario POR FILA. Al pulsar "Modificar Cita" envía idCita y -->
                            <!-- accion=mostrar_zona_edicion a esta misma página. -->
                            <form action="gestionar_citas.php" method="post">
                                <!-- Campo oculto con el id de la cita de esa fila. El value no lleva comillas: -->
                                <!-- funciona porque es un número, pero lo correcto es value="...". -->
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
        <!-- Operador ternario: condicion ? valor_si_verdadero : valor_si_falso. -->
        <!-- Si se está editando no se escribe nada (el div se ve); si no, se escribe el -->
        <!-- atributo hidden y el formulario de edición queda oculto. -->
        <!-- La etiqueta corta de echo equivale a echo. -->
        <div <?= $estaEditando ? '' : 'hidden' ?>>
            <!-- Formulario para actualizar una cita -->
            <!-- action vacío = se envía a la misma página (gestionar_citas.php). -->
            <!-- Este formulario NO lleva idCita: el id se recupera de $_SESSION['citaModificandose']. -->
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
                
                <!-- Este valor activa el bloque actualizar_cita de la parte superior de la página. -->
                <input type="hidden" name="accion" value="actualizar_cita">
                <button type="submit">Actualizar Cita</button>
            </form>
        </div>
        <!-- Formulario para vaciar la sesión: envía accion=borrar_todas -->
        <form action="gestionar_citas.php" method="post">
            <!-- Este formulario envía accion=borrar_todas y activa el PASO C de arriba. -->
            <input type="hidden" name="accion" value="borrar_todas">
            <button type="submit">
                Cancelar todas las citas
            </button>
        </form>
        

    <!-- else de la sintaxis alternativa: se muestra cuando NO hay citas. -->
    <!-- (Se cierra con endif justo debajo.) -->
    <?php else: ?>
        <p>No hay citas agendadas actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> +Pedir otra cita</a>

</body>
</html>