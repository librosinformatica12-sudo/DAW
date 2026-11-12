<?php
// =====================================================================
// RESUMEN DEL ARCHIVO (léelo primero)
// Es la CAPA DE ACCESO A DATOS: el puente entre PHP y MySQL.
//   1) Abre UNA conexión a MySQL ($connection) que reutilizan las páginas.
//   2) PA_Ejecutar() es una función genérica que lanza CUALQUIER procedimiento
//      almacenado con el formato:  CALL nombre(?, ?, @resultado)
//   3) Hay una función PA_Algo() por cada procedimiento de peluqueria.sql.
//      Cada una solo llama a PA_Ejecutar con sus datos. Son casi idénticas.
//
// FLUJO COMPLETO DE UNA OPERACIÓN:
//   formulario HTML (POST) -> gestionar_xxx.php -> PA_Algo() -> PA_Ejecutar()
//   -> CALL en MySQL -> devuelve ["codigo" => 0, "datos" => [filas...]]
//
// TIPOS PARA bind_param (un carácter por cada parámetro IN, en el MISMO orden):
//   i = integer (INT)   s = string (VARCHAR, DATE, DATETIME)   d = double   b = blob
//
// [EXAMEN] Lo que te pueden quitar en este archivo:
//   - Los datos de conexión ($host, $usuario, $password, $baseDatos, $puerto).
//   - Una función PA_ entera: copia el patrón de otra y cambia nombre del
//     procedimiento, cadena de tipos y lista de parámetros.
//   - La cadena de tipos: "iss" = int, string, string.
//   - El 4º argumento true: ponlo SOLO si el procedimiento hace un SELECT.
// =====================================================================
// =========================================================
// ACCESO A DATOS - PELUQUERIA
// Las consultas SQL están en peluqueria.sql.
// Aquí solamente llamamos a los procedimientos con CALL.
// =========================================================

// DATOS DE CONEXIÓN. Deben coincidir con tu servidor MySQL:
//   - $host: 127.0.0.1 (o localhost) = tu propio ordenador.
//   - $usuario / $password: el usuario de MySQL. En XAMPP suele ser root y
//     contraseña vacía (""), pero aquí está configurado "oscar" / "1234".
//     Si en el examen no conecta, lo primero es revisar estos dos valores.
//   - $baseDatos: nombre de la BD creada en peluqueria.sql (peluqueria).
//   - $puerto: 3306 es el puerto por defecto de MySQL (en algunos XAMPP 3307).
$host = "127.0.0.1";
$usuario = "oscar";
$password = "1234";
$baseDatos = "peluqueria";
$puerto = 3306;

// Hace que mysqli LANCE EXCEPCIONES (mysqli_sql_exception) cuando hay un
// error en vez de dar solo un warning. Por eso se usa try/catch más abajo.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// CONEXIÓN. Orden de los argumentos de new mysqli:
//   (servidor, usuario, contraseña, base de datos, puerto)
// set_charset("utf8mb4") evita problemas con tildes, ñ y emojis.
// Si falla la conexión, catch muestra el error y die() detiene el script.
// htmlspecialchars se usa para no pintar texto peligroso en la página.
try {
    $connection = new mysqli($host, $usuario, $password, $baseDatos, $puerto);
    $connection->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de conexión con MySQL: " . htmlspecialchars($e->getMessage()));
}

/*
 * Función auxiliar:
 * - Prepara un CALL con parámetros IN y un OUT @resultado.
 * - Si el procedimiento hace SELECT, recoge las filas.
 * - Al final consulta el valor OUT.
 *
 * Devuelve: ["codigo" => número, "datos" => array de filas]
 */
// PARÁMETROS DE PA_Ejecutar:
//   $nombre        -> nombre del procedimiento en MySQL ("AltaCliente").
//   $tipos         -> cadena de tipos para bind_param ("ss", "i", "iiss"...).
//   $parametros    -> array con los valores IN, en el orden del procedimiento.
//   $devuelveFilas -> true si el procedimiento hace SELECT (consultas).
// NOTA: el parámetro OUT (_res) NO se pasa desde PHP; siempre es @resultado.
// RESULTADO: array con "codigo" (0 = bien, negativo = error de validación)
//            y "datos" (filas del SELECT, vacío si no hay).
function PA_Ejecutar($nombre, $tipos = "", $parametros = [], $devuelveFilas = false)
{
    // global: permite usar dentro de la función la variable $connection que se
    // creó fuera. Sin esto PHP no la vería (las funciones tienen su propio ámbito).
    global $connection;

    // Los nombres de procedimiento se escriben fijos en nuestras funciones PA.
    // PASO 1: construir la lista de marcadores. Un "?" por cada parámetro IN
    // y al final "@resultado" para el parámetro OUT.
    // Ejemplo con 2 parámetros: ["?", "?", "@resultado"]
    $marcadores = [];
    foreach ($parametros as $p) {
        $marcadores[] = "?";
    }
    $marcadores[] = "@resultado";

    // PASO 2: montar el CALL. implode() une el array con ", ".
    // Resultado de ejemplo:  CALL AltaCliente(?, ?, @resultado)
    // El nombre va concatenado (no se puede usar ? para nombres de procedimiento);
    // es seguro porque lo escribimos nosotros en las funciones PA_, no el usuario.
    $sql = "CALL " . $nombre . "(" . implode(", ", $marcadores) . ")";
    // PASO 3: prepare() crea una SENTENCIA PREPARADA. Los valores se envían aparte
    // del SQL, lo que evita la INYECCIÓN SQL.
    $stmt = $connection->prepare($sql);

    // PASO 4: enlazar valores con bind_param. Se salta si no hay parámetros IN
    // (por ejemplo ConsultarClientes no recibe nada).
    if (count($parametros) > 0) {
        // mysqli necesita referencias para bind_param.
        // bind_param exige referencias (&), no valores. Construimos un array de
        // referencias y luego lo "desempaquetamos" con ... (operador spread).
        $referencias = [];
        foreach ($parametros as $i => &$valor) {
            $referencias[$i] = &$valor;
        }
        // Equivale a $stmt->bind_param("ss", $nombre, $telefono). Los tipos de $tipos
        // deben coincidir en número y orden con los valores, si no da error.
        $stmt->bind_param($tipos, ...$referencias);
        unset($valor);
    }

    // PASO 5: ejecutar el CALL en MySQL.
    $stmt->execute();

    // PASO 6: recoger las filas del SELECT (solo si $devuelveFilas es true).
    $datos = [];
    if ($devuelveFilas) {
        // get_result() obtiene el conjunto de filas; fetch_all(MYSQLI_ASSOC) las
        // convierte en un array de arrays asociativos: $datos[0]["nombre"], etc.
        // Las claves son los NOMBRES DE COLUMNA (o alias AS) del SELECT en MySQL.
        // (get_result necesita el driver mysqlnd, que viene con XAMPP.)
        $resultado = $stmt->get_result();
        if ($resultado) {
            $datos = $resultado->fetch_all(MYSQLI_ASSOC);
            $resultado->free();
        }
    }

    // Consumir todos los resultados pendientes del CALL.
    // PASO 7: un CALL devuelve un resultado extra (estado del procedimiento).
    // Hay que consumirlo todo con more_results/next_result; si no, la siguiente
    // consulta falla con "Commands out of sync".
    while ($stmt->more_results()) {
        $stmt->next_result();
        $extra = $stmt->get_result();
        if ($extra) {
            $extra->free();
        }
    }
    // Cerrar la sentencia para liberar recursos.
    $stmt->close();

    // Recuperar el parámetro OUT.
    // PASO 8: leer el parámetro OUT. @resultado es una variable de SESIÓN de
    // MySQL, por eso hay que leerla con la MISMA conexión ($connection).
    // AS codigo -> alias para leerlo como $filaRes["codigo"].
    $consultaRes = $connection->query("SELECT @resultado AS codigo");
    $filaRes = $consultaRes->fetch_assoc();
    $consultaRes->free();

    // PASO 9: devolver el código y los datos. "?? -99" = si no hubo valor,
    // se devuelve -99 (error desconocido). (int) fuerza que sea número.
    return [
        "codigo" => (int)($filaRes["codigo"] ?? -99),
        "datos" => $datos
    ];
}

// =====================================================================
// FUNCIONES PA_: una por cada procedimiento almacenado.
// Todas siguen el MISMO patrón. Para crear una nueva (típico de examen):
//   function PA_Nombre($a, $b) {
//       return PA_Ejecutar("NombreProcedimiento", "tipos", [$a, $b], true/false);
//   }
// El nombre debe ser IDÉNTICO al CREATE PROCEDURE de peluqueria.sql.
// Pon true como 4º argumento solo en las que hacen SELECT.
// =====================================================================
// ------------------------- CLIENTES -------------------------

// INSERT: 2 parámetros string (nombre, teléfono) -> "ss". No hace SELECT.
function PA_AltaCliente($nombre, $telefono) {
    return PA_Ejecutar("AltaCliente", "ss", [$nombre, $telefono]);
}

// DELETE: 1 parámetro entero (id) -> "i".
function PA_BajaCliente($id_cliente) {
    return PA_Ejecutar("BajaCliente", "i", [$id_cliente]);
}

// UPDATE: id (int) + nombre y teléfono (string) -> "iss".
function PA_ModificarCliente($id_cliente, $nombre, $telefono) {
    return PA_Ejecutar("ModificarCliente", "iss", [$id_cliente, $nombre, $telefono]);
}

// SELECT sin parámetros: tipos "" y array vacío []. true = devuelve filas.
function PA_ConsultarClientes() {
    return PA_Ejecutar("ConsultarClientes", "", [], true);
}

// SELECT con un id ("i") y true para recoger la fila.
function PA_ConsultarClientePorID($id_cliente) {
    return PA_Ejecutar("ConsultarClientePorID", "i", [$id_cliente], true);
}

// ------------------------- PELUQUEROS -------------------------
// Igual que clientes pero con la columna especialidad (string) en vez de teléfono.

// INSERT: nombre y especialidad -> "ss".
function PA_AltaPeluquero($nombre, $especialidad) {
    return PA_Ejecutar("AltaPeluquero", "ss", [$nombre, $especialidad]);
}

function PA_BajaPeluquero($id_peluquero) {
    return PA_Ejecutar("BajaPeluquero", "i", [$id_peluquero]);
}

// UPDATE: id + nombre + especialidad -> "iss".
function PA_ModificarPeluquero($id_peluquero, $nombre, $especialidad) {
    return PA_Ejecutar("ModificarPeluquero", "iss", [$id_peluquero, $nombre, $especialidad]);
}

function PA_ConsultarPeluqueros() {
    return PA_Ejecutar("ConsultarPeluqueros", "", [], true);
}

function PA_ConsultarPeluqueroPorID($id_peluquero) {
    return PA_Ejecutar("ConsultarPeluqueroPorID", "i", [$id_peluquero], true);
}

// ------------------------- RESERVAS -------------------------
// Las reservas llevan 4 datos: id_cliente (i), id_peluquero (i),
// fecha_hora (s, porque se envía como texto 'AAAA-MM-DD HH:MM:SS') y servicio (s).

// INSERT: "iiss" = int, int, string (fecha), string (servicio).
function PA_AltaReserva($id_cliente, $id_peluquero, $fecha_hora, $servicio) {
    return PA_Ejecutar(
        "AltaReserva",
        "iiss",
        [$id_cliente, $id_peluquero, $fecha_hora, $servicio]
    );
}

function PA_BajaReserva($id_reserva) {
    return PA_Ejecutar("BajaReserva", "i", [$id_reserva]);
}

// UPDATE: "iiiss" = id_reserva, id_cliente, id_peluquero (int) + fecha y servicio (string).
function PA_ModificarReserva($id_reserva, $id_cliente, $id_peluquero, $fecha_hora, $servicio) {
    return PA_Ejecutar(
        "ModificarReserva",
        "iiiss",
        [$id_reserva, $id_cliente, $id_peluquero, $fecha_hora, $servicio]
    );
}

// SELECT de la tabla reservas sin parámetros.
function PA_ConsultarReservas() {
    return PA_Ejecutar("ConsultarReservas", "", [], true);
}

function PA_ConsultarReservaPorID($id_reserva) {
    return PA_Ejecutar("ConsultarReservaPorID", "i", [$id_reserva], true);
}

// Filtros por cliente / peluquero (int) o por fecha (string, formato AAAA-MM-DD).
function PA_ConsultarReservasPorCliente($id_cliente) {
    return PA_Ejecutar("ConsultarReservasPorCliente", "i", [$id_cliente], true);
}

function PA_ConsultarReservasPorPeluquero($id_peluquero) {
    return PA_Ejecutar("ConsultarReservasPorPeluquero", "i", [$id_peluquero], true);
}

// La fecha es string ("s"): MySQL la convierte a DATE.
function PA_ConsultarReservasPorFecha($fecha) {
    return PA_Ejecutar("ConsultarReservasPorFecha", "s", [$fecha], true);
}

// La usa la página de reservas: devuelve reservas + nombre/teléfono del cliente
// + nombre/especialidad del peluquero (ver INNER JOIN en peluqueria.sql).
function PA_ConsultarReservasConJOIN() {
    return PA_Ejecutar("ConsultarReservasConJOIN", "", [], true);
}

// Dos parámetros string (inicio y fin) -> "ss".
function PA_ConsultarReservasEntreFechas($fecha_inicio, $fecha_fin) {
    return PA_Ejecutar(
        "ConsultarReservasEntreFechas",
        "ss",
        [$fecha_inicio, $fecha_fin],
        true
    );
}

// Devuelve una fila con COUNT(*) en la columna total: $r["datos"][0]["total"].
function PA_ContarReservas() {
    return PA_Ejecutar("ContarReservas", "", [], true);
}

// id del peluquero (i) + fecha y hora (s). Si devuelve filas, ese hueco está ocupado.
function PA_ConsultarDisponibilidadPeluquero($id_peluquero, $fecha_hora) {
    return PA_Ejecutar(
        "ConsultarDisponibilidadPeluquero",
        "is",
        [$id_peluquero, $fecha_hora],
        true
    );
}
// Nota: en archivos que solo contienen PHP la etiqueta de cierre es opcional.
?>
