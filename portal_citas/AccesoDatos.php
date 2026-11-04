<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
$username = 'root';
$password = '1234';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

function PA_AltaDoctor(string $n_licencia, string $n_nombre, string $n_apellido){
	global $connection;

	$sql = "CALL AltaCita(?, ?, ?)";
	$stmt = $connection->prepare($sql);

	$stmt->bind_param("sss", $n_licencia, $n_nombre, $n_apellido);

	if ($stmt->execute()) {
		$error = $stmt->error;
		$stmt->close();
		$two = "Error al ejecutar el procedimiento almacenado: " . $error;
	}
	$stmt->close();

	$resultado = $connection->query("SELECT @resultado AS resultado");

}

function PA_ListaDoctores(){
	global $connection;

	$sql = "CALL ListaDoctores()";
	$stmt = $connection->prepare($sql);
	$stmt->execute();

	$result = $stmt->get_result();
	$doctores = $result->fetch_all(MYSQLI_ASSOC);

	$stmt->close();
	return $doctores;


}

function PA_DameDoctor(int $n_licencia){
	global $connection;

	$sql = "CALL DameDoctor(?)";
	$stmt = $connection->prepare($sql);

	$stmt->bind_param("i", $n_licencia);

		if ($stmt->execute()) {
		$error = $stmt->error;
		$stmt->close();
		$two = "Error al ejecutar el procedimiento almacenado: " . $error;
	}

	$doctores = [];
	if($stmt)
}

	function PA_AltaDita(string $nombre_paciente, 
						string $especialidad, 
						string $fecha, 
						string $doctor_asignado ){

		global $connection;

		$sql = "CALL AltaCita(?, ?, ?, ?, @resultado)";
		$stmt = $connection->prepare($sql);

		$stmt->bind_param("sss", $nombre_paciente, $especialidad, $fecha, $doctor_asignado);

		if ($stmt->execute()) {
			$error = $stmt->error;
			$stmt->close();
			$two = "Error al ejecutar el procedimiento almacenado: " . $error;
		}
		$stmt->close();

		$resultado = $connection->query("SELECT @resultado AS resultado");
	}