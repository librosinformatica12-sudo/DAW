<?php
/**
 * dal/conexion.php
 * ------------------------------------------------------------------
 * Capa de acceso a datos (DAL) - conexion y utilidades internas.
 *
 * ESTE ES EL UNICO PUNTO DEL PROYECTO QUE ABRE LA CONEXION A MYSQL.
 * Ningun otro .php debe usar mysqli_*, new mysqli ni SQL directamente:
 * deben llamar a las funciones de dal/UsuarioDAL.php, dal/ColeccionDAL.php
 * y dal/DinosaurioDAL.php.
 *
 * Caracteristicas:
 *   - Conexion perezosa y unica por peticion (singleton): no se abre
 *     hasta la primera consulta, asi que paginas como cerrar.php no
 *     tocan la base de datos.
 *   - Errores de MySQL como excepciones (mysqli_sql_exception). El
 *     manejador global de src/bootstrap.php las registra en el log y
 *     muestra un mensaje generico: nunca se filtran detalles al usuario.
 *   - Las funciones dal_* de aqui son internas de la capa de datos.
 * ------------------------------------------------------------------
 */

/** Devuelve la conexion mysqli (la crea la primera vez). */
function dal_conexion(): mysqli
{
    static $conexion = null;

    if ($conexion instanceof mysqli) {
        return $conexion;
    }

    // Lanza excepciones en vez de devolver false/avisos.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $cfg = require __DIR__ . '/../config/database.php';

    $conexion = new mysqli(
        $cfg['host'],
        $cfg['username'],
        $cfg['password'],
        $cfg['database'],
        $cfg['port']
    );
    $conexion->set_charset($cfg['charset']); // tildes, ñ, etc.

    return $conexion;
}

/**
 * Ejecuta una consulta preparada y devuelve TODAS las filas.
 *
 * @param string $sql    SQL con marcadores ?
 * @param string $tipos  Tipos de bind_param ("i", "ss", ...). Vacio si no hay parametros.
 * @param array  $params Valores, en el mismo orden que los ?.
 * @return array<int, array<string, mixed>>
 */
function dal_filas(string $sql, string $tipos = '', array $params = []): array
{
    $consulta = dal_conexion()->prepare($sql);
    try {
        if ($tipos !== '') {
            $consulta->bind_param($tipos, ...$params);
        }
        $consulta->execute();
        $filas = $consulta->get_result()->fetch_all(MYSQLI_ASSOC);
    } finally {
        $consulta->close();
    }

    return $filas;
}

/** Igual que dal_filas(), pero devuelve solo la primera fila (o null). */
function dal_fila(string $sql, string $tipos = '', array $params = []): ?array
{
    $filas = dal_filas($sql, $tipos, $params);

    return $filas[0] ?? null;
}

/**
 * Ejecuta INSERT / UPDATE / DELETE preparado.
 *
 * @return int Filas afectadas.
 */
function dal_ejecutar(string $sql, string $tipos = '', array $params = []): int
{
    $consulta = dal_conexion()->prepare($sql);
    try {
        if ($tipos !== '') {
            $consulta->bind_param($tipos, ...$params);
        }
        $consulta->execute();
        $afectadas = $consulta->affected_rows;
    } finally {
        $consulta->close();
    }

    return (int) $afectadas;
}
