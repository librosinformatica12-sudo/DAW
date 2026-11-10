<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'clinica';
$username = 'guilleDB';
$password = '1234';

// Desactivamos los warnings automáticos para gestionarlos nosotros mediante excepciones
mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);


/* =========================================================================
   CASO 1: FUNCIONES QUE DEVUELVEN UN CONJUNTO DE FILAS (SELECT)
   Ejemplos: PA_ListaCitas, PA_ListaDoctores, PA_DameDoctor
   - Usan $stmt->get_result() para leer los registros devueltos por MySQL.
   - Recorren los resultados con un bucle while y fetch_assoc().
   ========================================================================= */

function PA_ListaCitas()
{
    global $connection;

    $sql = 'CALL ListaCitas()';
    $stmt = $connection->prepare($sql);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $citas = [];
    // Comprobamos si la consulta ha devuelto columnas/filas de datos ($field_count > 0)
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result(); // Obtenemos el objeto mysqli_result
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $cita['id_cita'] = $fila['id_cita'];
                $cita['nombre_paciente'] = $fila['nombre_paciente'];
                $cita['especialidad'] = $fila['especialidad'];
                $cita['fecha'] = $fila['fecha'];
                $cita['licencia_doctor'] = $fila['fk_doctor'];

                $citas[] = $cita; // Añadimos cada fila al array final
            }
            $resultadoSelect->free(); // Liberamos memoria del resultado
        }
    }
    $stmt->close(); // Cerramos el statement

    return $citas; // Devolvemos el array con todas las citas
}


function PA_ListaDoctores()
{
    global $connection;

    $sql = 'CALL ListaDoctores()';
    $stmt = $connection->prepare($sql);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $doctores = [];
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $doctor['id'] = $fila['n_licencia'];
                $doctor['nombre'] = $fila['nombre_doctor'];
                $doctor['apellidos'] = $fila['apellidos_doctor'];

                $doctores[] = $doctor;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $doctores;
}


function PA_DameDoctor(int $n_licencia)
{
    global $connection;

    // Este procedimiento recibe 1 parámetro de entrada (IN), por eso lleva un '?'
    $sql = 'CALL DameDoctor(?)';
    $stmt = $connection->prepare($sql);

    // Vinculamos el parámetro de entrada: 'i' porque es un entero (integer)
    $stmt->bind_param('i', $n_licencia);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $doctores = [];
    // Al hacer un SELECT por ID, también devuelve filas, así que usamos get_result igual que en las listas
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $doctor['id'] = $fila['n_licencia'];
                $doctor['nombre'] = $fila['nombre_doctor'];
                $doctor['apellidos'] = $fila['apellidos_doctor'];

                $doctores[] = $doctor;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $doctores;
}



/* =========================================================================
   CASO 2: FUNCIONES DE ACCIÓN CON PARÁMETRO DE SALIDA (INSERT / UPDATE con OUT)
   Ejemplos: PA_AltaDoctor, PA_AltaCita, PA_ActualizarCita
   - Llevan parámetros IN ('?') y un parámetro OUT en la llamada (ej. '@resultado').
   - NO usan get_result() porque no devuelven tablas, sino que consultan 
     la variable de sesión de MySQL (@resultado) justo después de cerrar el statement.
   ========================================================================= */

function PA_AltaDoctor(string $n_licencia, string $nombre, string $apellidos)
{
    global $connection;

    // El último parámetro('@resultado') recoge el valor de salida (OUT) del procedimiento en MySQL
    $sql = 'CALL AltaDoctor(?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Vinculamos los 3 parámetros de entrada ('sss' de string, string, string)
    $stmt->bind_param('sss', $n_licencia, $nombre, $apellidos);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    
    // IMPORTANTE: Hay que cerrar el statement ANTES de consultar la variable @resultado
    $stmt->close();

    // Consultamos la variable de usuario de MySQL donde se guardó el OUT
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    // Devolvemos el código de resultado (por ejemplo, 0 si fue bien, o -1, -2... si hubo error)
    return $fila['resultado'];
}


function PA_AltaCita(string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado)
{
    global $connection;

    // Procedimiento con 4 parámetros IN (?) y 1 parámetro OUT (@resultado)
    $sql = 'CALL AltaCita(?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Tipos: string (s), string (s), string (s), integer (i) -> 'sssi'
    $stmt->bind_param('sssi', $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    // Recuperamos el OUT exactamente igual que en el alta de doctor
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}


function PA_ActualizarCita(string $idCita, string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado)
{
    global $connection;

    // Procedimiento de modificación con 5 parámetros IN (?) y 1 OUT (@resultado)
    $sql = 'CALL ModificarCita(?, ?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    // Tipos: string, string, string, string, int -> 'ssssi'
    $stmt->bind_param('ssssi', $idCita, $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    // Recuperamos el parámetro OUT de la modificación
    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}