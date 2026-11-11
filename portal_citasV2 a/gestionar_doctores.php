<?php

/*
 * gestionar_doctores.php (portal_citasV2 - versión buena)
 *
 * ÍNDICE RÁPIDO (Ctrl+F):
 *   SESION
 *   CARGAR DOCTORES DE LA BD
 *   ALTA DE DOCTOR
 *   CODIGOS DE RESULTADO
 *   RENDER TABLA
 */
 // =====================================================================
 // EXPLICACIÓN GENERAL (ampliada para estudiar)
 // Página que da de alta doctores y muestra la lista. Recibe el formulario de
 // portal_citas.html (licencia_doctor, nombre_doctor, apellidos_doctor).
 // FLUJO: session_start -> cargar doctores de la BD (solo la 1ª vez) -> si hay
 // alta en el POST: llamar a PA_AltaDoctor y traducir el código a un mensaje
 // -> HTML con la tabla.
 // Es la versión sencilla de gestionar_citas.php: mismo esquema, sin edición.
 //
 // AVISOS:
 //   - El doctor se añade a la lista de la sesión ANTES de saber si la BD lo
 //     aceptó, así que con un error (-1, -2, -3) aparece en la tabla sin estar
 //     guardado en la BD.
 //   - La lista solo se carga de MySQL la primera vez de cada sesión.
 //
 // [EXAMEN] Típico: completar el isset del alta, la llamada a PA_AltaDoctor, los
 // mensajes de cada código o el foreach de la tabla.
 // =====================================================================

// Incluye la conexión y las funciones PA_. OJO: la barra invertida solo funciona
// en Windows; portable: __DIR__ . '/AccesoDatos.php'.
require_once __DIR__ . '\AccesoDatos.php';

// ===== SESION =====
// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio).
// Inicia o reanuda la sesión. Obligatorio antes de usar $_SESSION y antes de
// cualquier salida HTML.
session_start();
// ===== CARGAR DOCTORES DE LA BD =====
// PASO B: Inicializar el array de doctores 'lista_doctores' en la sesión si no existe.
// Solo se carga desde MySQL la primera vez; después se reutiliza la sesión.
// Solo se carga desde la BD si la sesión aún no tiene la lista. Diferencia con
// gestionar_citas.php, que recarga siempre. Consecuencia: un doctor creado por
// otra vía no aparece hasta que se cierre la sesión.
if(!isset($_SESSION['lista_doctores'])) {
    $_SESSION['lista_doctores'] = [];

    $doctoresExistentes = [];
    //LEO DE LA BASE DE DATOS LOS DOCTORES QUE YA EXISTAN
    // Devuelve un array de doctores con las claves 'id', 'nombre' y 'apellidos'.
    $doctoresExistentes = PA_ListaDoctores();
    //Y LOS METO EN $_SESSION
    foreach ($doctoresExistentes as $indice => $doctor)
    {
        $nuevo_doctor = [];
        // Se traducen las claves que devuelve AccesoDatos (id, nombre, apellidos) a
        // las que usa la tabla de esta página (licencia_doctor, nombre_doctor, ...).
        $nuevo_doctor['licencia_doctor'] = $doctor['id'];
        $nuevo_doctor['nombre_doctor'] = $doctor['nombre'];
        $nuevo_doctor['apellidos_doctor'] = $doctor['apellidos'];

        $_SESSION['lista_doctores'] [] = $nuevo_doctor;
    }
}

// ===== ALTA DE DOCTOR =====
// PASO D: Si se recibe una nueva alta de doctor por $_POST, se procesa.
// ALTA DE DOCTOR: se ejecuta si llegan los 3 campos del formulario. A diferencia
// de las citas, aquí NO se comprueba $_POST['accion'].
if(isset($_POST['licencia_doctor']) && 
   isset($_POST['nombre_doctor']) &&
   isset($_POST['apellidos_doctor'])) {
    // 1. Capturamos los datos que viajan en el POST
    $nuevo_doctor = [];
    $nuevo_doctor['licencia_doctor'] = $_POST['licencia_doctor'];
    $nuevo_doctor['nombre_doctor'] = $_POST['nombre_doctor'];
    $nuevo_doctor['apellidos_doctor'] = $_POST['apellidos_doctor'];
    // 2. Acumulamos la nueva cita dentro del array guardado en la SESIÓN
    // OJO: se añade a la sesión antes de saber si la BD lo acepta. Si PA_AltaDoctor
    // devuelve un error, el doctor sale en la tabla aunque NO esté guardado.
    $_SESSION['lista_doctores'][] = $nuevo_doctor;
    // 3. Mandar a Base de Datos el nuevo Doctor
    // Llama al procedure AltaDoctor. $resultado es un STRING ("0", "-1", ...).
    // Orden de argumentos: licencia, nombre, apellidos.
    $resultado = PA_AltaDoctor($_POST['licencia_doctor'],
                  $_POST['nombre_doctor'],
                  $_POST['apellidos_doctor']);
    
    // ===== CODIGOS DE RESULTADO =====
    // 0 = insertado; -1 nombre, -2 apellidos, -3 licencia duplicada.
    // Comparación === con comillas porque el código llega como texto.
    // Códigos de AltaDoctor: -1 nombre vacío, -2 apellidos vacíos, -3 licencia duplicada.
    if($resultado === "-1"){
        $mensaje = "Nombre vacio";
    }
    else if($resultado === "-2"){
        $mensaje = "Apellidos vacios";
    }
    else if($resultado === "-3"){
        $mensaje = "Nº de licencia duplicado";
    }
    // else = cualquier otro valor (normalmente "0") -> alta correcta.
    else{
        $mensaje = "Doctor insertado con éxito.";
    }
}
// Fin del bloque PHP. A partir de aquí es HTML con PHP incrustado.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Cuadro de Doctores</title>
</head>
<body>

    <h2>Gestión de Doctores</h2>

    <!-- Muestra el mensaje solo si alguna rama de arriba lo definió. -->
    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <!-- ===== RENDER TABLA ===== -->
    <h3>Doctores dados de alta (Total: <?php echo count($_SESSION['lista_doctores']) ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen doctores guardados -->
    <!-- Solo se pinta la tabla si la lista no está vacía (empty devuelve true si está -->
    <!-- vacía; el ! lo niega). -->
    <?php if (!empty($_SESSION['lista_doctores'])): ?>
        <table border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>NºLicencia</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                </tr>
            </thead>
            <tbody>
                <!-- Una fila <tr> por cada doctor. Las claves de $doctor son las creadas arriba. -->
                <?php foreach ($_SESSION['lista_doctores'] as $indice => $doctor): ?>
                    <tr>
                        <!-- echo imprime el valor de la clave. Una columna <td> por cada dato. -->
                        <td><?php echo $doctor['licencia_doctor'] ?></td>
                        <td><?php echo $doctor['nombre_doctor'] ?></td>
                        <td><?php echo $doctor['apellidos_doctor'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <!-- Lo que viene a continuación es código HTML comentado: no se ejecuta ni se pinta. -->
        <!-- Formulario para grabar en Base de Datos la informacion cargada en SESSION -->
        <!-- <form action="" method="post">
            <input type="hidden" name="accion" value="grabar_todas">
            <button type="submit">
                Grabar todas las citas en BD
            </button>
        </form> -->
        
    <!-- else: se muestra cuando no hay doctores. (El texto habla de citas porque se -->
    <!-- copió de la otra página: debería decir doctores.) -->
    <?php else: ?>
        <p>No hay citas agendadas actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> Volver al Portal</a>

</body>
</html>