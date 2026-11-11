<?php
// =====================================================================
//  AccesoDatos.php
//  Este archivo se encarga de TODO lo que tiene que ver con la base de
//  datos: conectarse a MySQL y llamar a los procedimientos almacenados.
//  Los demás archivos PHP lo incluyen con require_once y usan sus funciones.
// =====================================================================


// ---------------------------------------------------------------------
// DATOS DE CONEXIÓN A MYSQL
// ---------------------------------------------------------------------
$host = '127.0.0.1';      // Dirección del servidor MySQL (127.0.0.1 = tu propio ordenador)
$port = 3306;             // Puerto de MySQL (3306 es el normal en XAMPP)
$database = 'tienda';     // Nombre de la base de datos que creamos con Tienda.sql
$username = 'oscar';      // Usuario de MySQL  <-- cámbialo por el tuyo (en XAMPP suele ser "root")
$password = '1234';   // Contraseña de MySQL <-- cámbiala por la tuya (en XAMPP suele estar vacía)


// ---------------------------------------------------------------------
// CONEXIÓN CON PDO
// PDO es la herramienta de PHP para hablar con bases de datos.
// ---------------------------------------------------------------------
try {
    // Creamos la conexión. La guardamos en la variable $pdo para usarla en las funciones.
    $pdo = new PDO(
        // "DSN": dice qué tipo de base de datos es, dónde está y cómo se llama.
        // charset=utf8mb4 permite guardar tildes, ñ y emojis sin problemas.
        "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4",
        $username,
        $password,
        [
            // Si hay un error en MySQL, PHP lanzará una excepción (así podemos capturarlo con catch).
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Los resultados de las consultas llegan como arrays con el nombre de la columna
            // como clave, por ejemplo $fila['nombre_producto'].
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    // Si no se puede conectar, paramos todo y mostramos el motivo del error.
    die("Error al conectar: " . $e->getMessage());
}


// ==========================================
// 1. FUNCIÓN PARA REGISTRAR NUEVO PRODUCTO
// ==========================================
// Recibe los datos del producto y se los pasa al procedimiento NuevoProducto de MySQL.
// Devuelve el código que dé el procedimiento: 0 si va bien, o -2, -3, -4 si hay error.
function PA_NuevoProducto($nombre_producto, $precio, $stock){
    // "global $pdo" permite usar dentro de la función la conexión creada arriba.
    global $pdo;

    // Preparamos la llamada al procedimiento. Los "?" son huecos que se rellenan después
    // (así evitamos la inyección SQL). @res es la variable de SALIDA de MySQL.
    $stmt = $pdo->prepare('CALL NuevoProducto(?, ?, ?, @res)');

    // Ejecutamos la llamada rellenando los tres "?" en orden: nombre, precio y stock.
    $stmt->execute([$nombre_producto, $precio, $stock]);

    // Cerramos el cursor para liberar la conexión. Sin esto, la siguiente consulta
    // puede dar el error "Cannot execute queries while other unbuffered queries are active".
    $stmt->closeCursor();

    // Leemos el valor que el procedimiento dejó en la variable @res.
    $res = $pdo->query('SELECT @res AS resultado')->fetch();

    // Lo convertimos a número entero y lo devolvemos.
    return (int)$res['resultado'];
}


// ==========================================
// 2. FUNCIÓN PARA COMPRAR UN PRODUCTO
// ==========================================
// Recibe el ID del producto y llama al procedimiento ComprarProd de MySQL.
// Devuelve: 0 si va bien, -1 (ID inválido), -4 (no existe) o -5 (sin stock).
function PA_ComprarProd($producto){
    global $pdo;

    // Preparamos la llamada al procedimiento (1 dato de entrada + la salida @res).
    $stmt = $pdo->prepare('CALL ComprarProd(?, @res)');

    // Ejecutamos pasando el ID del producto.
    $stmt->execute([$producto]);

    // Liberamos la conexión (igual que en la función anterior).
    $stmt->closeCursor();

    // Recogemos el código de resultado que dejó MySQL en @res.
    $res = $pdo->query('SELECT @res AS resultado')->fetch();

    return (int)$res['resultado'];
}


// ==========================================
// 3. FUNCIÓN PARA LISTAR LOS PRODUCTOS
// ==========================================
// Devuelve un array con todos los productos guardados, ordenados por nombre (A-Z).
function PA_ListaProductos(){
    global $pdo;

    // Aquí no hay datos del usuario, así que no hace falta prepare(): basta con query().
    $stmt = $pdo->query('SELECT id_producto, nombre_producto, precio, stock FROM RegistrarProductos ORDER BY nombre_producto ASC');

    // fetchAll() devuelve TODAS las filas de golpe en un array.
    return $stmt->fetchAll();
}
?>
