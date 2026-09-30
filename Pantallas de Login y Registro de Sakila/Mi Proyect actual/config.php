<?php

require_once __DIR__ . '/AccesoDatos.php';

$nombre = '';
$apellido = '';
$email = '';
$tienda = '';
$usuario = '';
$resultado = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$nombre = trim($_POST['nombre'] ?? '');
	$apellido = trim($_POST['apellido'] ?? '');
	$email = trim($_POST['email'] ?? '');
	$tiendaValidada = filter_var($_POST['tienda'] ?? null, FILTER_VALIDATE_INT);
	$usuario = trim($_POST['usuario'] ?? '');
	$contrasena = $_POST['contrasena'] ?? '';
	$confirmaContrasena = $_POST['confirmaContrasena'] ?? '';

	$tienda = ($tiendaValidada === false || $tiendaValidada === null) ? '' : $tiendaValidada;

	if ($tiendaValidada === false || $tiendaValidada === null || $tiendaValidada < 1 || $tiendaValidada > 255) {
		$error = 'La tienda debe ser un número entre 1 y 255.';
	} elseif ($contrasena !== $confirmaContrasena) {
		$error = 'Las contraseñas no coinciden.';
	} else {
		try {
			$codigo = (int) PA_Registrar(
				$nombre,
				$apellido,
				$email,
				$tiendaValidada,
				$usuario,
				$contrasena
			);

			if ($codigo > 0) {
				$resultado = $codigo; // staff_id del nuevo empleado
			} else {
				$mensajes = [
					-1  => 'El usuario no puede estar vacío.',
					-2  => 'El usuario ya existe. Elige otro nombre de usuario.',
					-3  => 'La contraseña no puede estar vacía.',
					-5  => 'Nombre, apellido y email son obligatorios.',
					-6  => 'La tienda debe ser un número entre 1 y 255.',
					-99 => 'Error inesperado en la base de datos.',
				];
				$error = $mensajes[$codigo] ?? "No se pudo completar el registro (código $codigo).";
			}
		} catch (Throwable $exception) {
			$error = $exception->getMessage();
		}
	}
}