<?php
declare(strict_types=1);

const PROCEDIMIENTOS_PERMITIDOS = [
    'sp_usuario_por_id'           => true,
    'sp_credenciales_usuario'     => true,
    'sp_usuario_existe'           => true,
    'sp_usuario_crear'            => true,
    'sp_usuario_actualizar_hash'  => true,
    'sp_abrir_sobre_dia'          => true,
    'sp_abrir_sobre_prueba'       => true,
    'sp_coleccion_ids'            => true,
    'sp_coleccion_resumen'        => true,
    'sp_ultimas_cartas'           => true,
    'sp_dino_contar'              => true,
    'sp_dino_todos'               => true,
    'sp_dino_aleatorio'           => true,
    'sp_dino_aleatorios_n'        => true,
    'sp_dino_por_ids'             => true,
];

function configuracionBaseDatos(): array
{
    return [
        'host'     => (string) (getenv('DINOCARDS_DB_HOST') ?: '127.0.0.1'),
        'port'     => (int) (getenv('DINOCARDS_DB_PORT') ?: 3306),
        'database' => (string) (getenv('DINOCARDS_DB_NAME') ?: 'dinocards'),
        'username' => (string) (getenv('DINOCARDS_DB_USER') ?: 'root'),
        'password' => (string) getenv('DINOCARDS_DB_PASS'),
    ];
}

function conexionBaseDatos(): mysqli
{
    static $conexion = null;

    if ($conexion instanceof mysqli) {
        return $conexion;
    }

    $config = configuracionBaseDatos();

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conexion = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port']
    );
    $conexion->set_charset('utf8mb4');

    return $conexion;
}

function dal_llamar(string $procedimiento, string $tipos = '', array $valores = []): array
{
    if (!isset(PROCEDIMIENTOS_PERMITIDOS[$procedimiento])) {
        throw new InvalidArgumentException('Procedimiento no permitido.');
    }
    if (strlen($tipos) !== count($valores)) {
        throw new InvalidArgumentException('Número de parámetros incorrecto.');
    }

    $conexion = conexionBaseDatos();
    $marcadores = $tipos === '' ? '' : implode(',', array_fill(0, count($valores), '?'));
    $consulta = $conexion->prepare("CALL {$procedimiento}({$marcadores})");
    if ($tipos !== '') {
        $consulta->bind_param($tipos, ...$valores);
    }
    $consulta->execute();

    $resultado = $consulta->get_result();
    $filas = [];
    if ($resultado instanceof mysqli_result) {
        while ($fila = $resultado->fetch_assoc()) {
            $filas[] = $fila;
        }
        $resultado->free();
    }
    $consulta->close();

    while ($conexion->more_results()) {
        if (!$conexion->next_result()) {
            break;
        }
        $extra = $conexion->store_result();
        if ($extra instanceof mysqli_result) {
            $extra->free();
        }
    }

    return $filas;
}
