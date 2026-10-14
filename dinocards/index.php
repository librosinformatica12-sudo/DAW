<?php
/**
 * index.php
 * Página de entrada: iniciar sesión o registrarse.
 */
require_once __DIR__ . '/includes/funciones.php';

// Si ya hay sesión, al panel directamente
if (esta_logueado()) {
    header('Location: inicio.php');
    exit;
}

$titulo = 'Entrar';
require __DIR__ . '/includes/cabecera.php';
?>

<section class="hero">
    <h1>Bienvenido al <span>Portal del Coleccionista</span></h1>
    <p>Reclama una carta nueva cada día, consulta tu colección y descubre las estadísticas de cada dinosaurio.</p>
</section>

<section class="panel panel-auth" id="auth" data-inicial="login">
    <div class="pestanas">
        <button type="button" class="pestana activa" data-panel="login">Iniciar sesión</button>
        <button type="button" class="pestana" data-panel="registro">Registrarse</button>
    </div>

    <p id="aviso" class="aviso" hidden></p>

    <!-- FORMULARIO DE LOGIN -->
    <form id="form-login" class="formulario" novalidate>
        <h2>Iniciar sesión</h2>

        <label for="log-usuario">Usuario o correo</label>
        <input type="text" id="log-usuario" name="usuario" autocomplete="username" required>

        <label for="log-pass">Contraseña</label>
        <div class="campo-clave">
            <input type="password" id="log-pass" name="contrasena" autocomplete="current-password" required>
            <button type="button" class="ver-clave" data-para="log-pass">Mostrar</button>
        </div>

        <button type="submit" class="boton">Entrar</button>
    </form>

    <!-- FORMULARIO DE REGISTRO -->
    <form id="form-registro" class="formulario" hidden novalidate>
        <h2>Crear cuenta</h2>

        <label for="reg-usuario">Usuario</label>
        <input type="text" id="reg-usuario" name="usuario" maxlength="16" autocomplete="username" required>

        <label for="reg-email">Correo electrónico</label>
        <input type="email" id="reg-email" name="email" autocomplete="email" required>

        <label for="reg-pass">Contraseña</label>
        <div class="campo-clave">
            <input type="password" id="reg-pass" name="contrasena" autocomplete="new-password" required>
            <button type="button" class="ver-clave" data-para="reg-pass">Mostrar</button>
        </div>

        <label for="reg-pass2">Repite la contraseña</label>
        <input type="password" id="reg-pass2" name="confirma" autocomplete="new-password" required>

        <button type="submit" class="boton">Crear cuenta</button>
    </form>
</section>

<script type="module" src="js/index.js"></script>
<?php require __DIR__ . '/includes/pie.php'; ?>
