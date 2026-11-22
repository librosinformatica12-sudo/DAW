<?php
/**
 * dal/UsuarioDAL.php
 * ------------------------------------------------------------------
 * Acceso a datos de la tabla `usuarios`.
 * Todas las consultas son preparadas (sin concatenar entradas en el SQL).
 * ------------------------------------------------------------------
 */

/**
 * Devuelve los datos publicos del usuario, o null si no existe.
 * Nunca incluye password_hash: las paginas no lo necesitan.
 */
function obtenerUsuarioPorId(int $usuario_id): ?array
{
    return dal_fila(
        "SELECT id, username, email, fecha_registro, ultima_obtencion, carta_prueba
         FROM usuarios WHERE id = ?",
        "i",
        [$usuario_id]
    );
}

/** Fecha (string) de la ultima vez que abrio el sobre del dia, o null. */
function obtenerUltimaObtencion(int $usuario_id): ?string
{
    $fila = dal_fila("SELECT ultima_obtencion FROM usuarios WHERE id = ?", "i", [$usuario_id]);

    return $fila["ultima_obtencion"] ?? null;
}

/**
 * Busca una cuenta por correo o por nombre de usuario para el login.
 * Devuelve ['id', 'username', 'password_hash'] o null.
 */
function obtenerUsuarioParaLogin(string $identificador): ?array
{
    return dal_fila(
        "SELECT id, username, password_hash FROM usuarios
         WHERE email = ? OR username = ?
         LIMIT 1",
        "ss",
        [$identificador, $identificador]
    );
}

/** Guarda un nuevo hash de contraseña (p. ej. al migrar un md5 antiguo). */
function actualizarPasswordHash(int $usuario_id, string $hash): bool
{
    return dal_ejecutar(
        "UPDATE usuarios SET password_hash = ? WHERE id = ?",
        "si",
        [$hash, $usuario_id]
    ) > 0;
}

/** ¿Existe ya un usuario con ese nombre o ese correo? */
function existeUsuarioOEmail(string $username, string $email): bool
{
    return dal_fila(
        "SELECT id FROM usuarios WHERE username = ? OR email = ? LIMIT 1",
        "ss",
        [$username, $email]
    ) !== null;
}

/**
 * Crea un usuario. Devuelve false si no se pudo (p. ej. nombre o correo
 * duplicados por una peticion simultanea: los indices UNIQUE lo impiden).
 */
function crearUsuario(string $username, string $email, string $password_hash): bool
{
    try {
        return dal_ejecutar(
            "INSERT INTO usuarios (username, email, password_hash, fecha_registro)
             VALUES (?, ?, ?, NOW())",
            "sss",
            [$username, $email, $password_hash]
        ) === 1;
    } catch (mysqli_sql_exception $e) {
        error_log("[DinoCards] crearUsuario fallo: " . $e->getMessage());
        return false;
    }
}

/** Marca ahora como la ultima vez que se abrio el sobre del dia. */
function marcarUltimaObtencion(int $usuario_id): void
{
    dal_ejecutar("UPDATE usuarios SET ultima_obtencion = NOW() WHERE id = ?", "i", [$usuario_id]);
}

/** Marca ahora como la ultima vez que se abrio el sobre de prueba. */
function marcarCartaPrueba(int $usuario_id): void
{
    dal_ejecutar("UPDATE usuarios SET carta_prueba = NOW() WHERE id = ?", "i", [$usuario_id]);
}
