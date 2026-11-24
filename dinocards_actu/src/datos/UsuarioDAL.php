<?php
declare(strict_types=1);

function dal_usuarioPorId(int $id): ?array
{
    $filas = dal_llamar('sp_usuario_por_id', 'i', [$id]);

    return $filas[0] ?? null;
}

function dal_credencialesUsuario(string $identificador): ?array
{
    $filas = dal_llamar('sp_credenciales_usuario', 's', [$identificador]);

    return $filas[0] ?? null;
}

function dal_existeUsuario(string $usuario, string $email): bool
{
    $filas = dal_llamar('sp_usuario_existe', 'ss', [$usuario, $email]);

    return (bool) ($filas[0]['existe'] ?? 0);
}

function dal_crearUsuario(string $usuario, string $email, string $hash): bool
{
    $filas = dal_llamar('sp_usuario_crear', 'sss', [$usuario, $email, $hash]);

    return (bool) ($filas[0]['creado'] ?? 0);
}

function dal_actualizarHash(int $id, string $hash): void
{
    dal_llamar('sp_usuario_actualizar_hash', 'is', [$id, $hash]);
}
