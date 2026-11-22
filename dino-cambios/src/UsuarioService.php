<?php

function hashContrasena(string $contrasena): string
{
    return password_hash($contrasena, PASSWORD_DEFAULT);
}

function verificarContrasena(string $contrasena, string $guardado): bool
{
    if (password_verify($contrasena, $guardado)) {
        return true;
    }

    return strlen($guardado) === 32 && hash_equals($guardado, md5($contrasena));
}

function esHashAntiguo(string $guardado): bool
{
    return strlen($guardado) === 32;
}

function obtenerUsuarioPorId(int $usuarioId): ?array
{
    $resultado = PA_ObtenerUsuarioPorId($usuarioId);

    return $resultado['datos'][0] ?? null;
}
