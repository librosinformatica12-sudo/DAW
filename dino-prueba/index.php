<?php
session_start();
define('APP', true);

// Si ya hay sesión iniciada, redirige al inicio
if (isset($_SESSION['usuario_id'])) { 
    header('Location: inicio.php'); 
    exit; 
}

require_once __DIR__ . '/src/AccesoDatos.php';

function e(mixed $v): string {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

$accion = '';
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
    <title>DinoCards - Portal del Coleccionista</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@600;800&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/style.css">
    
    <script src="script/script.js"></script>
</head>
<body>
    <header class="topbar">
        <div class="logo">
            <i class="bx bx-id-card"></i>
            <div class="logo-text">
                <strong>DinoCards<span class="version-code"> V1.0</span></strong>
                <span>CLUB DE COLECCIONISTAS</span>
            </div>
        </div>
        <div class="topbar-der">
            <span>Portal del Coleccionista</span>
            <i class="bx bx-collection"></i>
        </div>
    </header>

    <main class="pagina">
        <section class="hero">
            <h2>Bienvenido al <span>Portal del Coleccionista</span></h2>
            <p>Reclama una carta nueva cada día, consulta tu colección y descubre las estadísticas de cada dinosaurio... todo desde un solo lugar.</p>
            <ul>
                <li><i class="bx bx-gift"></i><div><b>Carta diaria</b><small>Abre tu sobre y consigue un dinosaurio nuevo.</small></div></li>
                <li><i class="bx bx-collection"></i><div><b>Mi colección</b><small>Consulta las cartas que ya tienes.</small></div></li>
                <li><i class="bx bx-book-open"></i><div><b>DinoPedia</b><small>Explora el catálogo completo de especies.</small></div></li>
            </ul>
        </section>

        <div class="container" data-panel="<?= e($panelActivo) ?>">

            <button class="btn" type="button" role="tab" data-ir="login">Iniciar sesión</button>
            <button class="btn" type="button" role="tab" data-ir="registro">Registrarse</button>


            <!-- LOGIN -->
            <form id="form-login" method="POST" action="">
                <h2>Iniciar sesión</h2>
                <p class="sub">Rellena con tu usuario y contraseña para acceder a DinoCards</p>

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

                <p class="register-link">
                    <a href="#" data-ir="registro">
                        <i class="bx bx-user-plus"></i> Crear nueva cuenta
                    </a>
                </p>
                
                <p class="nota" style="text-align: center;">Portal exclusivo para miembros y coleccionistas de DinoCards</p>
            </form>

            <!-- REGISTRO -->
            <form id="form-registro" method="POST" action="php/registro.php">
                <h2>Crear cuenta</h2>
                <p class="sub">Rellena tus datos para registrarte</p>

                <input type="hidden" name="accion" value="registro">

                <div class="campo">
                    <label for="reg-nombre">Nombre</label>
                    <input type="text" id="reg-nombre" name="nombre" maxlength="50" placeholder="Tu nombre" required>
                </div>

                <div class="campo">
                    <label for="reg-apellido">Apellido</label>
                    <input type="text" id="reg-apellido" name="apellido" maxlength="50" placeholder="Tu apellido" required>
                </div>

                <div class="campo">
                    <label for="reg-usuario">Usuario</label>
                    <input type="text" id="reg-usuario" name="usuario" maxlength="16" autocomplete="username" placeholder="3 a 16 caracteres" value="<?= e($usuario) ?>" required>
                </div>
                
                <div class="campo">
                    <label for="reg-email">Correo electrónico</label>
                    <input type="email" id="reg-email" name="email" maxlength="100" autocomplete="email" placeholder="tu@correo.com" value="<?= e($email) ?>" required>
                </div>
                
                <div class="campo">
                    <label for="reg-pass">Contraseña</label>
                    <div class="entrada">
                        <input type="password" id="reg-pass" name="contrasena" autocomplete="new-password" placeholder="Mínimo 8 caracteres" required>
                        <button type="button" class="rojo" data-para="reg-pass">Mostrar</button>
                    </div>
                </div>
                
                <div class="campo">
                    <label for="reg-pass2">Repite la contraseña</label>
                    <div class="entrada">
                        <input type="password" id="reg-pass2" name="confirmaContrasena" autocomplete="new-password" placeholder="Repite la contraseña" required>
                        <button type="button" class="rojo" data-para="reg-pass2">Mostrar</button>
                    </div>
                </div>

                <button type="submit" class="enviar">Crear cuenta</button>
                <div class="aviso <?= $errorRegistro !== null ? 'error' : 'ok' ?>" id="aviso-registro" role="status"><?= e($errorRegistro ?? $exitoRegistro ?? '') ?></div>

                <p class="register-link">
                    ¿Ya tienes cuenta?
                    <a href="#" data-ir="login">
                        <i class="bx bx-user"></i> Iniciar sesión
                    </a>
                </p>
            </form>
        </div>
    </main>
</body>
</html>