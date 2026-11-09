<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'clinica';
$username = 'root';
$password = '1234';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

if ($connection->connect_error) {
    die("Error de conexión a la base de datos: " . $connection->connect_error);
}

function PA_AltaDoctor(string $n_licencia, string $n_nombre, string $n_apellido){
    global $connection;

    // Llamamos al procedimiento pasando los 3 datos y recibiendo el OUT en @resultado
    $sql = "CALL AltaDoctor(?, ?, ?, @resultado)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error en prepare (AltaDoctor): " . $connection->error);
    }

    $stmt->bind_param("sss", $n_licencia, $n_nombre, $n_apellido);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException("Error al ejecutar el procedimiento: " . $error);
    }
    $stmt->close();

    // Recuperamos el valor del parámetro OUT de MySQL
    $resultado = $connection->query("SELECT @resultado AS resultado");

    if (!$resultado){
        throw new RuntimeException("Error al recuperar el parámetro OUT: " . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    // Retorna 0 (éxito), -1 (nombre vacío), -2 (apellido vacío) o -3 (licencia duplicada)
    return (int)$fila["resultado"];
}

function PA_ListaDoctores(){
    global $connection;

    $sql = "CALL ListaDoctores()";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error en prepare (ListaDoctores): " . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException("Error al ejecutar el procedimiento: " . $error);
    }

    $doctores = [];
    if ($stmt->field_count > 0){
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()){
                $doctor["id"] = $fila["n_licencia"];
                $doctor["nombre"] = $fila["nombre_doctor"];
                $doctor["apellidos"] = $fila["apellido_doctor"];

                $doctores[] = $doctor;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $doctores;
}

function PA_DameDoctor(int $n_licencia){
    global $connection;

    $sql = 'CALL DameDoctor(?)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error en prepare (DameDoctor): " . $connection->error);
    }

    $stmt->bind_param('i', $n_licencia);

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
                $doctor['apellidos'] = $fila['apellido_doctor'];

                $doctores[] = $doctor;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $doctores;
}

function PA_ListaCitas(){
    global $connection;

    $sql = "CALL ListaCitas()";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error en prepare (ListaCitas): " . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException("Error al ejecutar el procedimiento: " . $error);
    }

    $citas = [];
    if ($stmt->field_count > 0) {
        $resultadoSelect = $stmt->get_result();
        if ($resultadoSelect) {
            while ($fila = $resultadoSelect->fetch_assoc()) {
                $cita["id_cita"] = $fila["id_cita"];
                $cita["paciente"] = $fila["nombre_paciente"];
                $cita["especialidad"] = $fila["especialidad"];
                $cita["fecha_hora"] = $fila["fecha"];
                $cita["doctor_asignado"] = $fila["fk_doctor"];

                $citas[] = $cita;
            }
            $resultadoSelect->free();
        }
    }
    $stmt->close();

    return $citas;
}

function PA_AltaCita(string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado){
    global $connection;

    $sql = "CALL AltaCita(?, ?, ?, ?, @resultado)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error en prepare (AltaCita): " . $connection->error);
    }

    $stmt->bind_param("sssi", $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException("Error al ejecutar el procedimiento: " . $error);
    }
    $stmt->close();

    $resultado = $connection->query("SELECT @resultado AS resultado");

    if (!$resultado){
        throw new RuntimeException("Error al recuperar el parámetro OUT: " . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int)$fila["resultado"];
}

function PA_ActualizarCita(string $idCita, string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado){
    global $connection;

    $sql = "CALL ModificarCita(?, ?, ?, ?, ?, @resultado)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException("Error en prepare (ModificarCita): " . $connection->error);
    }

    $stmt->bind_param("ssssi", $idCita, $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException("Error al ejecutar el procedimiento: " . $error);
    }
    $stmt->close();

    $resultado = $connection->query("SELECT @resultado AS resultado");

    if(!$resultado) {
        throw new RuntimeException("Error al recuperar el parámetro OUT: " . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int)$fila["resultado"];
}