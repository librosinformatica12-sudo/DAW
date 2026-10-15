<?php
/* ============================================================
   index.php
   ------------------------------------------------------------------
   Portal de entrada (diseño tipo dino-prueba).
   El login y el registro se resuelven en esta misma pagina.
   ============================================================ */

require_once "conexion.php";

// Si ya esta dentro, lo mandamos al panel
if (esta_logueado()) {
    header("Location: inicio.php");
    exit;
}

$aviso    = "";
$correcto = true;    // true = aviso verde, false = aviso rojo
$pestana  = "login"; // pestana que se muestra al cargar

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    // ---------------- REGISTRO ----------------
    if ($accion === "registro") {
        $pestana = "registro";
        $usuario = trim($_POST["usuario"] ?? "");
        $email   = trim($_POST["email"] ?? "");
        $clave1  = $_POST["contrasena"] ?? "";
        $clave2  = $_POST["confirma"] ?? "";

        if ($usuario === "" || $email === "" || $clave1 === "") {
            $aviso = "Rellena todos los campos.";
            $correcto = false;
        } elseif (strlen($usuario) < 3) {
            $aviso = "El usuario debe tener al menos 3 caracteres.";
            $correcto = false;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $aviso = "El correo no es válido.";
            $correcto = false;
        } elseif (strlen($clave1) < 4) {
            $aviso = "La contraseña debe tener al menos 4 caracteres.";
            $correcto = false;
        } elseif ($clave1 !== $clave2) {
            $aviso = "Las contraseñas no coinciden.";
            $correcto = false;
        } else {
            // Miramos si ese usuario o email ya existen
            $consulta = $conexion->prepare("SELECT id FROM usuarios WHERE username = ? OR email = ?");
            $consulta->bind_param("ss", $usuario, $email);
            $consulta->execute();
            $consulta->store_result();

            if ($consulta->num_rows > 0) {
                $aviso = "Ese usuario o correo ya está registrado.";
                $correcto = false;
            } else {
                // Guardamos la contraseña cifrada con md5
                $hash = md5($clave1);
                $alta = $conexion->prepare(
                    "INSERT INTO usuarios (username, email, password_hash, fecha_registro)
                     VALUES (?, ?, ?, NOW())"
                );
                $alta->bind_param("sss", $usuario, $email, $hash);

                if ($alta->execute()) {
                    $aviso = "Registro completado. ¡Ya puedes iniciar sesión!";
                    $pestana = "login";
                } else {
                    $aviso = "No se pudo completar el registro.";
                    $correcto = false;
                }
                $alta->close();
            }
            $consulta->close();
        }
    }

    // ---------------- LOGIN ----------------
    if ($accion === "login") {
        $pestana = "login";
        $usuario = trim($_POST["usuario"] ?? "");
        $clave   = $_POST["contrasena"] ?? "";

        if ($usuario === "" || $clave === "") {
            $aviso = "Escribe tu usuario o correo y tu contraseña.";
            $correcto = false;
        } else {
            $hash = md5($clave);
            $consulta = $conexion->prepare(
                "SELECT id, username FROM usuarios
                 WHERE (email = ? OR username = ?) AND password_hash = ?
                 LIMIT 1"
            );
            $consulta->bind_param("sss", $usuario, $usuario, $hash);
            $consulta->execute();
            $fila = $consulta->get_result()->fetch_assoc();
            $consulta->close();

            if ($fila) {
                // Guardamos el usuario en la sesion
                $_SESSION["usuario_id"] = (int) $fila["id"];
                $_SESSION["usuario"]    = $fila["username"];
                header("Location: inicio.php");
                exit;
            } else {
                $aviso = "Usuario o contraseña incorrectos.";
                $correcto = false;
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
    <title>DinoCards - Portal del Coleccionista</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=3">
    <script src="script.js" defer></script>
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
        <h2>Bienvenido al <span>Portal del Coleccionista</span></h2>
        <p>Reclama una carta nueva cada día, consulta tu colección y descubre las estadísticas de cada dinosaurio... todo desde un solo lugar.</p>
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

    <div class="container" id="contenedor-pestanas" data-inicial="<?= escapar($pestana) ?>">

        <button type="button" id="btn-login" class="btn<?= $pestana === "login" ? " activa" : "" ?>" onclick="mostrarPestana('login')">Iniciar sesión</button>
        <button type="button" id="btn-registro" class="btn<?= $pestana === "registro" ? " activa" : "" ?>" onclick="mostrarPestana('registro')">Registrarse</button>

        <?php if ($aviso !== ""): ?>
            <p class="aviso <?= $correcto ? "ok" : "error" ?>"><?= escapar($aviso) ?></p>
        <?php endif; ?>

        <!-- LOGIN -->
        <form id="form-login" method="POST" action="index.php" class="<?= $pestana === "registro" ? "oculto" : "" ?>">
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

            <div class="separador"><span>o</span></div>

            <p class="register-link">
                <a href="#" onclick="mostrarPestana('registro'); return false;">
                    <i class="bx bx-user-plus"></i> Crear nueva cuenta
                </a>
            </p>

            <p class="nota">Portal exclusivo para miembros y coleccionistas de DinoCards</p>
        </form>

        <!-- REGISTRO -->
        <form id="form-registro" method="POST" action="index.php" class="<?= $pestana === "login" ? "oculto" : "" ?>">
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
