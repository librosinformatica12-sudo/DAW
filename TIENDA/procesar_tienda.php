<?php
// =====================================================================
//  procesar_tienda.php
//  Recibe los datos de los formularios de index.html, los manda a la base
//  de datos (usando las funciones de AccesoDatos.php), prepara los mensajes
//  de éxito o error y muestra la tabla de productos.
// =====================================================================

// Incluimos el archivo que contiene la conexión y las funciones de acceso a datos.
// require_once: si el archivo no existe da error, y evita cargarlo dos veces.
// __DIR__ es la carpeta donde está este archivo.
require_once __DIR__ . '/AccesoDatos.php';

// PASO A: Iniciar o reanudar la sesión (OBLIGATORIO al principio, antes de escribir nada en pantalla).
// La sesión permite guardar datos en el servidor que se mantienen entre una página y otra.
session_start();

// Variables para los mensajes que se mostrarán en pantalla.
$mensaje = "";   // Mensaje de éxito (se mostrará en verde)
$error = "";     // Mensaje de error (se mostrará en rojo)

// Inicializar la lista en sesión si todavía no existe.
// isset() comprueba si una variable existe y tiene valor.
if (!isset($_SESSION['lista_productos'])) {
    $_SESSION['lista_productos'] = [];   // Empezamos con un array vacío
}


// ---------------------------------------------------------------------
// PASO B: Si se pulsa el botón "Limpiar Lista de la Sesión"
// ---------------------------------------------------------------------
// $_POST contiene los datos enviados por el formulario. Comprobamos que la acción sea "borrar_todas".
// El operador === compara valor Y tipo (más estricto que ==).
if(isset($_POST['accion']) && $_POST['accion'] === 'borrar_todas') {
    unset($_SESSION['lista_productos']);       // Eliminamos la lista de la sesión
    $_SESSION['lista_productos'] = [];         // La dejamos como array vacío
    $mensaje = "La lista de productos ha sido limpiada de la sesión.";
}


// ---------------------------------------------------------------------
// PASO C: Si se envía el formulario para registrar un nuevo producto (Alta)
// ---------------------------------------------------------------------
if(isset($_POST['accion']) && $_POST['accion'] === 'registrar_producto') {

    // Recogemos los datos del formulario.
    // El operador ?? significa: "usa el valor de la izquierda, y si no existe, usa el de la derecha".
    $nombre_producto = $_POST['nombre_producto'] ?? '';   // Si no llega, texto vacío
    $precio = $_POST['precio'] ?? 0;                      // Si no llega, 0
    $stock = (int)($_POST['stock'] ?? 0);                 // (int) lo convierte a número entero

    // Enviamos los datos a la base de datos mediante la función PA_NuevoProducto().
    // Nos devuelve un código numérico con el resultado.
    $resultado = PA_NuevoProducto($nombre_producto, $precio, $stock);

    // Comprobamos el código devuelto por el procedimiento almacenado
    // y preparamos el mensaje correspondiente.
    if($resultado === -2){
        $error = "El nombre del producto está vacío.";
    }
    else if($resultado === -3){
        $error = "El precio no es válido o es menor de 1.";
    }
    else if($resultado === -4){
        $error = "El stock no es válido (mínimo 1 unidad).";
    }
    else if($resultado === 0){
        // 0 = todo ha ido bien
        $mensaje = "¡Producto registrado con éxito en la base de datos!";
    } else {
        // Cualquier otro código inesperado: mostramos el número para poder investigarlo.
        $error = "Error al registrar el producto. Código: " . $resultado;
    }
}


// ---------------------------------------------------------------------
// PASO D: Si se envía el formulario para comprar un producto
// ---------------------------------------------------------------------
if(isset($_POST['accion']) && $_POST['accion'] === 'comprar_producto') {

    // Recogemos el ID del producto y lo convertimos a entero.
    $id_producto = (int)($_POST['producto'] ?? 0);

    // Llamamos a la función que ejecuta el procedimiento almacenado ComprarProd.
    $res = PA_ComprarProd($id_producto);

    // Según el código devuelto, mostramos un mensaje distinto.
    if ($res === 0) {
        $mensaje = "¡Compra realizada con éxito!";
    } else if ($res === -1) {
        $error = "El ID del producto no es válido.";
    } else if ($res === -4) {
        $error = "El producto no existe.";
    } else if ($res === -5) {
        $error = "Lo sentimos, el producto está agotado.";
    } else {
        $error = "Error al comprar el producto. Código de error: " . $res;
    }
}


// ---------------------------------------------------------------------
// PASO E: Cargar y actualizar los datos de la base de datos a la sesión
// ---------------------------------------------------------------------
// Se hace SIEMPRE al final, para que la tabla refleje los cambios recién hechos
// (por ejemplo, el stock actualizado tras una compra).

// Vaciamos la lista de la sesión para volver a llenarla con datos frescos.
$_SESSION['lista_productos'] = [];

// Pedimos a la base de datos todos los productos.
$productosExistentes = PA_ListaProductos();

// is_array() comprueba que lo recibido sea un array (por seguridad).
if (is_array($productosExistentes)) {

    // Recorremos los productos uno a uno. En cada vuelta, $producto es una fila.
    foreach ($productosExistentes as $producto) {

        // Creamos un array nuevo con los datos de este producto.
        $nuevo_producto = [];
        $nuevo_producto['id_producto'] = $producto['id_producto'];
        $nuevo_producto['nombre_producto'] = $producto['nombre_producto'];
        $nuevo_producto['precio'] = $producto['precio'];
        $nuevo_producto['stock'] = $producto['stock'];

        // Lo añadimos al final de la lista guardada en la sesión ([] = añadir al final).
        $_SESSION['lista_productos'][] = $nuevo_producto;
    }
}
?>
<!-- A partir de aquí termina la parte PHP "lógica" y empieza el HTML que se ve en pantalla. -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Tienda - Procesar</title>
</head>
<body>

    <h1>Resultado de la Operación</h1>

    <!-- Mensajes de feedback para el usuario -->
    <?php
    // empty() es true si la variable está vacía. Con "!" lo invertimos: "si NO está vacía".
    // Si hay mensaje de éxito, lo mostramos en verde y en negrita.
    if(!empty($mensaje)) echo "<p style='color: green; font-weight: bold;'>$mensaje</p>";
    // Si hay mensaje de error, lo mostramos en rojo y en negrita.
    if(!empty($error)) echo "<p style='color: red; font-weight: bold;'>$error</p>";
    ?>

    <!-- count() cuenta cuántos productos hay en la lista -->
    <h3>Productos Registrados (Total: <?php echo count($_SESSION['lista_productos']); ?>)</h3>

    <!-- Renderizar la tabla con los productos -->
    <!-- Sintaxis alternativa de PHP (if: ... else: ... endif;) muy cómoda para mezclar con HTML -->
    <?php if (!empty($_SESSION['lista_productos'])): ?>

        <!-- border="1": borde en las celdas. cellpadding: espacio interior. cellspacing="0": sin huecos entre celdas. -->
        <table border="1" cellpadding="8" cellspacing="0">
            <!-- thead: cabecera de la tabla con los títulos de las columnas -->
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Producto</th>
                    <th>Precio</th>
                    <th>Stock</th>
                </tr>
            </thead>
            <!-- tbody: cuerpo de la tabla con los datos -->
            <tbody>
                <!-- Repetimos una fila (<tr>) por cada producto de la lista -->
                <?php foreach ($_SESSION['lista_productos'] as $p): ?>
                    <tr>
                        <td><?php echo $p['id_producto']; ?></td>
                        <!-- htmlspecialchars() convierte caracteres como < y > en texto inofensivo.
                             Evita que alguien escriba código HTML o JavaScript malicioso como nombre. -->
                        <td><?php echo htmlspecialchars($p['nombre_producto']); ?></td>
                        <td><?php echo $p['precio']; ?> €</td>
                        <td><?php echo $p['stock']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>

        <!-- Botón para limpiar la sesión.
             action="" significa que se envía a esta misma página. -->
        <form action="" method="post">
            <!-- La acción "borrar_todas" la detecta el PASO B de arriba -->
            <input type="hidden" name="accion" value="borrar_todas">
            <button type="submit" style="color: red;">Limpiar Lista de la Sesión</button>
        </form>

    <?php else: ?>
        <!-- Si la lista está vacía, mostramos este texto en lugar de la tabla -->
        <p>No hay productos registrados actualmente.</p>
    <?php endif; ?>

    <br><hr>
    <!-- Enlace para volver al formulario principal (index.html) -->
    <a href="index.html">← Volver al formulario</a>

</body>
</html>
