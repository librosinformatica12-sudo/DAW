<?php
// =========================================================
// ACCESO A DATOS - PELUQUERIA
// Las consultas SQL están en peluqueria.sql.
// Aquí solamente llamamos a los procedimientos con CALL.
// =========================================================

$host = "127.0.0.1";
$usuario = "root";
$password = "";
$baseDatos = "peluqueria";
$puerto = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

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
function PA_Ejecutar($nombre, $tipos = "", $parametros = [], $devuelveFilas = false)
{
    global $connection;

    // Los nombres de procedimiento se escriben fijos en nuestras funciones PA.
    $marcadores = [];
    foreach ($parametros as $p) {
        $marcadores[] = "?";
    }
    $marcadores[] = "@resultado";

    $sql = "CALL " . $nombre . "(" . implode(", ", $marcadores) . ")";
    $stmt = $connection->prepare($sql);

    if (count($parametros) > 0) {
        // mysqli necesita referencias para bind_param.
        $referencias = [];
        foreach ($parametros as $i => &$valor) {
            $referencias[$i] = &$valor;
        }
        $stmt->bind_param($tipos, ...$referencias);
        unset($valor);
    }

    $stmt->execute();

    $datos = [];
    if ($devuelveFilas) {
        $resultado = $stmt->get_result();
        if ($resultado) {
            $datos = $resultado->fetch_all(MYSQLI_ASSOC);
            $resultado->free();
        }
    }

    // Consumir todos los resultados pendientes del CALL.
    while ($stmt->more_results()) {
        $stmt->next_result();
        $extra = $stmt->get_result();
        if ($extra) {
            $extra->free();
        }
    }
    $stmt->close();

    // Recuperar el parámetro OUT.
    $consultaRes = $connection->query("SELECT @resultado AS codigo");
    $filaRes = $consultaRes->fetch_assoc();
    $consultaRes->free();

    return [
        "codigo" => (int)($filaRes["codigo"] ?? -99),
        "datos" => $datos
    ];
}

// ------------------------- CLIENTES -------------------------

function PA_AltaCliente($nombre, $telefono) {
    return PA_Ejecutar("AltaCliente", "ss", [$nombre, $telefono]);
}

function PA_BajaCliente($id_cliente) {
    return PA_Ejecutar("BajaCliente", "i", [$id_cliente]);
}

function PA_ModificarCliente($id_cliente, $nombre, $telefono) {
    return PA_Ejecutar("ModificarCliente", "iss", [$id_cliente, $nombre, $telefono]);
}

function PA_ConsultarClientes() {
    return PA_Ejecutar("ConsultarClientes", "", [], true);
}

function PA_ConsultarClientePorID($id_cliente) {
    return PA_Ejecutar("ConsultarClientePorID", "i", [$id_cliente], true);
}

// ------------------------- PELUQUEROS -------------------------

function PA_AltaPeluquero($nombre, $especialidad) {
    return PA_Ejecutar("AltaPeluquero", "ss", [$nombre, $especialidad]);
}

function PA_BajaPeluquero($id_peluquero) {
    return PA_Ejecutar("BajaPeluquero", "i", [$id_peluquero]);
}

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

function PA_ModificarReserva($id_reserva, $id_cliente, $id_peluquero, $fecha_hora, $servicio) {
    return PA_Ejecutar(
        "ModificarReserva",
        "iiiss",
        [$id_reserva, $id_cliente, $id_peluquero, $fecha_hora, $servicio]
    );
}

function PA_ConsultarReservas() {
    return PA_Ejecutar("ConsultarReservas", "", [], true);
}

function PA_ConsultarReservaPorID($id_reserva) {
    return PA_Ejecutar("ConsultarReservaPorID", "i", [$id_reserva], true);
}

function PA_ConsultarReservasPorCliente($id_cliente) {
    return PA_Ejecutar("ConsultarReservasPorCliente", "i", [$id_cliente], true);
}

function PA_ConsultarReservasPorPeluquero($id_peluquero) {
    return PA_Ejecutar("ConsultarReservasPorPeluquero", "i", [$id_peluquero], true);
}

function PA_ConsultarReservasPorFecha($fecha) {
    return PA_Ejecutar("ConsultarReservasPorFecha", "s", [$fecha], true);
}

function PA_ConsultarReservasConJOIN() {
    return PA_Ejecutar("ConsultarReservasConJOIN", "", [], true);
}

function PA_ConsultarReservasEntreFechas($fecha_inicio, $fecha_fin) {
    return PA_Ejecutar(
        "ConsultarReservasEntreFechas",
        "ss",
        [$fecha_inicio, $fecha_fin],
        true
    );
}

function PA_ContarReservas() {
    return PA_Ejecutar("ContarReservas", "", [], true);
}

function PA_ConsultarDisponibilidadPeluquero($id_peluquero, $fecha_hora) {
    return PA_Ejecutar(
        "ConsultarDisponibilidadPeluquero",
        "is",
        [$id_peluquero, $fecha_hora],
        true
    );
}
?>
