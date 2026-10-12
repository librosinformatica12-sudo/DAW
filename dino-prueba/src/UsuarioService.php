<?php
/**
 * UsuarioService.php
 * ------------------------------------------------------------------
 * Lógica de usuarios: registro, login y datos del usuario.
 * Usa la conexión $connection definida en AccesoDatos.php.
 * ------------------------------------------------------------------
 */

/** Hash de la contraseña (md5 por compatibilidad con la BD actual). */
function hashContrasena(string $contrasena): string
{
    return md5($contrasena);
}

/** Devuelve el código de salida (_res) del procedimiento Registro. */
function PA_Registrar(string $usuario, string $email, string $contrasena): int
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    // El SP 'Registro' recibe 3 IN (username, email, password) y 1 OUT (_res)
    $stmt = $connection->prepare('CALL Registro(?, ?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('sss', $usuario, $email, $hash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }
    $stmt->close();

    $res = $connection->query('SELECT @resultado AS resultado');
    if (!$res) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }
    $fila = $res->fetch_assoc();
    $res->free();

    return (int) $fila['resultado'];
}

/** Devuelve ['codigo' => int, 'usuario' => array|null]. */
function PA_Login(string $email, string $contrasena): array
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    // El SP 'Login' recibe 2 IN (email, password) y 1 OUT (_res)
    $stmt = $connection->prepare('CALL Login(?, ?, @resultado)');
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $hash = hashContrasena($contrasena);
    $stmt->bind_param('ss', $email, $hash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    $datosUsuario = null;
    do {
        $rs = $stmt->get_result();
        if ($rs) {
            $fila = $rs->fetch_assoc();
            if ($fila) {
                $datosUsuario = $fila;
            }
            $rs->free();
        }
    } while ($stmt->more_results() && $stmt->next_result());
    $stmt->close();

    $res = $connection->query('SELECT @resultado AS resultado');
    if (!$res) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }
    $fila = $res->fetch_assoc();
    $res->free();

    return ['codigo' => (int) $fila['resultado'], 'usuario' => $datosUsuario];
}

/** Devuelve los datos de un usuario por su id, o null si no existe. */
function obtenerUsuarioPorId(int $usuarioId): ?array
{
    global $connection;

    if ($connection->connect_errno) {
        return null;
    }

    $stmt = $connection->prepare(
        'SELECT id, username, email, fecha_registro, ultima_obtencion
         FROM usuarios WHERE id = ? LIMIT 1'
    );
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $fila = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $fila ?: null;
}

/** Actualiza la fecha/hora de la última carta obtenida del usuario. */
function marcarUltimaObtencion(int $usuarioId): void
{
    global $connection;

    if ($connection->connect_errno) {
        return;
    }

    $stmt = $connection->prepare('UPDATE usuarios SET ultima_obtencion = NOW() WHERE id = ?');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $stmt->close();
}
