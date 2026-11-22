<?php
/**
 * src/AuthService.php
 * ------------------------------------------------------------------
 * Logica de negocio de autenticacion y alta de usuarios.
 * No contiene SQL: usa las funciones de dal/UsuarioDAL.php.
 * ------------------------------------------------------------------
 */

/**
 * Comprueba usuario/correo + contraseña.
 *
 * Acepta contraseñas modernas (password_hash) y las antiguas guardadas con
 * md5, que se actualizan automaticamente al formato nuevo al entrar.
 *
 * @return array|null ['id' => int, 'username' => string] si es correcto; null si no.
 */
function autenticarUsuario(string $identificador, string $clave): ?array
{
    $fila = obtenerUsuarioParaLogin($identificador);
    if (!$fila) {
        return null;
    }

    $guardado = (string) $fila["password_hash"];
    $correcto = false;

    if (password_verify($clave, $guardado)) {
        $correcto = true;
    } elseif (strlen($guardado) === 32 && hash_equals($guardado, md5($clave))) {
        // Contraseña antigua (md5): se acepta y se migra a password_hash.
        $correcto = true;
        actualizarPasswordHash((int) $fila["id"], password_hash($clave, PASSWORD_DEFAULT));
    }

    if (!$correcto) {
        return null;
    }

    return ['id' => (int) $fila["id"], 'username' => (string) $fila["username"]];
}

/**
 * Valida los datos del formulario de registro y crea la cuenta.
 *
 * @return array ['ok' => bool, 'mensaje' => string]
 */
function registrarUsuario(string $usuario, string $email, string $clave1, string $clave2): array
{
    if ($usuario === "" || $email === "" || $clave1 === "") {
        return ['ok' => false, 'mensaje' => "Rellena todos los campos."];
    }
    if (mb_strlen($usuario, "UTF-8") < 3) {
        return ['ok' => false, 'mensaje' => "El usuario debe tener al menos 3 caracteres."];
    }
    // Mismos limites que el formulario (maxlength): ahora tambien se exigen en el servidor.
    if (mb_strlen($usuario, "UTF-8") > 16) {
        return ['ok' => false, 'mensaje' => "El usuario no puede tener más de 16 caracteres."];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email, "UTF-8") > 100) {
        return ['ok' => false, 'mensaje' => "El correo no es válido."];
    }
    if (strlen($clave1) < 4) {
        return ['ok' => false, 'mensaje' => "La contraseña debe tener al menos 4 caracteres."];
    }
    if ($clave1 !== $clave2) {
        return ['ok' => false, 'mensaje' => "Las contraseñas no coinciden."];
    }

    if (existeUsuarioOEmail($usuario, $email)) {
        return ['ok' => false, 'mensaje' => "Ese usuario o correo ya está registrado."];
    }

    if (crearUsuario($usuario, $email, password_hash($clave1, PASSWORD_DEFAULT))) {
        return ['ok' => true, 'mensaje' => "Registro completado. ¡Ya puedes iniciar sesión!"];
    }

    return ['ok' => false, 'mensaje' => "No se pudo completar el registro."];
}
