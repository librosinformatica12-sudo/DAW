<?php
session_start();
define('APP', true);
require_once __DIR__ . '/AccesoDatos.php';

function e(mixed $v): string { 
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); 
}

$accion = '';
$nombre = '';
$apellido = '';
$email = '';
$usuario = '';
$errorRegistro = null;
$exitoRegistro = null;
$errorLogin = null;
$exitoLogin = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'registro') {
        require __DIR__ . '/php/registro.php';
    } elseif ($accion === 'login') {
        require __DIR__ . '/php/login.php';
    }
}

// Qué pestaña se muestra al cargar
$panelActivo = ($accion === 'registro') ? 'registro' : 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sakila Video Club - Portal del Empleado</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <i class="bx bx-film"></i>
            <div class="logo-text">
                <strong>DinoCards<span class="version-code">v1.0</span></strong>
                <span>DINO CARDS</span>
            </div>
        </div>
        <div class="topbar-der">
            <span>Portal del Empleado</span>
            <i class="bx bx-film"></i>
        </div>
    </header>

    <main class="pagina">
        <section class="hero">
            <h2>Bienvenido al <span>Portal del Empleado</span></h2>
            <p>Gestiona el alquiler de películas, consulta el catálogo, administra clientes y mucho más... todo desde un solo lugar.</p>
            <ul>
                <li><i class="bx bx-movie-play"></i><div><b>Catálogo de películas</b><small>Consulta y gestiona el inventario.</small></div></li>
                <li><i class="bx bx-group"></i><div><b>Clientes</b><small>Controla el estado de los alquileres.</small></div></li>
                <li><i class="bx bx-bar-chart-alt-2"></i><div><b>Informes</b><small>Visualiza la actividad de la tienda.</small></div></li>
            </ul>
        </section>

        <div class="container" data-panel="<?= e($panelActivo) ?>">
            <h1>Iniciar Sesión</h1>

            <button class="btn" type="button">Iniciar sesión</button>
            <button class="btn" type="button">Registrarse</button>

            <!-- LOGIN -->
            <form id="form-login" method="POST" action="">
                <input type="hidden" name="accion" value="login">

                <div class="campo icono">
                    <i class="bx bx-user"></i>
                    <input type="text" name="usuario" placeholder="Usuario o email" maxlength="50" id="log-usuario" autocomplete="username" required>
                </div>

                <div class="campo icono">
                    <i class="bx bx-lock-alt"></i>
                    <div class="entrada">
                        <input type="password" id="log-pass" name="contrasena" placeholder="Contraseña" autocomplete="current-password" required>
                        <button type="button" class="rojo" data-para="log-pass">Mostrar</button>
                    </div>
                </div>

                <div class="remember-password">
                    <label><input type="checkbox" id="log-recordar"> Recordarme</label>
                    <a href="#" id="olvide">¿Olvidaste tu contraseña?</a>
                </div>

                <button type="submit" class="enviar">Iniciar sesión</button>
                <div class="aviso <?= $errorLogin !== null ? 'error' : 'ok' ?>" id="aviso-login" role="status"><?= e($errorLogin ?? $exitoLogin ?? '') ?></div>

                <div class="separador"><span>o</span></div>
                <p class="register-link"><a href="#"><i class="bx bx-user-plus"></i> Crear nueva cuenta</a></p>
                <center><p class="nota">Solo para empleados autorizados de DinoCards</p></center>
            </form>

            <!-- REGISTRO -->
            <form id="form-registro" method="POST" action="" novalidate hidden>
                <input type="hidden" name="accion" value="registro">

                <h2>Crear cuenta</h2>
                <p class="sub">Rellena tus datos para registrarte</p>

                <div class="fila">
                    <div class="campo">
                        <label for="reg-nombre">Nombre</label>
                        <input type="text" id="reg-nombre" name="nombre" maxlength="45" autocomplete="given-name" placeholder="Tu nombre" value="<?= e($nombre) ?>">
                        <div class="msg"></div>
                    </div>
                    <div class="campo">
                        <label for="reg-apellido">Apellido</label>
                        <input type="text" id="reg-apellido" name="apellido" maxlength="45" autocomplete="family-name" placeholder="Tu apellido" value="<?= e($apellido) ?>">
                        <div class="msg"></div>
                    </div>
                </div>
                <div class="campo">
                    <label for="reg-email">Correo electrónico</label>
                    <input type="email" id="reg-email" name="email" maxlength="50" autocomplete="email" placeholder="tu@correo.com" value="<?= e($email) ?>">
                    <div class="msg"></div>
                </div>
                <div class="campo">
                    <label for="reg-usuario">Usuario</label>
                    <input type="text" id="reg-usuario" name="usuario" maxlength="16" autocomplete="username" placeholder="3 a 16 caracteres" value="<?= e($usuario) ?>">
                    <div class="msg"></div>
                </div>
                <div class="campo">
                    <label for="reg-pass">Contraseña</label>
                    <div class="entrada">
                        <input type="password" id="reg-pass" name="contrasena" autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                        <button type="button" class="rojo" data-para="reg-pass">Mostrar</button>
                    </div>
                    <div class="fuerza"><span id="barra"></span></div>
                    <div class="msg"></div>
                </div>
                <div class="campo">
                    <label for="reg-pass2">Repite la contraseña</label>
                    <input type="password" id="reg-pass2" name="confirmaContrasena" autocomplete="new-password" placeholder="Repite la contraseña">
                    <div class="msg"></div>
                </div>

                <button type="submit" class="enviar">Crear cuenta</button>
                <div class="aviso <?= $errorRegistro !== null ? 'error' : 'ok' ?>" id="aviso-registro" role="status"><?= e($errorRegistro ?? $exitoRegistro ?? '') ?></div>

                <p class="register-link">¿Ya tienes cuenta? <a href="#">Iniciar sesión</a></p>
            </form>
        </div>
    </main>

    <script src="script/script.js"></script>
</body>
</html>