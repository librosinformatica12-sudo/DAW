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

	// Mantener la tienda elegida en el formulario
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
				$resultado = $codigo; // nuevo staff_id
			} else {
				$mensajes = [
					-1  => 'El usuario no puede estar vacío.',
					-2  => 'El usuario ya existe. Elige otro nombre de usuario.',
					-3  => 'La contraseña no puede estar vacía.',
					-6  => 'La tienda debe ser un número entre 1 y 255.',
					-5  => 'Nombre, apellido y email son obligatorios.',
					-99 => 'Error inesperado en la base de datos.',
				];
				$error = $mensajes[$codigo] ?? "No se pudo completar el registro (código $codigo).";
			}
		} catch (Throwable $exception) {
			$error = $exception->getMessage();
		}
	}
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Catálogo Sakila | Film</title>
	<link rel="stylesheet" href="style/estilos.css">
	<script>
		function validarFormulario() {
			var contrasena = document.getElementById("contrasena").value;
			var confirmaContrasena = document.getElementById("confirmaContrasena").value;

			if (contrasena !== confirmaContrasena) {
				alert("Las contraseñas no coinciden. Por favor, inténtalo de nuevo.");
				return false; // Evita que el formulario se envíe
			}
			return true; // Permite que el formulario se envíe
		}
	</script>
</head>
<body>
	<div class="container">
		<h1>Catálogo Sakila</h1>
		<h2>Registro de Usuario</h2>

		<?php if ($error !== null): ?>
			<p class="error">Error: <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
		<?php elseif ($resultado !== null): ?>
			<p>Registro realizado correctamente.</p>
			<p>ID de empleado: <?= (int) $resultado ?></p>
		<?php endif; ?>

		<form method="POST" action="" onsubmit="return validarFormulario();">
			<label for="nombre">Nombre:</label>
			<input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?>" required>

			<label for="apellido">Apellido:</label>
			<input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($apellido, ENT_QUOTES, 'UTF-8') ?>" required>

			<label for="email">Email:</label>
			<input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" required>

			<label for="tienda">Tienda:</label>
			<input type="number" id="tienda" name="tienda" min="1" max="255" value="<?= htmlspecialchars((string) $tienda, ENT_QUOTES, 'UTF-8') ?>" required>

			<label for="usuario">Usuario:</label>
			<input type="text" id="usuario" name="usuario" value="<?= htmlspecialchars($usuario, ENT_QUOTES, 'UTF-8') ?>" required>

			<label for="contrasena">Contraseña:</label>
			<input type="password" id="contrasena" name="contrasena" required>

			<label for="confirmaContrasena">Confirmar Contraseña:</label>
			<input type="password" id="confirmaContrasena" name="confirmaContrasena" required>

			<button type="submit">Registrar</button>
		</form>
	</div>
</body>
</html>