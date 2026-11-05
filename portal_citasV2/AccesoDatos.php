<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'clinica';
$username = 'guilleDB';
$password = '1234';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

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
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $cita['id_cita'] = $fila['id_cita'];
                $cita['nombre_paciente'] = $fila['nombre_paciente'];
                $cita['especialidad'] = $fila['especialidad'];
                $cita['fecha'] = $fila['fecha'];
                $cita['licencia_doctor'] = $fila['fk_doctor'];

                $citas[] = $cita;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $citas;
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

    $sql = 'CALL DameDoctor(?)';
    $stmt = $connection->prepare($sql);

    $stmt->bind_param('i',$n_licencia);

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

function PA_AltaDoctor(string $n_licencia, string $nombre ,string $apellidos)
{
    global $connection;

    $sql = 'CALL AltaDoctor(?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

   
    $stmt->bind_param('sss',$n_licencia, $nombre, $apellidos);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}
function PA_AltaCita(string $nombre_paciente,
                    string $especialidad,                    
                    string $fecha,
                    int $doctor_asignado)
{
    global $connection;

    $sql = 'CALL AltaCita(?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

   
    $stmt->bind_param('sssi',$nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}
function PA_ActualizarCita( string $idCita,
                            string $nombre_paciente,
                            string $especialidad,                    
                            string $fecha,
                            int $doctor_asignado)
{
    global $connection;

    $sql = 'CALL ModificarCita(?, ?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

   
    $stmt->bind_param('ssssi',$idCita, $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}