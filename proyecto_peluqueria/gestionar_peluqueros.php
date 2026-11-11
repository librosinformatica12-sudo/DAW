<?php
// =====================================================================
// PÁGINA: GESTIÓN DE PELUQUEROS (alta, modificar, baja y listado)
// Es idéntica en estructura a gestionar_clientes.php; cambia que en vez de
// teléfono se usa especialidad. Si dominas una, dominas la otra.
// Flujo: formulario POST -> if/elseif según accion -> PA_xxx() -> mensaje
// -> consulta del listado -> HTML con foreach.
// =====================================================================
// Carga la conexión y las funciones PA_ (AccesoDatos.php).
require_once __DIR__ . "/AccesoDatos.php";
// h() escapa el texto para mostrarlo seguro en HTML (evita XSS).
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
$mensaje = "";
// Solo se ejecuta cuando se ha enviado un formulario.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Lee la acción del input hidden (alta / modificar / baja).
    $accion = $_POST["accion"] ?? "";
    if ($accion === "alta") {
        // ALTA: nombre y especialidad. trim() quita espacios; ?? "" evita errores si falta el campo.
        $r = PA_AltaPeluquero(trim($_POST["nombre"] ?? ""), trim($_POST["especialidad"] ?? ""));
    } elseif ($accion === "modificar") {
        // MODIFICAR: id convertido a entero + nombre + especialidad.
        $r = PA_ModificarPeluquero((int)($_POST["id_peluquero"] ?? 0), trim($_POST["nombre"] ?? ""), trim($_POST["especialidad"] ?? ""));
    } else {
        // BAJA (por defecto, igual que en clientes: una acción desconocida también llegaría aquí).
        $r = PA_BajaPeluquero((int)($_POST["id_peluquero"] ?? 0));
    }
    // Códigos de los procedimientos -> mensajes (ver peluqueria.sql).
    // OJO: BajaPeluquero devuelve -4 cuando el peluquero TIENE RESERVAS, pero aquí
    // -4 está definido como "Nombre no válido". El mensaje "puede que tenga
    // reservas" de la línea siguiente solo sale con códigos que NO están en el array.
    $codigos = [0=>"Operación correcta.", -2=>"ID o nombre no válido.", -3=>"El peluquero no existe o la especialidad está vacía.", -4=>"Nombre no válido.", -5=>"Especialidad no válida."];
    // Busca el mensaje; si el código no existe usa el texto genérico con el código.
    $mensaje = $codigos[$r["codigo"]] ?? "No se pudo completar la operación. Puede que tenga reservas asociadas. Código: ".$r["codigo"];
}
// Consulta el listado ya con los cambios aplicados; se pinta en la tabla de abajo.
$peluqueros = PA_ConsultarPeluqueros();
// Fin del PHP superior; empieza el HTML.
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Peluqueros</title>
  <link rel="stylesheet" href="estilos.css">
</head>
<body>
  <header>
    <h1>Gestión de peluqueros</h1>
    <nav>
      <a href="index.html">Inicio</a> | 
      <a href="gestionar_reservas.php">Reservas</a> | 
      <a href="gestionar_clientes.php">Clientes</a>
    </nav>
  </header>

  <main>
    <!-- Muestra el aviso solo si hay mensaje (sintaxis alternativa if/endif). -->
    <?php if ($mensaje): ?>
      <p class="mensaje"><?= h($mensaje) ?></p>
    <?php endif; ?>

    <!-- Alta de peluquero -->
    <section class="panel">
      <h2>Alta de peluquero</h2>
      <form method="post">
      <!-- Formulario de ALTA. Los name de los inputs (nombre, especialidad) deben -->
      <!-- coincidir con las claves $_POST["..."] del PHP de arriba. -->
        <input type="hidden" name="accion" value="alta">
        
        <label>
          Nombre 
          <input name="nombre" maxlength="50" required>
        </label>
        
        <label>
          Especialidad 
          <!-- maxlength=50 coincide con VARCHAR(50) de la tabla peluqueros. -->
          <input name="especialidad" maxlength="50" required>
        </label>
        
        <button type="submit">Guardar</button>
      </form>
    </section>

    <!-- Modificar peluquero -->
    <section class="panel">
      <h2>Modificar peluquero</h2>
      <form method="post">
        <input type="hidden" name="accion" value="modificar">
        
        <label>
          ID 
          <!-- El id identifica al peluquero que se modifica o elimina. -->
          <input type="number" name="id_peluquero" min="1" required>
        </label>
        
        <label>
          Nombre 
          <input name="nombre" maxlength="50" required>
        </label>
        
        <label>
          Especialidad 
          <input name="especialidad" maxlength="50" required>
        </label>
        
        <button type="submit">Modificar</button>
      </form>
    </section>

    <!-- Eliminar peluquero -->
    <section class="panel">
      <h2>Eliminar peluquero</h2>
      <form method="post" class="form-line">
      <!-- Formulario de BAJA: solo necesita el id. -->
        <input type="hidden" name="accion" value="baja">
        
        <label>
          ID 
          <input type="number" name="id_peluquero" min="1" required>
        </label>
        
        <button type="submit" class="peligro">Eliminar</button>
      </form>
    </section>

    <!-- Listado de peluqueros -->
    <section class="panel">
      <h2>Listado de peluqueros</h2>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Especialidad</th>
          </tr>
        </thead>
        <tbody>
          <!-- Recorre las filas devueltas por PA_ConsultarPeluqueros(). Las claves -->
          <!-- (id_peluquero, nombre, especialidad) son las columnas de la tabla. -->
          <?php foreach ($peluqueros["datos"] as $p): ?>
            <tr>
              <td><?= h($p["id_peluquero"]) ?></td>
              <td><?= h($p["nombre"]) ?></td>
              <td><?= h($p["especialidad"]) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>