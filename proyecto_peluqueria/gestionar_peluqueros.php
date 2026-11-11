<?php
require_once __DIR__ . "/AccesoDatos.php";
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
$mensaje = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";
    if ($accion === "alta") {
        $r = PA_AltaPeluquero(trim($_POST["nombre"] ?? ""), trim($_POST["especialidad"] ?? ""));
    } elseif ($accion === "modificar") {
        $r = PA_ModificarPeluquero((int)($_POST["id_peluquero"] ?? 0), trim($_POST["nombre"] ?? ""), trim($_POST["especialidad"] ?? ""));
    } else {
        $r = PA_BajaPeluquero((int)($_POST["id_peluquero"] ?? 0));
    }
    $codigos = [0=>"Operación correcta.", -2=>"ID o nombre no válido.", -3=>"El peluquero no existe o la especialidad está vacía.", -4=>"Nombre no válido.", -5=>"Especialidad no válida."];
    $mensaje = $codigos[$r["codigo"]] ?? "No se pudo completar la operación. Puede que tenga reservas asociadas. Código: ".$r["codigo"];
}
$peluqueros = PA_ConsultarPeluqueros();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Peluqueros</title><link rel="stylesheet" href="estilos.css"></head>
<body><header><h1>Gestión de peluqueros</h1><nav><a href="index.html">Inicio</a> | <a href="gestionar_reservas.php">Reservas</a> | <a href="gestionar_clientes.php">Clientes</a></nav></header><main>
<?php if ($mensaje): ?><p class="mensaje"><?= h($mensaje) ?></p><?php endif; ?>
<section class="panel"><h2>Alta de peluquero</h2><form method="post"><input type="hidden" name="accion" value="alta"><label>Nombre <input name="nombre" maxlength="50" required></label><label>Especialidad <input name="especialidad" maxlength="50" required></label><button>Guardar</button></form></section>
<section class="panel"><h2>Modificar peluquero</h2><form method="post"><input type="hidden" name="accion" value="modificar"><label>ID <input type="number" name="id_peluquero" min="1" required></label><label>Nombre <input name="nombre" maxlength="50" required></label><label>Especialidad <input name="especialidad" maxlength="50" required></label><button>Modificar</button></form></section>
<section class="panel"><h2>Eliminar peluquero</h2><form method="post" class="form-line"><input type="hidden" name="accion" value="baja"><label>ID <input type="number" name="id_peluquero" min="1" required></label><button class="peligro">Eliminar</button></form></section>
<section class="panel"><h2>Listado de peluqueros</h2><table><tr><th>ID</th><th>Nombre</th><th>Especialidad</th></tr><?php foreach($peluqueros["datos"] as $p): ?><tr><td><?=h($p["id_peluquero"])?></td><td><?=h($p["nombre"])?></td><td><?=h($p["especialidad"])?></td></tr><?php endforeach; ?></table></section>
</main></body></html>
