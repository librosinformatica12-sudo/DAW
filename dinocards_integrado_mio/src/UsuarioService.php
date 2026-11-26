<?php
/**
 * UsuarioService.php
 * ------------------------------------------------------------------
 * Todo lo relacionado con el usuario: traer sus datos y marcar las
 * fechas de "ultima obtencion" (sobre del dia) y "carta_prueba"
 * (sobre de prueba).
 *
 * Usa la conexion $conexion definida en AccesoDatos.php.
 * Equivale (adaptado) a src/UsuarioService.php de dino-prueba.
 * ------------------------------------------------------------------
 */

/** Devuelve la fila del usuario, o null si no existe. */
function obtenerUsuarioPorId(int $usuario_id): ?array
{
    global $conexion;

    $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
    $consulta->bind_param("i", $usuario_id);
    $consulta->execute();
    $usuario = $consulta->get_result()->fetch_assoc();
    $consulta->close();

    return $usuario ?: null;
}

/** Marca ahora como la ultima vez que se abrio el sobre del dia. */
function marcarUltimaObtencion(int $usuario_id): void
{
    global $conexion;

    $consulta = $conexion->prepare("UPDATE usuarios SET ultima_obtencion = NOW() WHERE id = ?");
    $consulta->bind_param("i", $usuario_id);
    $consulta->execute();
    $consulta->close();
}

/** Marca ahora como la ultima vez que se abrio el sobre de prueba. */
function marcarCartaPrueba(int $usuario_id): void
{
    global $conexion;

    $consulta = $conexion->prepare("UPDATE usuarios SET carta_prueba = NOW() WHERE id = ?");
    $consulta->bind_param("i", $usuario_id);
    $consulta->execute();
    $consulta->close();
}
