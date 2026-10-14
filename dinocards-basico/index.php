<?php
/* ============================================================
   index.php
   ------------------------------------------------------------------
   Pagina de entrada. Sirve para iniciar sesion o registrarse.
   Los formularios se envian a esta misma pagina.
   ============================================================ */

require_once "conexion.php";

// Si ya esta dentro, lo mandamos al panel
if (esta_logueado()) {
    header("Location: inicio.php");
    exit;
}

$aviso   = "";
$correcto = true;   // true = aviso verde, false = aviso rojo
$pestana = "login"; // pestana que se muestra al cargar

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
    <title>DinoCards - Entrar</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>

<header class="topbar">
    <span class="titulo">DinoCards</span>
    <span>Club de coleccionistas</span>
</header>

<main>
    <div class="tarjeta">
        <h1>Bienvenido al Portal del Coleccionista</h1>
        <p>Reclama una carta nueva cada dia, consulta tu coleccion y descubre las estadisticas de cada dinosaurio.</p>
    </div>

    <div class="tarjeta" id="contenedor-pestanas" data-inicial="<?= escapar($pestana) ?>">
        <div class="pestanas">
            <button type="button" id="btn-login" onclick="mostrarPestana('login')">Iniciar sesión</button>
            <button type="button" id="btn-registro" onclick="mostrarPestana('registro')">Registrarse</button>
        </div>

        <?php if ($aviso !== ""): ?>
            <p class="aviso <?= $correcto ? "ok" : "error" ?>"><?= escapar($aviso) ?></p>
        <?php endif; ?>

        <!-- Formulario de LOGIN -->
        <form id="form-login" method="POST" action="index.php">
            <input type="hidden" name="accion" value="login">
            <h2>Iniciar sesión</h2>

            <div class="campo">
                <label for="log-usuario">Usuario o correo</label>
                <input type="text" id="log-usuario" name="usuario" required>
            </div>

            <div class="campo">
                <label for="log-pass">Contraseña</label>
                <input type="password" id="log-pass" name="contrasena" required>
                <button type="button" class="boton secundario" onclick="alternarClave('log-pass', this)">Mostrar</button>
            </div>

            <button type="submit" class="boton">Entrar</button>
        </form>

        <!-- Formulario de REGISTRO -->
        <form id="form-registro" method="POST" action="index.php">
            <input type="hidden" name="accion" value="registro">
            <h2>Crear cuenta</h2>

            <div class="campo">
                <label for="reg-usuario">Usuario</label>
                <input type="text" id="reg-usuario" name="usuario" maxlength="16" required>
            </div>

            <div class="campo">
                <label for="reg-email">Correo electrónico</label>
                <input type="email" id="reg-email" name="email" required>
            </div>

            <div class="campo">
                <label for="reg-pass">Contraseña</label>
                <input type="password" id="reg-pass" name="contrasena" required>
                <button type="button" class="boton secundario" onclick="alternarClave('reg-pass', this)">Mostrar</button>
            </div>

            <div class="campo">
                <label for="reg-pass2">Repite la contraseña</label>
                <input type="password" id="reg-pass2" name="confirma" required>
            </div>

            <button type="submit" class="boton">Crear cuenta</button>
        </form>
    </div>
</main>

</body>
</html>
