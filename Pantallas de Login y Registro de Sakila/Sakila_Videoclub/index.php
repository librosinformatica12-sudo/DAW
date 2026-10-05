<?php
session_start();
define('APP', true);
require_once __DIR__ . '/src/AccesoDatos.php';

function e($v): string { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }

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
    <title>Pagina web Pantallas de Login y Registro de Sakila</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <i class='bx bx-film'></i>
            <div>
                <strong>Sakila</strong>
                <span>VIDEO CLUB</span>
            </div>
        </div>
        <div class="topbar-der">Portal del Empleado <i class="bs bs-film"></i></div>
    </header>

    <main class="pagina">
        <section class="hero">
            <h2>Bienvenido al  <span> Portal del empleado</span></h2>
            <P> Gestiona el alquiler de películas, consulta el catálogo, administra clientes y mucho más... todo desde un solo lugar.</P>
            <ul>
                <li><i class="bx bx-movie-play"></i><div><b> Catalogo de películas </b><small>Consulta y gestiona el inventario.</small></div></li>
                <li><i class="bx bx-group"></i><div><b> Clientes</b><small> Controla el estado de los alquileres.</small></div></li>
                <li><i class="bx bx-bar-chart-alt-2"></i><div><b> Informes</b><small> Visualiza la actividad de la tienda.</small></div></li>
            </ul>
        </section>
        <div class="container" data-panel="<?=  e ($panelActivo) ?>">
            



    </main>






















    <center>
    <div class="container" data-panel="<?= e($panelActivo) ?>">
        <h1>Iniciar Sesión</h1>

        <button class="btn" type="button">Iniciar sesion</button>
        <button class="btn" type="button">Registrarse</button>

        <!-- LOGIN -->
        <form id="form-login" method="POST" action="">
            <input type="hidden" name="accion" value="login">

            <div class="campo">
                <label for="log-usuario">Usuario o Correo</label>
                <input type="text" id="log-usuario" name="usuario" placeholder="Usuario o Correo" maxlength="50" autocomplete="username" required>
            </div>

            <div class="campo">
                <label for="log-pass">Contraseña</label>
                <div class="entrada">
                    <input type="password" id="log-pass" name="contrasena" placeholder="Contraseña" autocomplete="current-password" required>
                    <button type="button" class="rojo" data-para="log-pass">Mostrar</button>
                </div>
            </div>

            <div class="remember-password">
                <label><input type="checkbox" id="log-recordar"> Recordarme</label>
                <a href="#" id="olvide">¿Olvidaste tu contraseña?</a>
            </div>

            <button type="submit" class="enviar">Entrar</button>
            <div class="aviso <?= $errorLogin !== null ? 'error' : 'ok' ?>" id="aviso-login" role="status"><?= e($errorLogin ?? $exitoLogin ?? '') ?></div>

            <p class="register-link">¿Todavia no tienes una cuenta? <a href="#">Regístrate</a></p>
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

            <p class="register-link">¿Quieres iniciar Sesión? <a href="#">Iniciar sesion</a></p>
        </form>
    </div>
    </center>
    <script src="script/script.js"></script>
</body>
</html>