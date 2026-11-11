-- =====================================================================
--  BASE DE DATOS DE LA TIENDA
--  Ejecuta este archivo entero en phpMyAdmin (pestaña "SQL") ANTES de
--  abrir la página web. Crea la base de datos, las tablas y los
--  procedimientos almacenados que usa el PHP.
-- =====================================================================


-- ---------------------------------------------------------------------
-- 1. CREAR LA BASE DE DATOS
-- ---------------------------------------------------------------------

-- Borra la base de datos "tienda" si ya existía (así empezamos siempre de cero).
-- OJO: esto elimina todos los datos que hubiera dentro.
drop database if exists tienda;

-- Crea la base de datos solo si no existe ya.
create database if not exists tienda;

-- Le dice a MySQL que a partir de aquí trabajamos dentro de la base "tienda".
use tienda;


-- ---------------------------------------------------------------------
-- 2. CREAR LAS TABLAS
-- ---------------------------------------------------------------------

-- Tabla donde se guardan los productos que vende la tienda.
create table RegistrarProductos(
    id_producto int primary key auto_increment,  -- Identificador único; se numera solo (1, 2, 3...)
    nombre_producto varchar(50),                 -- Nombre del producto (máximo 50 caracteres)
    precio DECIMAL(6,2),                         -- Precio: hasta 6 cifras en total, 2 de ellas decimales (ej: 1234.50)
    stock int                                    -- Unidades que quedan disponibles para vender
);

-- Tabla donde se guarda cada compra que se hace (una fila por unidad comprada).
create table ComprarProductos(
    id_compra int auto_increment primary key,    -- Identificador único de la compra; se numera solo
    producto int,                                -- ID del producto que se ha comprado
    FechaCompra datetime,                        -- Fecha y hora exacta en la que se compró
    -- Clave foránea: obliga a que "producto" sea un id_producto que exista de verdad
    -- en la tabla RegistrarProductos (no se puede comprar un producto inventado).
    foreign key (producto) references RegistrarProductos(id_producto)
);


-- ---------------------------------------------------------------------
-- 3. PROCEDIMIENTO PARA REGISTRAR UN PRODUCTO NUEVO
--    Devuelve un código en _res:
--       0 = todo correcto
--      -2 = nombre vacío
--      -3 = precio no válido (vacío o menor de 1)
--      -4 = stock no válido (vacío o menor de 1)
-- ---------------------------------------------------------------------

-- Cambiamos el delimitador de ";" a "$$" para poder escribir ";" dentro del procedimiento
-- sin que MySQL crea que la instrucción ya ha terminado.
delimiter $$

-- Si el procedimiento ya existía, se borra para poder crearlo de nuevo.
drop procedure if exists NuevoProducto$$

create procedure NuevoProducto(
    in _nombre_producto varchar(50),   -- ENTRADA: nombre del producto
    in _precio DECIMAL(6,2),           -- ENTRADA: precio del producto
    in _stock int,                     -- ENTRADA: unidades disponibles
    out _res int                       -- SALIDA: código de resultado (ver arriba)
)
begin
    -- Validación 1: el nombre no puede estar vacío ni ser nulo
    if (_nombre_producto LIKE "" or _nombre_producto is null) then
        set _res = -2;  -- Nombre vacío

    -- Validación 2: el precio no puede ser nulo ni menor de 1 €
    elseif (_precio IS NULL or _precio < 1) then
        set _res = -3;  -- Precio no válido

    -- Validación 3: el stock no puede ser nulo ni menor de 1 unidad
    elseif (_stock IS NULL or _stock < 1) then
        set _res = -4;  -- Stock no válido

    -- Si pasa todas las validaciones, se guarda el producto
    else
        insert into RegistrarProductos(nombre_producto, precio, stock)
        values (_nombre_producto, _precio, _stock);
        set _res = 0;   -- Todo correcto
    end if;
end$$

-- Volvemos a poner ";" como delimitador normal.
delimiter ;


-- ---------------------------------------------------------------------
-- 4. PROCEDIMIENTO PARA COMPRAR UN PRODUCTO
--    Devuelve un código en _res:
--       0 = compra realizada
--      -1 = ID de producto no válido (vacío o menor o igual que 0)
--      -4 = el producto no existe
--      -5 = el producto está agotado (stock 0)
-- ---------------------------------------------------------------------
delimiter $$
drop procedure if exists ComprarProd$$

create procedure ComprarProd(
    in _producto int,      -- ENTRADA: ID del producto que se quiere comprar
    out _res int           -- SALIDA: código de resultado (ver arriba)
)
begin
    -- Variables locales que usaremos dentro del procedimiento
    DECLARE existeProducto INT;   -- Guardará el id del producto si existe (o NULL si no)
    DECLARE stockActual INT;      -- Guardará las unidades que quedan de ese producto

    -- Damos valores iniciales a las variables
    SET existeProducto = NULL;
    SET stockActual = 0;

    -- Buscamos el producto en la tabla y guardamos su id y su stock en las variables.
    -- Si no existe, existeProducto se queda en NULL.
    SELECT id_producto, stock FROM RegistrarProductos WHERE id_producto = _producto
    INTO existeProducto, stockActual;

    -- Comprobación 1: el ID tiene que ser un número positivo
    if (_producto IS NULL or _producto <= 0) then
        set _res = -1;  -- ID inválido

    -- Comprobación 2: el producto tiene que existir en la tabla
    elseif existeProducto IS NULL then
        set _res = -4;  -- El producto no existe

    -- Comprobación 3: tiene que quedar al menos 1 unidad
    elseif stockActual <= 0 then
        set _res = -5;  -- Sin stock

    -- Si todo está bien, se hace la compra
    else
        -- Guardamos la compra con la fecha y hora actuales (now())
        insert into ComprarProductos(producto, FechaCompra)
        values (_producto, timestamp(now()));

        -- Restamos 1 unidad al stock del producto comprado
        update RegistrarProductos set stock = stock - 1 where id_producto = _producto;

        set _res = 0;   -- Todo correcto
    end if;
end$$
delimiter ;


-- ---------------------------------------------------------------------
-- 5. CONSULTA DE COMPROBACIÓN
--    Muestra todas las compras realizadas (al principio estará vacía).
-- ---------------------------------------------------------------------
select * from ComprarProductos;
