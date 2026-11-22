<?php

$config = [];
$configPath = getenv('DB_CONFIG') ?: 'C:/xampp/config-dinocards.php';
if (is_file($configPath)) {
    $config = require $configPath;
}

$host = getenv('DB_HOST') ?: ($config['host'] ?? '');
$port = (int) (getenv('DB_PORT') ?: ($config['port'] ?? 3306));
$database = getenv('DB_NAME') ?: ($config['database'] ?? '');
$dbUser = getenv('DB_USER') ?: ($config['username'] ?? '');
$dbPass = getenv('DB_PASS');
if ($dbPass === false) {
    $dbPass = $config['password'] ?? '';
}

if ($host === '' || $database === '' || $dbUser === '') {
    throw new RuntimeException('La configuración de la base de datos está incompleta.');
}

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $dbUser, $dbPass, $database, $port);

if ($connection->connect_errno) {
    error_log('Error de conexión a MySQL: ' . $connection->connect_error);
    throw new RuntimeException('No se pudo conectar a la base de datos.');
}

$connection->set_charset('utf8mb4');

function PA_Ejecutar(
    string $nombre,
    string $tipos = '',
    array $parametros = [],
    bool $devuelveFilas = false
): array {
    global $connection;

    $marcadores = [];
    for ($i = 0; $i < strlen($tipos); $i++) {
        $marcadores[] = '?';
    }
    $marcadores[] = '@resultado';

    $sql = 'CALL `' . $nombre . '`(' . implode(', ', $marcadores) . ')';
    $stmt = $connection->prepare($sql);
    if (!$stmt) {
        error_log('Error al preparar ' . $nombre . ': ' . $connection->error);
        throw new RuntimeException('No se pudo completar la operación. Inténtalo de nuevo.');
    }

    if ($parametros) {
        $referencias = [];
        foreach ($parametros as &$parametro) {
            $referencias[] = &$parametro;
        }
        unset($parametro);
        $stmt->bind_param($tipos, ...$referencias);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        error_log('Error al ejecutar ' . $nombre . ': ' . $error);
        throw new RuntimeException('No se pudo completar la operación. Inténtalo de nuevo.');
    }

    $datos = [];
    if ($devuelveFilas) {
        $resultado = $stmt->get_result();
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $datos[] = $fila;
            }
            $resultado->free();
        }
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }
        $resultado = $stmt->get_result();
        if ($resultado) {
            if ($devuelveFilas) {
                while ($fila = $resultado->fetch_assoc()) {
                    $datos[] = $fila;
                }
            }
            $resultado->free();
        }
    }
    $stmt->close();

    $res = $connection->query('SELECT @resultado AS resultado');
    if (!$res) {
        error_log('Error al leer el resultado de ' . $nombre . ': ' . $connection->error);
        throw new RuntimeException('No se pudo completar la operación. Inténtalo de nuevo.');
    }
    $fila = $res->fetch_assoc();
    $res->free();

    return ['codigo' => (int) ($fila['resultado'] ?? -99), 'datos' => $datos];
}

function PA_Registrar(string $usuario, string $email, string $contrasenaHash): array
{
    return PA_Ejecutar('Registro', 'sss', [$usuario, $email, $contrasenaHash]);
}

function PA_Login(string $identificador): array
{
    return PA_Ejecutar('ObtenerUsuarioLogin', 's', [$identificador], true);
}

function PA_MigrarPassword(int $usuarioId, string $hash): array
{
    return PA_Ejecutar('MigrarPassword', 'is', [$usuarioId, $hash]);
}

function PA_ObtenerUsuarioPorId(int $usuarioId): array
{
    return PA_Ejecutar('ObtenerUsuarioPorId', 'i', [$usuarioId], true);
}

function PA_ObtenerColeccion(int $usuarioId): array
{
    return PA_Ejecutar('ObtenerColeccion', 'i', [$usuarioId], true);
}

function PA_AbrirCartaDiaria(int $usuarioId): array
{
    return PA_Ejecutar('AbrirCartaDiaria', 'i', [$usuarioId], true);
}

function PA_AnadirCartaElegida(int $usuarioId, int $cartaId): array
{
    return PA_Ejecutar('AnadirCartaElegida', 'ii', [$usuarioId, $cartaId], true);
}

function PA_AnadirCartasPrueba(int $usuarioId, int $cuantas): array
{
    return PA_Ejecutar('AnadirCartasPrueba', 'ii', [$usuarioId, $cuantas], true);
}

function PA_ObtenerResumenColeccion(int $usuarioId): array
{
    return PA_Ejecutar('ObtenerResumenColeccion', 'i', [$usuarioId], true);
}

function PA_ObtenerUltimasCartas(int $usuarioId, int $limite): array
{
    return PA_Ejecutar('ObtenerUltimasCartas', 'ii', [$usuarioId, $limite], true);
}

function PA_ObtenerDinosauriosPorIds(string $ids): array
{
    return PA_Ejecutar('ObtenerDinosauriosPorIds', 's', [$ids], true);
}

function PA_ContarDinosaurios(): array
{
    return PA_Ejecutar('ContarDinosaurios', '', [], true);
}

function PA_ListarDinosaurios(): array
{
    return PA_Ejecutar('ListarDinosaurios', '', [], true);
}

function PA_ActualizarImagenDinosaurio(int $id, string $url): array
{
    return PA_Ejecutar('ActualizarImagenDinosaurio', 'is', [$id, $url]);
}

require_once __DIR__ . '/ReglasColeccion.php';
require_once __DIR__ . '/UsuarioService.php';
require_once __DIR__ . '/ColeccionService.php';
