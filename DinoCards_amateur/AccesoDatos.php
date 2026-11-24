<?php

$host = '127.0.0.1';
$port = 3306;
$database = 'dinocards';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

function falloAccesoDatos(string $detalle): void
{
    error_log($detalle);
    responder('No se pudo completar la operación. Inténtalo de nuevo.', 500);
}

function PA_Registrar(string $nick, string $email, string $contrasena)
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL Registro(?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }
    $contrasenaHash = password_hash($contrasena, PASSWORD_DEFAULT);
    $stmt->bind_param('sss', $nick, $email, $contrasenaHash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}

function verificarContrasena(string $contrasena, string $hash): bool
{
    if ($hash === '') {
        return false;
    }

    if (password_verify($contrasena, $hash)) {
        return true;
    }

    return hash_equals($hash, md5($contrasena));
}

function PA_Login(string $email, string $contrasena): array
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $email = trim($email);
    if ($email === '') {
        return ['resultado' => -1, 'usuario' => null];
    }
    if ($contrasena === '') {
        return ['resultado' => -2, 'usuario' => null];
    }

    $sql = 'SELECT * FROM usuarios WHERE email = ? OR username = ? LIMIT 1';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar la consulta: ' . $connection->error);
    }

    $stmt->bind_param('ss', $email, $email);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar la consulta: ' . $error);
    }

    $resultadoUsuario = $stmt->get_result();
    $usuario = $resultadoUsuario ? $resultadoUsuario->fetch_assoc() : null;
    if ($resultadoUsuario) {
        $resultadoUsuario->free();
    }
    $stmt->close();

    if (!is_array($usuario)) {
        return ['resultado' => -3, 'usuario' => null];
    }

    if (!verificarContrasena($contrasena, (string) ($usuario['password_hash'] ?? ''))) {
        return ['resultado' => -3, 'usuario' => null];
    }

    return ['resultado' => 0, 'usuario' => $usuario];
}

/** @return array{resultado: int, coleccion: list<array<string, mixed>>} */
function PA_ObtenerColeccion(int $idUsuario): array
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL ObtenerColeccion(?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }

    $stmt->bind_param('i', $idUsuario);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    $coleccion = [];
    if ($stmt->field_count > 0) {
        $resultadoColeccion = $stmt->get_result();
        if ($resultadoColeccion) {
            while ($fila = $resultadoColeccion->fetch_assoc()) {
                $fila['id'] = (int) $fila['id'];
                $coleccion[] = $fila;
            }
            $resultadoColeccion->free();
        }
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }

        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return [
        'resultado' => (int) $fila['resultado'],
        'coleccion' => $coleccion,
    ];
}

function PA_ObtenerUsuariosParaTrade() 
{
    
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos("Error de conexión: " . $connection->connect_error);
    }

    $sql = "CALL UserListReadyTrading(@resultado)";
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos("Error al preparar el procedimiento: " . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos("Error al ejecutar el procedimiento: " . $error);
    }


    // Primer conjunto de resultados (la lista de usuarios)
    $ListaUsuariosTrading = [];
    if ($stmt->field_count > 0) {
        $resultadoColeccion = $stmt->get_result();
        if ($resultadoColeccion) {
            while ($fila = $resultadoColeccion->fetch_assoc()) {
                $fila['id'] = (int) $fila['id'];
                $ListaUsuariosTrading[] = $fila;
            }
            $resultadoColeccion->free();
        }
    }

    // Limpiar posibles conjuntos de resultados adicionales del procedimiento
    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }
        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    // Recuperar el parámetro OUT @resultado
    $resOut = $connection->query("SELECT @resultado AS resultado");
    if (!$resOut) {
        falloAccesoDatos("Error al recuperar el parámetro OUT: " . $connection->error);
    }

    $filaOut = $resOut->fetch_assoc();
    $resOut->free();

    return [
        'resultado' => (int) $filaOut['resultado'],
        'coleccion' => $ListaUsuariosTrading
    ];
}

function PA_AñadirNuevaCartaAColeccion(int $idUsuario): int
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL `AñadirNuevaCartaAColeccion`(?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }

    $stmt->bind_param('i', $idUsuario);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }

        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int) $fila['resultado'];
}

function PA_CambiarListoParaNegociar()
{
    global $connection;

    if ($connection->connect_errno) {
        falloAccesoDatos('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL `SetListoParaNegociar`()';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        falloAccesoDatos('Error al preparar el procedimiento: ' . $connection->error);
    }

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        falloAccesoDatos('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        if (!$stmt->next_result()) {
            break;
        }

        if ($stmt->field_count > 0) {
            $resultadoAdicional = $stmt->get_result();
            if ($resultadoAdicional) {
                $resultadoAdicional->free();
            }
        }
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    if (!$resultado) {
        falloAccesoDatos('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return (int) $fila['resultado'];
}

