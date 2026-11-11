<?php

/*
 * AccesoDatos.php (portal_citas - versión antigua)
 *
 * ÍNDICE RÁPIDO (busca estas palabras con Ctrl+F):
 *   CONEXION
 *   PA_AltaDoctor
 *   PA_ListaDoctores
 *   PA_DameDoctor
 *   PA_AltaDita
 *
 * OJO: esta versión es antigua/incompleta. La versión completa y comentada
 * está en portal_citasV2/AccesoDatos.php. Si el examen te pide una parte
 * concreta, busca el nombre de la función en el índice.
 */

// ===== CONEXION =====
// Datos de conexión a MySQL.
$host = '127.0.0.1';
$port = 3306;
$database = 'clinica';
$username = 'oscar';
$password = '1234';

// Desactiva los warnings automáticos de mysqli para controlar los errores a mano.
mysqli_report(MYSQLI_REPORT_OFF);

// El @ evita que PHP muestre el warning si la conexión falla.
$connection = @new mysqli($host, $username, $password, $database, $port);

// ===== FUNCION PA_AltaDoctor =====
// Recibe los datos de un doctor y llama a un procedure de alta.
// En esta versión antigua la llamada no coincide con el procedure AltaDoctor;
// revisa la versión V2 si necesitas el alta funcionando.
function PA_AltaDoctor(string $n_licencia, string $n_nombre, string $n_apellido){
	global $connection;

	// Prepara la llamada al procedure. Los ? son los parámetros de entrada.
	$sql = "CALL AltaCita(?, ?, ?)";
	$stmt = $connection->prepare($sql);

	// Vincula los 3 parámetros como string ("sss").
	$stmt->bind_param("sss", $n_licencia, $n_nombre, $n_apellido);

	if ($stmt->execute()) {
		$error = $stmt->error;
		$stmt->close();
		$two = "Error al ejecutar el procedimiento almacenado: " . $error;
	}
	$stmt->close();

	// Lee el parámetro OUT del procedure.
	$resultado = $connection->query("SELECT @resultado AS resultado");

}

// ===== FUNCION PA_ListaDoctores =====
// Devuelve todos los doctores en un array asociativo.
function PA_ListaDoctores(){
	global $connection;

	// Llama al procedure que hace el SELECT.
	$sql = "CALL ListaDoctores()";
	$stmt = $connection->prepare($sql);

	// get_result() obtiene las filas; fetch_all() las convierte en array.
	$result = $stmt->get_result();
	$doctores = $result->fetch_all(MYSQLI_ASSOC);

	$stmt->close();
	return $doctores;


}

// ===== FUNCION PA_ListaCitas =====
// Devuelve todas las citas en un array asociativo.
function PA_ListaCitas(){
    global $connection;

    // Llama al procedimiento almacenado que hace el SELECT de las citas
    $sql = "CALL ListaCitas()";
    $stmt = $connection->prepare($sql);

    // Es necesario ejecutar la sentencia antes de obtener los resultados
    $stmt->execute();
    
    // get_result() obtiene las filas; fetch_all() las convierte en array asociativo.
    $result = $stmt->get_result();
    $citas = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    return $citas;
}

// ===== FUNCION PA_ActualizarCita =====
// Actualiza una cita existente llamando al procedimiento almacenado ModificarCita.
function PA_ActualizarCita(string $id_cita, string $nombre_paciente, string $especialidad, string $fecha, int $doctor_asignado) {
    global $connection;

    // Llama al procedimiento almacenado ModificarCita con su parámetro OUT (@resultado)
    $sql = "CALL ModificarCita(?, ?, ?, ?, ?, @resultado)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        return -99; // Error al preparar la sentencia
    }

    $idInt = (int)$id_cita;
    
    // Vinculamos los parámetros: "isssi" (integer, string, string, string, integer)
    $stmt->bind_param("isssi", $idInt, $nombre_paciente, $especialidad, $fecha, $doctor_asignado);
    $stmt->execute();
    $stmt->close();

    // Lee el código de resultado devuelto por el parámetro OUT del procedure
    $resQuery = $connection->query("SELECT @resultado AS resultado");
    if ($resQuery) {
        $row = $resQuery->fetch_assoc();
        $resQuery->free();
        return (int)$row["resultado"];
    }

    return -99;
}

// ===== FUNCION PA_DameDoctor =====
// Busca un doctor por su número de licencia.
// En esta versión antigua no devuelve las filas: solo se usa para comprobar
// si el doctor existe.
// ===== FUNCION PA_DameDoctor =====
// Busca un doctor por su número de licencia y devuelve sus datos.
function PA_DameDoctor(int $n_licencia) {
    global $connection;

    // Prepara la llamada al procedimiento almacenado
    $sql = "CALL DameDoctor(?)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        return [];
    }

    // "i" significa entero (integer)
    $stmt->bind_param("i", $n_licencia);
    
    $doctores = [];
    if ($stmt->execute()) {
        // Obtenemos el resultado de la consulta del procedure
        $result = $stmt->get_result();
        if ($result) {
            $doctores = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
        }
    }

    $stmt->close();

    // Limpia resultados pendientes en MySQL para evitar bloqueos por llamadas a procedures
    while ($connection->more_results() && $connection->next_result()) {;}

    return $doctores;
}

// ===== FUNCION PA_AltaDita =====
// Alta de una cita médica: paciente, especialidad, fecha y doctor.
// En esta versión antigua hay 4 parámetros en el CALL pero solo se vinculan 3;
// la versión buena está en portal_citasV2/AccesoDatos.php.
function PA_AltaCita(string $nombre_paciente, string $especialidad, string $fecha, string $doctor_asignado ){

		global $connection;

		// Llamada al procedure de alta de cita con parámetro OUT (@resultado).
		$sql = "CALL AltaCita(?, ?, ?, ?, @resultado)";
		$stmt = $connection->prepare($sql);

		// Aquí faltaría vincular el cuarto parámetro; se deja igual para no
		// cambiar el comportamiento de esta versión antigua.
		$stmt->bind_param("sss", $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

		if ($stmt->execute()) {
			$error = $stmt->error;
			$stmt->close();
			$two = "Error al ejecutar el procedimiento almacenado: " . $error;
		}
		$stmt->close();

		// Lee el código OUT que devuelve el procedure.
		$resultado = $connection->query("SELECT @resultado AS resultado");
}
