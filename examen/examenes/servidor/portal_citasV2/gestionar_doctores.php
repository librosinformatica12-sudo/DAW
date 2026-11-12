<?php

require_once __DIR__ . '\AccesoDatos.php';

// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio)
session_start();
// PASO B: Inicializar el array de doctores 'lista_doctores'en la sesión si no existe aún
if(!isset($_SESSION['lista_doctores'])) {
    $_SESSION['lista_doctores'] = [];

    $doctoresExistentes = [];
    //LEO DE LA BASE DE DATOS LOS DOCTORES QUE YA EXISTAN
    $doctoresExistentes = PA_ListaDoctores();
    //Y LOS METO EN $_SESSION
    foreach ($doctoresExistentes as $indice => $doctor)
    {
        $nuevo_doctor = [];
        $nuevo_doctor['licencia_doctor'] = $doctor['id'];
        $nuevo_doctor['nombre_doctor'] = $doctor['nombre'];
        $nuevo_doctor['apellidos_doctor'] = $doctor['apellidos'];

        $_SESSION['lista_doctores'] [] = $nuevo_doctor;
    }
}

// PASO D: Si se recibe una nueva alta de doctor por $_POST, se procesa
if(isset($_POST['licencia_doctor']) && 
   isset($_POST['nombre_doctor']) &&
   isset($_POST['apellidos_doctor'])) {
    // 1. Capturamos los datos que viajan en el POST
    $nuevo_doctor = [];
    $nuevo_doctor['licencia_doctor'] = $_POST['licencia_doctor'];
    $nuevo_doctor['nombre_doctor'] = $_POST['nombre_doctor'];
    $nuevo_doctor['apellidos_doctor'] = $_POST['apellidos_doctor'];
    // 2. Acumulamos la nueva cita dentro del array guardado en la SESIÓN
    $_SESSION['lista_doctores'][] = $nuevo_doctor;
    // 3. Mandar a Base de Datos el nuevo Doctor
    $resultado = PA_AltaDoctor($_POST['licencia_doctor'],
                  $_POST['nombre_doctor'],
                  $_POST['apellidos_doctor']);
    
    if($resultado === "-1"){
        $mensaje = "Nombre vacio";
    }
    else if($resultado === "-2"){
        $mensaje = "Apellidos vacios";
    }
    else if($resultado === "-3"){
        $mensaje = "Nº de licencia duplicado";
    }
    else{
        $mensaje = "Doctor insertado con éxito.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Centro Médico - Cuadro de Doctores</title>
</head>
<body>

    <h2>Gestión de Doctores</h2>

    <?php if (isset($mensaje)): ?>
        <p style="color: green; font-weight: bold;"><?php echo $mensaje; ?></p>
    <?php endif; ?>

    <h3>Doctores dados de alta (Total: <?php echo count($_SESSION['lista_doctores']) ?>)</h3>

    <!-- PASO E: Renderizar la tabla si existen citas guardadas -->
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
                <?php foreach ($_SESSION['lista_doctores'] as $indice => $doctor): ?>
                    <tr>
                        <td><?php echo $doctor['licencia_doctor'] ?></td>
                        <td><?php echo $doctor['nombre_doctor'] ?></td>
                        <td><?php echo $doctor['apellidos_doctor'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <!-- Formulario para grabar en Base de Datos la informacion cargada en SESSION -->
        <!-- <form action="" method="post">
            <input type="hidden" name="accion" value="grabar_todas">
            <button type="submit">
                Grabar todas las citas en BD
            </button>
        </form> -->
        
    <?php else: ?>
        <p>No hay citas agendadas actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <a href="portal_citas.html"> Volver al Portal</a>

</body>
</html>