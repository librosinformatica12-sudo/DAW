<?php
// =====================================================================
// PÁGINA: GESTIÓN DE RESERVAS (alta, modificar, baja y listado con JOIN)
// Es la página más completa. Diferencias respecto a clientes/peluqueros:
//   - Usa try/catch para capturar errores de la BD (p. ej. clave ajena).
//   - Los campos cliente y peluquero son <select> rellenados desde la BD.
//   - La fecha viene de un input datetime-local y hay que adaptarla a MySQL.
//   - El listado usa PA_ConsultarReservasConJOIN (une 3 tablas).
// [EXAMEN] Típico: completar el <select>, el foreach de la tabla, el
// str_replace de la fecha o la llamada PA_ de alguna rama.
// =====================================================================
require_once __DIR__ . "/AccesoDatos.php";

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
// $codigo guardará el número devuelto por el procedimiento (0 = correcto).
// Empieza en null porque aún no se ha ejecutado nada.
$codigo = null;

// Procesar acciones enviadas desde el formulario.
// Procesar acciones enviadas desde los formularios (solo si es POST).
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    // try/catch: con mysqli en modo estricto, un error de BD lanza una excepción.
    // Si ocurre, el catch del final la captura y muestra "Error: ...".
    try {
        if ($accion === "alta") {
            // <input type="datetime-local"> envía "2026-11-15T10:00" (con una T).
            // MySQL espera "2026-11-15 10:00" (con espacio), así que se cambia la T.
            $fecha = str_replace("T", " ", $_POST["fecha_hora"] ?? "");
            // ALTA: orden de los argumentos = id_cliente, id_peluquero, fecha, servicio
            // (el mismo orden que en PA_AltaReserva y en el procedimiento).
            // (int) fuerza número en los ids; trim() limpia espacios del servicio.
            $r = PA_AltaReserva(
                (int)($_POST["id_cliente"] ?? 0),
                (int)($_POST["id_peluquero"] ?? 0),
                $fecha,
                trim($_POST["servicio"] ?? "")
            );
            $codigo = $r["codigo"];
        } elseif ($accion === "baja") {
            // BAJA: solo hace falta el id de la reserva.
            $r = PA_BajaReserva((int)($_POST["id_reserva"] ?? 0));
            $codigo = $r["codigo"];
        } elseif ($accion === "modificar") {
            // MODIFICAR: igual que el alta pero con el id_reserva al principio.
            $fecha = str_replace("T", " ", $_POST["fecha_hora"] ?? "");
            $r = PA_ModificarReserva(
                (int)($_POST["id_reserva"] ?? 0),
                (int)($_POST["id_cliente"] ?? 0),
                (int)($_POST["id_peluquero"] ?? 0),
                $fecha,
                trim($_POST["servicio"] ?? "")
            );
            $codigo = $r["codigo"];
        }

        // Códigos de _res -> mensaje. Aquí cada número se reutiliza entre alta y
        // modificar (ver AltaReserva y ModificarReserva en peluqueria.sql).
        // -8 es el control de disponibilidad: ese peluquero ya tiene reserva a esa hora.
        $mensajes = [
            0 => "Operación realizada correctamente.",
            -2 => "Identificador de reserva o cliente no válido.",
            -3 => "La reserva no existe o el cliente no está registrado.",
            -4 => "El peluquero o el cliente no es válido.",
            -5 => "El peluquero no está registrado.",
            -6 => "La fecha no es válida.",
            -7 => "El servicio no puede estar vacío.",
            -8 => "El peluquero ya tiene una reserva en esa fecha y hora."
        ];
        // Busca el mensaje por código; si no está, usa el texto genérico.
        $mensaje = $mensajes[$codigo] ?? "No se ha podido realizar la operación.";
    // Throwable captura cualquier error o excepción (también errores de PHP).
    } catch (Throwable $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}

// Se cargan los tres listados DESPUÉS del POST para que reflejen los cambios:
//   $reservas   -> tabla del final (con JOIN, ya trae nombres en vez de ids)
//   $clientes   -> para rellenar el <select> de clientes
//   $peluqueros -> para rellenar el <select> de peluqueros
$reservas = PA_ConsultarReservasConJOIN();
$clientes = PA_ConsultarClientes();
$peluqueros = PA_ConsultarPeluqueros();
// Fin del bloque PHP. Sigue el HTML.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de reservas</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<!-- Cabecera con el título y el menú de navegación. -->
<header><h1>Gestión de reservas</h1><nav><a href="index.html">Inicio</a> | <a href="gestionar_clientes.php">Clientes</a> | <a href="gestionar_peluqueros.php">Peluqueros</a></nav></header>
<main>
    <!-- Muestra el aviso con el mensaje y el código entre paréntesis (útil para depurar). -->
    <?php if ($mensaje !== ""): ?>
        <p class="mensaje"><?= h($mensaje) ?> (código: <?= h($codigo) ?>)</p>
    <?php endif; ?>

    <section class="panel">
        <!-- FORMULARIO DE ALTA. Los name (id_cliente, id_peluquero, fecha_hora, servicio) -->
        <!-- son las claves que lee el PHP en $_POST. -->
        <h2>Dar de alta una reserva</h2>
        <form method="post">
            <input type="hidden" name="accion" value="alta">
            <label>Cliente
                <!-- <select> con las opciones generadas desde la BD. value = el id (lo que se -->
                <!-- envía) y el texto visible = el nombre. -->
                <select name="id_cliente" required>
                    <!-- Opción vacía por defecto: con required obliga a elegir una real. -->
                    <option value="">Selecciona un cliente</option>
                    <!-- Bucle: una <option> por cada cliente. value=id, texto=nombre. -->
                    <?php foreach ($clientes["datos"] as $c): ?>
                        <option value="<?= h($c["id_cliente"]) ?>"><?= h($c["nombre"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Peluquero
                <select name="id_peluquero" required>
                    <option value="">Selecciona un peluquero</option>
                    <!-- Igual con los peluqueros: muestra "nombre - especialidad". -->
                    <?php foreach ($peluqueros["datos"] as $p): ?>
                        <option value="<?= h($p["id_peluquero"]) ?>"><?= h($p["nombre"]) ?> - <?= h($p["especialidad"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <!-- datetime-local: selector de fecha y hora del navegador. -->
            <label>Fecha y hora <input type="datetime-local" name="fecha_hora" required></label>
            <label>Servicio <input type="text" name="servicio" maxlength="50" required></label>
            <button type="submit">Guardar reserva</button>
        </form>
    </section>

    <section class="panel">
        <!-- FORMULARIO DE MODIFICAR: pide el id de la reserva y todos los datos nuevos. -->
        <!-- OJO: aquí los <select> no tienen opción vacía, así que si no se toca, se -->
        <!-- enviaría el primer cliente/peluquero de la lista. -->
        <h2>Modificar una reserva</h2>
        <form method="post">
            <input type="hidden" name="accion" value="modificar">
            <label>ID de reserva <input type="number" name="id_reserva" min="1" required></label>
            <label>Cliente
                <select name="id_cliente" required>
                    <?php foreach ($clientes["datos"] as $c): ?>
                        <option value="<?= h($c["id_cliente"]) ?>"><?= h($c["nombre"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Peluquero
                <select name="id_peluquero" required>
                    <?php foreach ($peluqueros["datos"] as $p): ?>
                        <option value="<?= h($p["id_peluquero"]) ?>"><?= h($p["nombre"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Fecha y hora <input type="datetime-local" name="fecha_hora" required></label>
            <label>Servicio <input type="text" name="servicio" maxlength="50" required></label>
            <button type="submit">Modificar reserva</button>
        </form>
    </section>

    <section class="panel">
        <!-- FORMULARIO DE BAJA: solo el id de la reserva. -->
        <h2>Eliminar una reserva</h2>
        <form method="post" class="form-line">
            <input type="hidden" name="accion" value="baja">
            <label>ID de reserva <input type="number" name="id_reserva" min="1" required></label>
            <button type="submit" class="peligro">Eliminar</button>
        </form>
    </section>

    <section class="panel">
        <!-- LISTADO. Las claves de $fila (cliente, telefono, peluquero, especialidad...) -->
        <!-- son los ALIAS del SELECT de ConsultarReservasConJOIN en peluqueria.sql. -->
        <!-- [EXAMEN] Para añadir una columna: añadirla al SELECT del procedimiento, -->
        <!-- luego un <th> en la cabecera y un <td> en el bucle. -->
        <h2>Listado de reservas</h2>
        <table>
            <thead><tr><th>ID</th><th>Cliente</th><th>Teléfono</th><th>Peluquero</th><th>Especialidad</th><th>Fecha y hora</th><th>Servicio</th></tr></thead>
            <tbody>
            <!-- Una fila de la tabla por cada reserva devuelta. -->
            <?php foreach ($reservas["datos"] as $fila): ?>
                <tr>
                    <td><?= h($fila["id_reserva"]) ?></td>
                    <td><?= h($fila["cliente"]) ?></td>
                    <td><?= h($fila["telefono"]) ?></td>
                    <td><?= h($fila["peluquero"]) ?></td>
                    <td><?= h($fila["especialidad"]) ?></td>
                    <td><?= h($fila["fecha_hora"]) ?></td>
                    <td><?= h($fila["servicio"]) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
