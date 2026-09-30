<?php
// ====== Conexión a la base de datos Sakila ======
$host = '127.0.0.1';
$port = 3306;
$database = 'sakila';
$username = 'oscar';
$password = '561Cazadora#%';

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);

if (!$connection->connect_errno) {
    $connection->set_charset('utf8mb4');
}

// ====== Registro (procedimiento Registro) ======
// Devuelve el código de salida: > 0 = staff_id nuevo, negativo = error
function PA_Registrar(string $nombre, string $apellido, string $email, int $tienda, string $usuario, string $contrasena)
{
    global $connection;

    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    $sql = 'CALL Registro(?, ?, ?, ?, ?, ?, @resultado)';
    $stmt = $connection->prepare($sql);

    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    $contrasenaHash = md5($contrasena);
    $stmt->bind_param('sssiss', $nombre, $apellido, $email, $tienda, $usuario, $contrasenaHash);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    while ($stmt->more_results()) {
        $stmt->next_result();
    }

    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar el parámetro OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}

/*
// =====================================================================
// PA_Login: llama al procedimiento almacenado "Login" de MySQL
// ---------------------------------------------------------------------
// Recibe: el usuario (o correo) y la contraseña tal cual la escribió
//         la persona en el formulario (sin hashear).
// Devuelve un array con 3 cosas:
//   'res'      -> código del procedure (1 ok, -1 vacíos, -2 incorrecto...)
//   'extra'    -> dato auxiliar (intentos que quedan / minutos de bloqueo)
//   'empleado' -> datos del empleado si el login fue bien, o null si falló
// =====================================================================
function PA_Login(string $usuario, string $contrasena)
{
    // Usamos la conexión $connection creada en el archivo de conexión
    global $connection;

    // Si la conexión falló, no tiene sentido seguir
    if ($connection->connect_errno) {
        throw new RuntimeException('Error de conexión: ' . $connection->connect_error);
    }

    // Preparamos la llamada al procedure:
    // los ? son los 2 parámetros de entrada, @res y @extra recogen los OUT
    $stmt = $connection->prepare('CALL Login(?, ?, @res, @extra)');

    // Si prepare() falla (por ejemplo, el procedure no existe), devuelve false
    if (!$stmt) {
        throw new RuntimeException('Error al preparar el procedimiento: ' . $connection->error);
    }

    // Hasheamos la contraseña con md5 para que coincida con la guardada en staff
    $contrasenaHash = md5($contrasena);

    // 'ss' = dos strings (usuario y hash). Evita la inyección SQL
    $stmt->bind_param('ss', $usuario, $contrasenaHash);

    // Ejecutamos; si falla, guardamos el error, cerramos y lanzamos excepción
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        throw new RuntimeException('Error al ejecutar el procedimiento: ' . $error);
    }

    // Si el login es correcto, el procedure devuelve una fila con los datos
    // del empleado. Si falló, get_result() da false
    $empleado = null;
    $rs = $stmt->get_result();
    if ($rs) {
        $empleado = $rs->fetch_assoc();
        $rs->free();
    }

    // Vaciamos resultados pendientes del CALL (evita "Commands out of sync")
    while ($stmt->more_results()) {
        $stmt->next_result();
    }

    $stmt->close();

    // Leemos los parámetros OUT del procedure
    $resultado = $connection->query('SELECT @res AS res, @extra AS extra');

    if (!$resultado) {
        throw new RuntimeException('Error al recuperar los parámetros OUT: ' . $connection->error);
    }

    $fila = $resultado->fetch_assoc();
    $resultado->free();

    // Devolvemos todo junto; (int) convierte los textos de MySQL a números
    return [
        'res'      => (int) $fila['res'],
        'extra'    => (int) $fila['extra'],
        'empleado' => $empleado,
    ];
}

// =====================================================================
// Utilidades para las páginas
// =====================================================================

// Arranca la sesión de PHP con una duración de 30 días
function iniciarSesion()
{
    ini_set('session.gc_maxlifetime', 2592000); // 30 días en segundos
    session_start();
}

// Envía una respuesta JSON y termina el script
function responder($datos, $codigo = 200)
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    exit;
}

// Lee el JSON que envía JavaScript (fetch) y lo convierte en array
function leerJson()
{
    $d = json_decode(file_get_contents('php://input'), true);
    return is_array($d) ? $d : [];
}
*/