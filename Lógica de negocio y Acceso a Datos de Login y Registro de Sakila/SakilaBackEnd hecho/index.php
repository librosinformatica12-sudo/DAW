<?php

session_start();
require_once __DIR__ . '/AccesoDatos.php';

$nombre = '';
$apellido = '';
$email = '';
$tienda = '';
$usuario = '';
$resultado = null;
$error = null;
$errorLogin = null;
$exitoLogin = null;



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

	// registro
    if ($accion === 'registro') {

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tiendaValidada = filter_var($_POST['tienda'] ?? null, FILTER_VALIDATE_INT);
        $usuario = trim($_POST['usuario'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmaContrasena = $_POST['confirmaContrasena'] ?? '';

        if ($tiendaValidada === false || $tiendaValidada === null || $tiendaValidada < 1 || $tiendaValidada > 255) {
            $error = 'La tienda debe ser un número entre 1 y 255.';
        } elseif ($contrasena !== $confirmaContrasena) {
            $error = 'Las contraseñas no coinciden.';
        } else {
            try {
                $codigo = (int) PA_Registrar($nombre, $apellido, $email, $tiendaValidada, $usuario, $contrasena);

                if ($codigo > 0) {
                    $resultado = $codigo;
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
		


	// login	
	} elseif ($accion === 'login') {

		$loginUsuario = trim($_POST['usuario'] ?? '');
		$contrasena = $_POST['contrasena'] ?? '';

		if ($loginUsuario === '' || $contrasena === '') {
			$errorLogin = 'Usuario y contraseña son obligatorios.';
		} else {
			try {
				$r = PA_Login($loginUsuario, $contrasena);
				$codigo = $r['codigo'];

				if ($codigo > 0) {
					session_regenerate_id(true);
					$_SESSION['staff'] = $r['staff'];
					$_SESSION['staff_id'] = $codigo;
					$_SESSION['login'] = $loginUsuario;
					$exitoLogin = 'Sesión iniciada correctamente. ¡Bienvenido, ' . $loginUsuario . '!';
				} elseif ($codigo === -1) {
					$errorLogin = 'Introduce usuario y contraseña.';
				} elseif ($codigo === -2) {
					$errorLogin = 'Usuario o contraseña incorrectos.';
				} elseif ($codigo === -3) {
					$errorLogin = 'Cuenta bloqueada por demasiados intentos fallidos. Inténtalo de nuevo en 5 minutos.';				} else {
					$errorLogin = 'Error inesperado. Inténtalo más tarde.';
				}
			} catch (Throwable $e) {
				error_log($e->getMessage());
				$errorLogin = 'Error inesperado. Inténtalo más tarde.';
			}
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
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<script src="script.js"></script>
	<div class="container">
		<h1>Catálogo Sakila</h1>
		<h2>Registro de Usuario</h2>
		<?php if ($error !== null): ?>
			<p class="error">Error: <?= $error ?></p>
		<?php elseif ($resultado !== null): ?>
			<p>Registro realizado correctamente.</p>
			<p>ID de empleado: <?= (int) $resultado ?></p>
		<?php endif; ?>

		<!-- Crear usuario -->
		<form method="POST" action="">
			<label for="nombre">Nombre:</label>
			<input type="text" id="nombre" name="nombre" value="<?= $nombre ?>" required>

			<label for="apellido">Apellido:</label>
			<input type="text" id="apellido" name="apellido" value="<?= $apellido ?>" required>

			<label for="email">Email:</label>
			<input type="email" id="email" name="email" value="<?= $email ?>" required>

			<label for="tienda">Tienda:</label>
			<input type="number" id="tienda" name="tienda" min="1" max="255" value="<?= $tienda ?>" required>
			<label for="usuario">Usuario:</label>
			<input type="text" id="usuario" name="usuario" value="<?= $usuario ?>" required>

			<label for="contrasena">Contraseña:</label>
			<input type="password" id="contrasena" name="contrasena" required>
			<label for="confirmaContrasena">Confirmar Contraseña:</label>
			<input type="password" id="confirmaContrasena" name="confirmaContrasena" required>

			<button type="submit" name="accion" value="registro" onclick="return validarFormulario();"> Registrarse </button>
		</form>	

		<br>
		<br>
		<br>

		<!-- iniciar sesion -->
		 <h2>Iniciar Sesion</h2>
			<?php if ($errorLogin !== null): ?>
				<p class="error"><?= htmlspecialchars($errorLogin) ?></p>
			<?php elseif ($exitoLogin !== null): ?>
				<p class="success"><?= htmlspecialchars($exitoLogin) ?></p>
			<?php endif; ?>

		<form method="POST" action="">
			
			<label for="loginUsuario">Usuario o correo:</label>
			<input type="text" id="loginUsuario" name="usuario" required>

			<label for="loginContrasena">Contraseña:</label>
			<input type="password" id="loginContrasena" name="contrasena" required>

			<button type="submit" name="accion" value="login">Iniciar Sesion</button>
		</form>
	</div>		

</body>
</html>