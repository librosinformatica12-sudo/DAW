<?php

require_once __DIR__ . '/../src/AccesoDatos.php';
require_once __DIR__ . '/../src/Respuesta.php';

ejecutarEndpoint(function (): void {
    $usuario = trim((string) ($_POST['usuario'] ?? $_POST['username'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $contrasena = (string) ($_POST['contrasena'] ?? $_POST['password'] ?? '');
    $confirma = (string) ($_POST['confirmaContrasena'] ?? $_POST['confirmPassword'] ?? '');

    if ($usuario === '' || $email === '' || $contrasena === '') {
        responder(false, 'Todos los campos son obligatorios.');
    }
    if (mb_strlen($usuario) < 3 || mb_strlen($usuario) > 16) {
        responder(false, 'El usuario debe tener entre 3 y 16 caracteres.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        responder(false, 'El email no es válido.');
    }
    if (mb_strlen($contrasena) < 4) {
        responder(false, 'La contraseña debe tener al menos 4 caracteres.');
    }
    if ($contrasena !== $confirma) {
        responder(false, 'Las contraseñas no coinciden.');
    }

    $codigo = PA_Registrar($usuario, $email, hashContrasena($contrasena))['codigo'];

    $mensajes = [
        -1 => 'El usuario o email no pueden estar vacíos.',
        -2 => 'El usuario o el correo electrónico ya están registrados.',
        -3 => 'La contraseña no puede estar vacía.',
    ];

    if ($codigo === 0) {
        responder(true, 'Registro completado con éxito. Ya puedes iniciar sesión.');
    }
    responder(false, $mensajes[$codigo] ?? 'No se pudo completar el registro.');
});
