<?php
require_once __DIR__ . "/AccesoDatos.php";

$seccion = $_GET["seccion"] ?? "reservas";
$mensaje = "";
$tipoMensaje = "ok";

function h($valor) {
    return htmlspecialchars((string)$valor, ENT_QUOTES, "UTF-8");
}
function ejecutarAccion($resultado, $correcto, $error = "No se pudo realizar la operación.") {
    global $mensaje, $tipoMensaje;
    $codigo = $resultado["codigo"] ?? -99;
    if ($codigo === 0) {
        $mensaje = $correcto;
        $tipoMensaje = "ok";
    } else {
        $mensaje = $error . " Código devuelto: " . $codigo;
        $tipoMensaje = "error";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";
    try {
        switch ($accion) {
            case "alta_cliente":
                ejecutarAccion(PA_AltaCliente(trim($_POST["nombre"]), trim($_POST["telefono"])), "Cliente añadido correctamente.");
                break;
            case "baja_cliente":
                ejecutarAccion(PA_BajaCliente((int)$_POST["id_cliente"]), "Cliente eliminado correctamente.");
                break;
            case "modificar_cliente":
                ejecutarAccion(PA_ModificarCliente((int)$_POST["id_cliente"], trim($_POST["nombre"]), trim($_POST["telefono"])), "Cliente modificado correctamente.");
                break;
            case "alta_peluquero":
                ejecutarAccion(PA_AltaPeluquero(trim($_POST["nombre"]), trim($_POST["especialidad"])), "Peluquero añadido correctamente.");
                break;
            case "baja_peluquero":
                ejecutarAccion(PA_BajaPeluquero((int)$_POST["id_peluquero"]), "Peluquero eliminado correctamente.");
                break;
            case "modificar_peluquero":
                ejecutarAccion(PA_ModificarPeluquero((int)$_POST["id_peluquero"], trim($_POST["nombre"]), trim($_POST["especialidad"])), "Peluquero modificado correctamente.");
                break;
            case "alta_reserva":
                ejecutarAccion(PA_AltaReserva((int)$_POST["id_cliente"], (int)$_POST["id_peluquero"], $_POST["fecha_hora"], trim($_POST["servicio"])), "Reserva creada correctamente.");
                break;
            case "baja_reserva":
                ejecutarAccion(PA_BajaReserva((int)$_POST["id_reserva"]), "Reserva eliminada correctamente.");
                break;
            case "modificar_reserva":
                ejecutarAccion(PA_ModificarReserva((int)$_POST["id_reserva"], (int)$_POST["id_cliente"], (int)$_POST["id_peluquero"], $_POST["fecha_hora"], trim($_POST["servicio"])), "Reserva modificada correctamente.");
                break;
        }
    } catch (Throwable $e) {
        $mensaje = "Error al procesar la operación: " . $e->getMessage();
        $tipoMensaje = "error";
    }
}

function filas($resultado) {
    return $resultado["datos"] ?? [];
}
function tabla($datos) {
    if (!$datos) { echo "<p>No hay registros para mostrar.</p>"; return; }
    echo "<div class='tabla-wrap'><table><thead><tr>";
    foreach (array_keys($datos[0]) as $campo) echo "<th>" . h($campo) . "</th>";
    echo "</tr></thead><tbody>";
    foreach ($datos as $fila) {
        echo "<tr>";
        foreach ($fila as $valor) echo "<td>" . h($valor) . "</td>";
        echo "</tr>";
    }
    echo "</tbody></table></div>";
}
$clientes = filas(PA_ConsultarClientes());
$peluqueros = filas(PA_ConsultarPeluqueros());
$reservas = filas(PA_ConsultarReservasConJOIN());
$titulo = [
    "clientes" => "Gestión de clientes",
    "peluqueros" => "Gestión de peluqueros",
    "reservas" => "Gestión de reservas",
    "consultas" => "Consultas y búsquedas"
][$seccion] ?? "Gestión de reservas";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($titulo) ?></title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f5f2f0;color:#292323;font-family:Arial,sans-serif}
header{background:#563b4a;color:#fff;padding:20px;text-align:center}nav{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:12px}
nav a{color:white;text-decoration:none;padding:8px 12px;border:1px solid #ffffff66;border-radius:7px}
main{max-width:1100px;margin:25px auto;padding:0 16px}.panel{background:#fff;border-radius:11px;padding:20px;margin-bottom:18px;box-shadow:0 2px 10px #0000000d}
h2{color:#563b4a;margin-top:0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:14px}
label{display:block;font-weight:bold;margin:8px 0 5px}input,select{width:100%;padding:10px;border:1px solid #cfc4ca;border-radius:6px}
button{background:#80566b;color:#fff;border:0;padding:11px 16px;border-radius:7px;margin-top:12px;cursor:pointer}button:hover{background:#654255}
.msg{padding:12px;border-radius:7px;margin-bottom:16px}.ok{background:#e4f4e8;color:#205b2d}.error{background:#fde8e8;color:#8b2222}
.tabla-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse}th,td{padding:9px;border-bottom:1px solid #e8e0e4;text-align:left}th{background:#f0e9ed}
a.volver{color:#80566b}footer{text-align:center;padding:20px;color:#766b70}
</style>
</head>
<body>
<header>
<h1><?= h($titulo) ?></h1>
<nav>
<a href="index.html">Inicio</a>
<a href="?seccion=clientes">Clientes</a>
<a href="?seccion=peluqueros">Peluqueros</a>
<a href="?seccion=reservas">Reservas</a>
<a href="?seccion=consultas">Consultas</a>
</nav>
</header>
<main>
<?php if ($mensaje !== ""): ?><div class="msg <?= h($tipoMensaje) ?>"><?= h($mensaje) ?></div><?php endif; ?>

<?php if ($seccion === "clientes"): ?>
<section class="panel"><h2>Alta de cliente</h2>
<form method="post"><input type="hidden" name="accion" value="alta_cliente"><div class="grid">
<div><label>Nombre</label><input name="nombre" maxlength="50" required></div>
<div><label>Teléfono</label><input name="telefono" maxlength="15" required></div></div><button>Añadir cliente</button></form></section>
<section class="panel"><h2>Modificar cliente</h2>
<form method="post"><input type="hidden" name="accion" value="modificar_cliente"><div class="grid">
<div><label>ID del cliente</label><input type="number" name="id_cliente" min="1" required></div>
<div><label>Nuevo nombre</label><input name="nombre" maxlength="50" required></div>
<div><label>Nuevo teléfono</label><input name="telefono" maxlength="15" required></div></div><button>Modificar cliente</button></form></section>
<section class="panel"><h2>Dar de baja cliente</h2>
<form method="post"><input type="hidden" name="accion" value="baja_cliente"><label>ID del cliente</label><input type="number" name="id_cliente" min="1" required><button>Eliminar cliente</button></form></section>
<section class="panel"><h2>Listado de clientes</h2><?php tabla($clientes); ?></section>

<?php elseif ($seccion === "peluqueros"): ?>
<section class="panel"><h2>Alta de peluquero</h2>
<form method="post"><input type="hidden" name="accion" value="alta_peluquero"><div class="grid">
<div><label>Nombre</label><input name="nombre" maxlength="50" required></div>
<div><label>Especialidad</label><input name="especialidad" maxlength="50" required></div></div><button>Añadir peluquero</button></form></section>
<section class="panel"><h2>Modificar peluquero</h2>
<form method="post"><input type="hidden" name="accion" value="modificar_peluquero"><div class="grid">
<div><label>ID del peluquero</label><input type="number" name="id_peluquero" min="1" required></div>
<div><label>Nuevo nombre</label><input name="nombre" maxlength="50" required></div>
<div><label>Nueva especialidad</label><input name="especialidad" maxlength="50" required></div></div><button>Modificar peluquero</button></form></section>
<section class="panel"><h2>Dar de baja peluquero</h2>
<form method="post"><input type="hidden" name="accion" value="baja_peluquero"><label>ID del peluquero</label><input type="number" name="id_peluquero" min="1" required><button>Eliminar peluquero</button></form></section>
<section class="panel"><h2>Listado de peluqueros</h2><?php tabla($peluqueros); ?></section>

<?php elseif ($seccion === "reservas"): ?>
<section class="panel"><h2>Crear reserva</h2>
<form method="post"><input type="hidden" name="accion" value="alta_reserva"><div class="grid">
<div><label>Cliente</label><select name="id_cliente" required><option value="">Selecciona</option><?php foreach($clientes as $c): ?><option value="<?= (int)$c["id_cliente"] ?>"><?= h($c["nombre"]) ?> (ID <?= (int)$c["id_cliente"] ?>)</option><?php endforeach; ?></select></div>
<div><label>Peluquero</label><select name="id_peluquero" required><option value="">Selecciona</option><?php foreach($peluqueros as $p): ?><option value="<?= (int)$p["id_peluquero"] ?>"><?= h($p["nombre"]) ?> (ID <?= (int)$p["id_peluquero"] ?>)</option><?php endforeach; ?></select></div>
<div><label>Fecha y hora</label><input type="datetime-local" name="fecha_hora" required></div>
<div><label>Servicio</label><input name="servicio" maxlength="50" placeholder="Corte, tinte..." required></div></div><button>Crear reserva</button></form></section>
<section class="panel"><h2>Modificar reserva</h2>
<form method="post"><input type="hidden" name="accion" value="modificar_reserva"><div class="grid">
<div><label>ID de reserva</label><input type="number" name="id_reserva" min="1" required></div>
<div><label>ID de cliente</label><input type="number" name="id_cliente" min="1" required></div>
<div><label>ID de peluquero</label><input type="number" name="id_peluquero" min="1" required></div>
<div><label>Fecha y hora</label><input type="datetime-local" name="fecha_hora" required></div>
<div><label>Servicio</label><input name="servicio" maxlength="50" required></div></div><button>Modificar reserva</button></form></section>
<section class="panel"><h2>Dar de baja reserva</h2>
<form method="post"><input type="hidden" name="accion" value="baja_reserva"><label>ID de reserva</label><input type="number" name="id_reserva" min="1" required><button>Eliminar reserva</button></form></section>
<section class="panel"><h2>Reservas registradas</h2><?php tabla($reservas); ?></section>

<?php else: ?>
<section class="panel"><h2>Reservas con datos relacionados (INNER JOIN)</h2><?php tabla($reservas); ?></section>
<section class="panel"><h2>Buscar reservas por fecha</h2>
<form method="get"><input type="hidden" name="seccion" value="consultas"><label>Fecha</label><input type="date" name="fecha" required><button>Buscar</button></form>
<?php if (!empty($_GET["fecha"])): ?><h3>Resultados del <?= h($_GET["fecha"]) ?></h3><?php tabla(filas(PA_ConsultarReservasPorFecha($_GET["fecha"]))); ?><?php endif; ?></section>
<section class="panel"><h2>Reservas entre fechas</h2>
<form method="get"><input type="hidden" name="seccion" value="consultas"><div class="grid">
<div><label>Desde</label><input type="datetime-local" name="inicio" required></div>
<div><label>Hasta</label><input type="datetime-local" name="fin" required></div></div><button>Consultar intervalo</button></form>
<?php if (!empty($_GET["inicio"]) && !empty($_GET["fin"])): ?><?php tabla(filas(PA_ConsultarReservasEntreFechas($_GET["inicio"], $_GET["fin"]))); ?><?php endif; ?></section>
<section class="panel"><h2>Número total de reservas</h2><?php $total=filas(PA_ContarReservas()); ?><p>Total: <strong><?= h($total[0]["total"] ?? "0") ?></strong></p></section>
<?php endif; ?>
<p><a class="volver" href="index.html">← Volver al inicio</a></p>
</main>
<footer>Proyecto de práctica DAW · PHP + MySQL</footer>
</body>
</html>
