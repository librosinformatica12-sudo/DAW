<?php
// =====================================================================
// PÁGINA: GESTIÓN DE CLIENTES (alta, modificar, baja y listado)
// Estructura típica de TODAS las páginas gestionar_*.php:
//   1) PHP arriba: si la petición es POST, ejecuta la acción elegida y guarda
//      un mensaje con el resultado.
//   2) PHP: consulta los datos actuales para pintarlos.
//   3) HTML abajo: mensaje, formularios y tabla con el listado.
// La página envía los datos a SÍ MISMA (el <form> no tiene action).
// El campo oculto name="accion" indica qué botón se pulsó (alta/modificar/baja).
//
// [EXAMEN] Puede faltar: la rama del if de una acción, una llamada PA_,
// algún name="..." de un input (debe coincidir con la clave de $_POST),
// el foreach de la tabla o una columna nueva de la tabla.
// =====================================================================
// Incluye AccesoDatos.php (conexión + funciones PA_). require_once = si falla
// se detiene el script, y no se incluye dos veces. __DIR__ = carpeta actual.
require_once __DIR__ . "/AccesoDatos.php";
// h() = escapar texto antes de mostrarlo en HTML (protege de XSS, es decir,
// de que alguien escriba <script> en un campo). Úsala SIEMPRE al imprimir datos.
// ENT_QUOTES escapa también comillas simples y dobles.
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, "UTF-8"); }
// $mensaje guardará el texto que se mostrará al usuario tras una acción.
// Si sigue vacío, el HTML no pinta el aviso (ver el if más abajo).
$mensaje = "";
// Solo se procesa si se envió un formulario (método POST). Al entrar por
// primera vez a la página es GET y este bloque se salta.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // $_POST["accion"] viene del input hidden de cada formulario.
    // ?? "" (operador de fusión de null) = si no existe, usa cadena vacía.
    $accion = $_POST["accion"] ?? "";
    // Según la acción se llama a la función PA_ correspondiente.
    // Cada una devuelve un array: $r["codigo"] (0 = correcto, negativo = error).
    if ($accion === "alta") {
        // ALTA: trim() quita espacios sobrantes. Las claves de $_POST ("nombre",
        // "telefono") son los atributos name= de los inputs del formulario de alta.
        $r = PA_AltaCliente(trim($_POST["nombre"] ?? ""), trim($_POST["telefono"] ?? ""));
    } elseif ($accion === "modificar") {
        // MODIFICAR: (int) convierte el id a número entero (evita texto raro).
        $r = PA_ModificarCliente((int)($_POST["id_cliente"] ?? 0), trim($_POST["nombre"] ?? ""), trim($_POST["telefono"] ?? ""));
    } else {
        // BAJA: cualquier acción que no sea alta ni modificar cae aquí.
        // OJO: así una acción desconocida también borraría; más seguro sería
        // comprobar elseif ($accion === "baja") de forma explícita.
        $r = PA_BajaCliente((int)($_POST["id_cliente"] ?? 0));
    }
    // TABLA DE CÓDIGOS -> MENSAJES. Los números son los valores de _res de los
    // procedimientos (ver peluqueria.sql). Si el código no está en el array,
    // se usa el mensaje genérico de la línea siguiente.
    // BUG A VIGILAR: la clave -4 está DUPLICADA. En PHP la última sobrescribe, así
    // que -4 siempre dice "No se puede borrar..." aunque en ModificarCliente
    // -4 signifique "nombre vacío". Además, los mismos números significan cosas
    // distintas según el procedimiento (alta, modificar, baja).
    $codigos = [0=>"Operación correcta.", -2=>"ID o nombre no válido.", -3=>"Teléfono vacío o cliente inexistente.", -4=>"Nombre no válido.", -5=>"Teléfono no válido.", -4=>"No se puede borrar: el cliente tiene reservas."];
    // Busca el mensaje por su código. ?? = valor por defecto si la clave no existe.
    // . es el operador de concatenación de texto en PHP.
    $mensaje = $codigos[$r["codigo"]] ?? "No se pudo completar la operación. Código: ".$r["codigo"];
}
// Se consulta DESPUÉS de procesar el POST para que el listado ya incluya el
// cambio recién hecho. $clientes["datos"] es el array de filas.
$clientes = PA_ConsultarClientes();
// Fin del bloque PHP de arriba. A partir de aquí empieza el HTML.
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <!-- charset UTF-8: tildes y ñ se ven bien. viewport: adapta a móviles. -->
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Clientes</title>
  <!-- Enlaza la hoja de estilos externa (estilos.css, misma carpeta). -->
  <link rel="stylesheet" href="estilos.css">
</head>
<body>
  <header>
    <h1>Gestión de clientes</h1>
    <!-- Menú de navegación: enlaces a las otras páginas. -->
    <nav>
      <a href="index.html">Inicio</a> | 
      <a href="gestionar_reservas.php">Reservas</a> | 
      <a href="gestionar_peluqueros.php">Peluqueros</a>
    </nav>
  </header>

  <main>
    <!-- Sintaxis alternativa de PHP en HTML: if ... endif. Muestra el aviso solo si -->
    <!-- hay mensaje. La etiqueta corta de salida (signo menor, interrogación, igual) equivale a echo. -->
    <?php if ($mensaje): ?>
      <p class="mensaje"><?= h($mensaje) ?></p>
    <?php endif; ?>

    <!-- Alta de cliente -->
    <section class="panel">
      <h2>Alta de cliente</h2>
      <form method="post">
      <!-- ALTA - method=post: los datos viajan en el cuerpo y llegan en $_POST. -->
      <!-- Sin atributo action, el formulario se envía a esta misma página. -->
        <input type="hidden" name="accion" value="alta">
        <!-- Campo oculto: dice al PHP qué acción hay que ejecutar. -->
        
        <label>
          Nombre 
          <!-- name="nombre" -> será $_POST["nombre"]. maxlength limita los caracteres -->
          <!-- (igual que VARCHAR(50) en la BD). required impide enviar el campo vacío. -->
          <input name="nombre" maxlength="50" required>
        </label>
        
        <label>
          Teléfono 
          <input name="telefono" maxlength="15" required>
        </label>
        
        <button type="submit">Guardar</button>
      </form>
    </section>

    <!-- Modificar cliente -->
    <section class="panel">
      <h2>Modificar cliente</h2>
      <form method="post">
        <input type="hidden" name="accion" value="modificar">
        
        <label>
          ID 
          <!-- type=number solo admite números; min=1 evita ids menores que 1. -->
          <input type="number" name="id_cliente" min="1" required>
        </label>
        
        <label>
          Nombre 
          <input name="nombre" maxlength="50" required>
        </label>
        
        <label>
          Teléfono 
          <input name="telefono" maxlength="15" required>
        </label>
        
        <button type="submit">Modificar</button>
      </form>
    </section>

    <!-- Eliminar cliente -->
    <section class="panel">
      <h2>Eliminar cliente</h2>
      <form method="post" class="form-line">
      <!-- BAJA - mismo patrón; el botón usa la clase "peligro" (rojo en el CSS). -->
        <input type="hidden" name="accion" value="baja">
        
        <label>
          ID 
          <input type="number" name="id_cliente" min="1" required>
        </label>
        
        <button type="submit" class="peligro">Eliminar</button>
      </form>
    </section>

    <!-- Listado de clientes -->
    <section class="panel">
      <h2>Listado de clientes</h2>
      <!-- LISTADO: tabla con cabecera (thead) y cuerpo (tbody). -->
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Teléfono</th>
          </tr>
        </thead>
        <tbody>
          <!-- Un <tr> por cada fila devuelta por la BD. Las claves $c["id_cliente"], etc. -->
          <!-- son los nombres de columna de la tabla clientes. -->
          <!-- [EXAMEN] Si añaden una columna (p. ej. email): añadirla en el CREATE TABLE, -->
          <!-- en los procedimientos, en las funciones PA_, en un <th> y en un <td> aquí. -->
          <?php foreach ($clientes["datos"] as $c): ?>
            <tr>
              <td><?= h($c["id_cliente"]) ?></td>
              <td><?= h($c["nombre"]) ?></td>
              <td><?= h($c["telefono"]) ?></td>
            </tr>
          <!-- Cierre del bucle foreach (sintaxis alternativa con dos puntos). -->
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>