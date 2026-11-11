<?php
require_once __DIR__ . "/AccesoDatos.php";
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";
    if ($accion === "alta") {
        $r = PA_AltaCliente(trim($_POST["nombre"] ?? ""), trim($_POST["telefono"] ?? ""));
    } elseif ($accion === "modificar") {
        $r = PA_ModificarCliente((int)($_POST["id_cliente"] ?? 0), trim($_POST["nombre"] ?? ""), trim($_POST["telefono"] ?? ""));
    } else {
        $r = PA_BajaCliente((int)($_POST["id_cliente"] ?? 0));
    }
    $codigos = [0=>"Operación correcta.", -2=>"ID o nombre no válido.", -3=>"Teléfono vacío o cliente inexistente.", -4=>"Nombre no válido.", -5=>"Teléfono no válido.", -4=>"No se puede borrar: el cliente tiene reservas."];
    $mensaje = $codigos[$r["codigo"]] ?? "No se pudo completar la operación. Código: ".$r["codigo"];
}
$clientes = PA_ConsultarClientes();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Clientes</title><link rel="stylesheet" href="estilos.css"></head>
<body><header><h1>Gestión de clientes</h1><nav><a href="index.html">Inicio</a> | <a href="gestionar_reservas.php">Reservas</a> | <a href="gestionar_peluqueros.php">Peluqueros</a></nav></header><main>
<?php if ($mensaje): ?><p class="mensaje"><?= h($mensaje) ?></p><?php endif; ?>
<section class="panel"><h2>Alta de cliente</h2><form method="post"><input type="hidden" name="accion" value="alta"><label>Nombre <input name="nombre" maxlength="50" required></label><label>Teléfono <input name="telefono" maxlength="15" required></label><button>Guardar</button></form></section>
<section class="panel"><h2>Modificar cliente</h2><form method="post"><input type="hidden" name="accion" value="modificar"><label>ID <input type="number" name="id_cliente" min="1" required></label><label>Nombre <input name="nombre" maxlength="50" required></label><label>Teléfono <input name="telefono" maxlength="15" required></label><button>Modificar</button></form></section>
<section class="panel"><h2>Eliminar cliente</h2><form method="post" class="form-line"><input type="hidden" name="accion" value="baja"><label>ID <input type="number" name="id_cliente" min="1" required></label><button class="peligro">Eliminar</button></form></section>
<section class="panel"><h2>Listado de clientes</h2><table><tr><th>ID</th><th>Nombre</th><th>Teléfono</th></tr><?php foreach($clientes["datos"] as $c): ?><tr><td><?=h($c["id_cliente"])?></td><td><?=h($c["nombre"])?></td><td><?=h($c["telefono"])?></td></tr><?php endforeach; ?></table></section>
</main></body></html>
