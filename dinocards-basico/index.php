<?php
/* ============================================================
   index.php
   ------------------------------------------------------------------
   Portal de entrada (diseño tipo dino-prueba).
   El login y el registro se resuelven en php/login.php y
   php/registro.php (endpoints JSON llamados con fetch).
   ============================================================ */

require_once __DIR__ . "/src/AccesoDatos.php";

// Si ya esta dentro, lo mandamos al panel
if (esta_logueado()) {
    header("Location: inicio.php");
    exit;
}

<<<<<<< HEAD
$pestana = "login"; // pestana que se muestra al cargar
=======
$aviso    = "";
$correcto = true;    // true = aviso verde, false = aviso rojo
$pestana  = "login"; // pestana que se muestra al cargar
>>>>>>> b7032b5d8e0c3c0bd80c1f6144c9ef5dd5aee06c

// La logica de login y registro vive en php/login.php y php/registro.php
// (endpoints JSON llamados con fetch, igual que en dino-prueba).
// Este dispatch solo actua como respaldo si el formulario se envia sin JavaScript.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    if ($accion === "registro") {
        require __DIR__ . "/php/registro.php";
    } elseif ($accion === "login") {
        require __DIR__ . "/php/login.php";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DinoCards - Portal del Coleccionista</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles/comun.css?v=<?= @filemtime(__DIR__ . '/styles/comun.css') ?>">
    <link rel="stylesheet" href="styles/paginas.css?v=<?= @filemtime(__DIR__ . '/styles/paginas.css') ?>">
    <script src="script/script.js?v=<?= @filemtime(__DIR__ . '/script/script.js') ?>" defer></script>
</head>
<body>

<header class="topbar">
    <a class="logo" href="index.php">
        <span class="logo-mark" aria-hidden="true"><i class="bx bx-id-card"></i></span>
        <span class="logo-texto">
            <strong>DinoCards</strong>
            <small>Club de coleccionistas</small>
        </span>
    </a>
    <div class="topbar-der">
        <span>Portal del Coleccionista</span>
        <i class="bx bx-collection"></i>
    </div>
</header>

<main class="pagina">

    <section class="hero">
        <h2 id="hero-title">Bienvenido al <span>Portal del Coleccionista</span></h2>
        <p id="hero-description">Reclama una carta nueva cada día, consulta tu colección y descubre las estadísticas de cada dinosaurio... todo desde un solo lugar.</p>
        <ul>
            <li>
                <i class="bx bx-gift"></i>
                <div><b>Carta diaria</b><small>Abre tu sobre y consigue un dinosaurio nuevo.</small></div>
            </li>
            <li>
                <i class="bx bx-collection"></i>
                <div><b>Mi colección</b><small>Consulta las cartas que ya tienes.</small></div>
            </li>
            <li>
                <i class="bx bx-book-open"></i>
                <div><b>DinoPedia</b><small>Explora el catálogo completo de especies.</small></div>
            </li>
        </ul>
    </section>
<<<<<<< HEAD
=======

    <div class="container" id="contenedor-pestanas" data-inicial="<?= escapar($pestana) ?>">

        <button class="btn<?= $pestana === "login" ? " activa" : "" ?>" type="button" role="tab" data-ir="login">Iniciar sesión</button>
        <button class="btn<?= $pestana === "registro" ? " activa" : "" ?>" type="button" role="tab" data-ir="registro">Registrarse</button>

        <!-- LOGIN -->
        <form id="form-login" method="POST" action="php/login.php" class="<?= $pestana === "registro" ? "oculto" : "" ?>">
            <h2>Iniciar sesión</h2>
            <p class="sub">Rellena con tu usuario y contraseña para acceder a DinoCards</p>

            <input type="hidden" name="accion" value="login">

            <div class="campo icono">
                <i class="bx bx-user"></i>
                <input type="text" name="usuario" placeholder="Usuario o correo" maxlength="50" id="log-usuario" autocomplete="username" required>
            </div>

            <div class="campo icono">
                <i class="bx bx-lock-alt"></i>
                <div class="entrada">
                    <input type="password" id="log-pass" name="contrasena" placeholder="Contraseña" autocomplete="current-password" required>
                    <button type="button" class="rojo" onclick="alternarClave('log-pass', this)">Mostrar</button>
                </div>
            </div>

            <button type="submit" class="enviar">Iniciar sesión</button>

            <div class="aviso ok" id="aviso-login" role="status" style="display: none;"></div>

            <div class="separador"><span>o</span></div>

            <p class="register-link">
                <a href="#" onclick="mostrarPestana('registro'); return false;">
                    <i class="bx bx-user-plus"></i> Crear nueva cuenta
                </a>
            </p>

            <p class="nota">Portal exclusivo para miembros y coleccionistas de DinoCards</p>
        </form>

        <!-- REGISTRO -->
        <form id="form-registro" method="POST" action="php/registro.php" class="<?= $pestana === "login" ? "oculto" : "" ?>">
            <h2>Crear cuenta</h2>
            <p class="sub">Rellena tus datos para registrarte</p>

            <input type="hidden" name="accion" value="registro">

            <div class="campo">
                <label for="reg-usuario">Usuario</label>
                <input type="text" id="reg-usuario" name="usuario" maxlength="16" autocomplete="username" placeholder="3 a 16 caracteres" required>
            </div>

            <div class="campo">
                <label for="reg-email">Correo electrónico</label>
                <input type="email" id="reg-email" name="email" maxlength="100" autocomplete="email" placeholder="tu@correo.com" required>
            </div>

            <div class="campo">
                <label for="reg-pass">Contraseña</label>
                <div class="entrada">
                    <input type="password" id="reg-pass" name="contrasena" autocomplete="new-password" placeholder="Mínimo 4 caracteres" required>
                    <button type="button" class="rojo" onclick="alternarClave('reg-pass', this)">Mostrar</button>
                </div>
            </div>

            <div class="campo">
                <label for="reg-pass2">Repite la contraseña</label>
                <div class="entrada">
                    <input type="password" id="reg-pass2" name="confirma" autocomplete="new-password" placeholder="Repite la contraseña" required>
                    <button type="button" class="rojo" onclick="alternarClave('reg-pass2', this)">Mostrar</button>
                </div>
            </div>

            <button type="submit" class="enviar">Crear cuenta</button>

            <div class="aviso ok" id="aviso-registro" role="status" style="display: none;"></div>

            <p class="register-link">
                ¿Ya tienes cuenta?
                <a href="#" onclick="mostrarPestana('login'); return false;">
                    <i class="bx bx-user"></i> Iniciar sesión
                </a>
            </p>
        </form>
    </div>
</main>

</body>
</html>