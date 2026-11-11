<?php
require_once __DIR__ . "/AccesoDatos.php";

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}

$mensaje = "";
$codigo = null;

// Procesar acciones enviadas desde el formulario.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    try {
        if ($accion === "alta") {
            $fecha = str_replace("T", " ", $_POST["fecha_hora"] ?? "");
            $r = PA_AltaReserva(
                (int)($_POST["id_cliente"] ?? 0),
                (int)($_POST["id_peluquero"] ?? 0),
                $fecha,
                trim($_POST["servicio"] ?? "")
            );
            $codigo = $r["codigo"];
        } elseif ($accion === "baja") {
            $r = PA_BajaReserva((int)($_POST["id_reserva"] ?? 0));
            $codigo = $r["codigo"];
        } elseif ($accion === "modificar") {
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
        $mensaje = $mensajes[$codigo] ?? "No se ha podido realizar la operación.";
    } catch (Throwable $e) {
        $mensaje = "Error: " . $e->getMessage();
    }
}

$reservas = PA_ConsultarReservasConJOIN();
$clientes = PA_ConsultarClientes();
$peluqueros = PA_ConsultarPeluqueros();
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
<header><h1>Gestión de reservas</h1><nav><a href="index.html">Inicio</a> | <a href="gestionar_clientes.php">Clientes</a> | <a href="gestionar_peluqueros.php">Peluqueros</a></nav></header>
<main>
    <?php if ($mensaje !== ""): ?>
        <p class="mensaje"><?= h($mensaje) ?> (código: <?= h($codigo) ?>)</p>
    <?php endif; ?>

    <section class="panel">
        <h2>Dar de alta una reserva</h2>
        <form method="post">
            <input type="hidden" name="accion" value="alta">
            <label>Cliente
                <select name="id_cliente" required>
                    <option value="">Selecciona un cliente</option>
                    <?php foreach ($clientes["datos"] as $c): ?>
                        <option value="<?= h($c["id_cliente"]) ?>"><?= h($c["nombre"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Peluquero
                <select name="id_peluquero" required>
                    <option value="">Selecciona un peluquero</option>
                    <?php foreach ($peluqueros["datos"] as $p): ?>
                        <option value="<?= h($p["id_peluquero"]) ?>"><?= h($p["nombre"]) ?> - <?= h($p["especialidad"]) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Fecha y hora <input type="datetime-local" name="fecha_hora" required></label>
            <label>Servicio <input type="text" name="servicio" maxlength="50" required></label>
            <button type="submit">Guardar reserva</button>
        </form>
    </section>

    <section class="panel">
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
        <h2>Eliminar una reserva</h2>
        <form method="post" class="form-line">
            <input type="hidden" name="accion" value="baja">
            <label>ID de reserva <input type="number" name="id_reserva" min="1" required></label>
            <button type="submit" class="peligro">Eliminar</button>
        </form>
    </section>

    <section class="panel">
        <h2>Listado de reservas</h2>
        <table>
            <thead><tr><th>ID</th><th>Cliente</th><th>Teléfono</th><th>Peluquero</th><th>Especialidad</th><th>Fecha y hora</th><th>Servicio</th></tr></thead>
            <tbody>
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
