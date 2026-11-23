<?php
declare(strict_types=1);

function autenticarUsuario(string $usuario, string $clave): ?array
{
    $credenciales = dal_credencialesUsuario($usuario);
    if ($credenciales === null) {
        return null;
    }

    $id       = (int) $credenciales['id'];
    $guardado = (string) ($credenciales['password_hash'] ?? '');

    if (password_verify($clave, $guardado)) {
        if (password_needs_rehash($guardado, PASSWORD_DEFAULT)) {
            dal_actualizarHash($id, password_hash($clave, PASSWORD_DEFAULT));
        }

        return $credenciales;
    }

    if (strlen($guardado) === 32 && hash_equals($guardado, md5($clave))) {
        dal_actualizarHash($id, password_hash($clave, PASSWORD_DEFAULT));

        return $credenciales;
    }

    return null;
}

function registrarUsuario(string $usuario, string $email, string $clave, string $confirmacion): array
{
    $usuario = trim($usuario);
    $email   = trim($email);

    if ($usuario === '' || $email === '' || $clave === '') {
        return ['ok' => false, 'mensaje' => 'Rellena todos los campos.'];
    }
    if (strlen($usuario) < 3) {
        return ['ok' => false, 'mensaje' => 'El usuario debe tener al menos 3 caracteres.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'mensaje' => 'El correo no es válido.'];
    }
    if (strlen($clave) < 4) {
        return ['ok' => false, 'mensaje' => 'La contraseña debe tener al menos 4 caracteres.'];
    }
    if ($clave !== $confirmacion) {
        return ['ok' => false, 'mensaje' => 'Las contraseñas no coinciden.'];
    }

    $duplicado = ['ok' => false, 'mensaje' => 'Ese usuario o correo ya está registrado.'];
    if (dal_existeUsuario($usuario, $email)) {
        return $duplicado;
    }
    if (!dal_crearUsuario($usuario, $email, password_hash($clave, PASSWORD_DEFAULT))) {
        return $duplicado;
    }

    return ['ok' => true, 'mensaje' => 'Registro completado. ¡Ya puedes iniciar sesión!'];
}
