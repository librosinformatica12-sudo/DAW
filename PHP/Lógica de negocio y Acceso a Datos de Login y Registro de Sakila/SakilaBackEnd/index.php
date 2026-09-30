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
	$tienda = filter_var($_POST['tienda'] ?? null, FILTER_VALIDATE_INT);
	$usuario = trim($_POST['usuario'] ?? '');
	$contrasena = $_POST['contrasena'] ?? '';

	if ($tienda === false || $tienda === null) {
		$error = 'El número de tienda no es válido.';
	} else {
		try {
			$resultado = PA_Registrar(
				$nombre,
				$apellido,
				$email,
				$tienda,
				$usuario,
				$contrasena
			);
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
			<p class="error">Error: <?= htmlspecialchars($error) ?></p>
		<?php elseif ($resultado !== null && $resultado > 0): ?>
			<p>Registro realizado correctamente. ID de empleado: <?= (int)$resultado ?></p>
		<?php elseif ($resultado !== null): ?>
			<p class="error">No se pudo registrar (código <?= (int)$resultado ?>).</p>
		<?php endif; ?>

		<form method="POST" action="">
			<label for="nombre">Nombre:</label>
			<input type="text" id="nombre" name="nombre" value="<?= $nombre ?>" required>

			<label for="apellido">Apellido:</label>
			<input type="text" id="apellido" name="apellido" value="<?= $apellido ?>" required>

			<label for="email">Email:</label>
			<input type="email" id="email" name="email" value="<?= $email ?>" required>

			<label for="tienda">Tienda:</label>
			<input type="number" id="tienda" name="tienda" value="<?= $tienda ?>" required>

			<label for="usuario">Usuario:</label>
			<input type="text" id="usuario" name="usuario" value="<?= $usuario ?>" required>

			<label for="contrasena">Contraseña:</label>
			<input type="password" id="contrasena" name="contrasena" required>
			<label for="confirmaContrasena">Confirmar Contraseña:</label>
			<input type="password" id="confirmaContrasena" name="confirmaContrasena" required>

			<button type="submit" onclick="return validarFormulario();">Registrar</button>
		</form>	



</body>
</html>