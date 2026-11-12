-- ============================================================
-- EJERCICIO 2 - VENTA DE ENTRADAS CON STOCK Y TRANSACCION
-- Base de datos: conciertos_ventas_examen
-- ============================================================

create database if not exists conciertos_ventas_examen;
use conciertos_ventas_examen;

-- Se borra primero la tabla hija porque tiene clave foranea.
drop table if exists entrada;
drop table if exists concierto;

-- ------------------------------------------------------------
-- Tabla concierto
-- aforo: entradas que caben en total.
-- entradas_vendidas: entradas que ya se han vendido.
-- ------------------------------------------------------------
create table concierto(
    id_concierto int unsigned not null auto_increment primary key,
    nombre_banda varchar(60) not null,
    fecha_concierto datetime not null,
    precio decimal(6,2) not null,
    aforo int unsigned not null,
    entradas_vendidas int unsigned not null default 0
);

-- ------------------------------------------------------------
-- Tabla entrada
-- Cada fila es una compra de una o varias entradas.
-- ------------------------------------------------------------
create table entrada(
    id_entrada int unsigned not null auto_increment primary key,
    fk_concierto int unsigned not null,
    cantidad int unsigned not null,
    total decimal(8,2) not null,
    fecha_compra datetime not null default current_timestamp,
    foreign key (fk_concierto) references concierto(id_concierto)
);

-- ------------------------------------------------------------
-- Datos de prueba
-- Las fechas son futuras para que se puedan comprar entradas.
-- ------------------------------------------------------------
insert into concierto(nombre_banda, fecha_concierto, precio, aforo, entradas_vendidas)
values
('Los Dinosaurios', '2030-05-01 20:00:00', 25.50, 50, 0),
('The Rockers', '2030-06-15 21:00:00', 40.00, 30, 0),
('Jazz Night', '2030-07-20 19:30:00', 15.75, 100, 0);

-- ------------------------------------------------------------
-- Procedimiento ComprarEntradas
-- Devuelve:
--   0 = compra correcta
--  -1 = el concierto no existe
--  -2 = cantidad menor o igual que 0
--  -3 = no hay entradas suficientes
--  -4 = el concierto ya ha pasado
--  -5 = error inesperado
-- ------------------------------------------------------------
drop procedure if exists ComprarEntradas;

delimiter //
create procedure ComprarEntradas(
    in _id_concierto int,
    in _cantidad int,
    out _res int
)
begin
    declare v_aforo int unsigned;
    declare v_vendidas int unsigned;
    declare v_fecha datetime;
    declare v_precio decimal(6,2);

    -- Si ocurre cualquier error SQL, se deshace la transaccion.
    declare exit handler for sqlexception
    begin
        rollback;
        set _res = -5;
    end;

    set _res = -5;

    -- Inicio de la transaccion: todo o nada.
    start transaction;

    -- Bloquea la fila del concierto para que dos compras a la vez
    -- no puedan vender mas entradas de las que hay.
    select aforo, entradas_vendidas, fecha_concierto, precio
      into v_aforo, v_vendidas, v_fecha, v_precio
      from concierto
     where id_concierto = _id_concierto
     for update;

    -- Si el SELECT no encontro ninguna fila, el concierto no existe.
    if row_count() = 0 then
        rollback;
        set _res = -1;

    -- La cantidad tiene que ser positiva.
    elseif _cantidad <= 0 then
        rollback;
        set _res = -2;

    -- No se puede comprar para un concierto que ya paso.
    elseif v_fecha <= now() then
        rollback;
        set _res = -4;

    -- No se puede superar el aforo.
    elseif v_vendidas + _cantidad > v_aforo then
        rollback;
        set _res = -3;

    -- Todo correcto: se guarda la entrada y se actualiza el stock.
    else
        insert into entrada(fk_concierto, cantidad, total)
        values(_id_concierto, _cantidad, _cantidad * v_precio);

        update concierto
           set entradas_vendidas = entradas_vendidas + _cantidad
         where id_concierto = _id_concierto;

        commit;
        set _res = 0;
    end if;
end //
delimiter ;

-- ------------------------------------------------------------
-- Procedimiento ListaConciertos
-- Devuelve los conciertos y calcula las entradas disponibles.
-- ------------------------------------------------------------
drop procedure if exists ListaConciertos;

delimiter //
create procedure ListaConciertos()
begin
    select id_concierto,
           nombre_banda,
           fecha_concierto,
           precio,
           aforo,
           entradas_vendidas,
           (aforo - entradas_vendidas) as entradas_disponibles
      from concierto
     order by fecha_concierto;
end //
delimiter ;

-- ------------------------------------------------------------
-- Procedimiento ListaEntradas
-- Devuelve las ventas realizadas con el nombre del concierto.
-- ------------------------------------------------------------
drop procedure if exists ListaEntradas;

delimiter //
create procedure ListaEntradas()
begin
    select e.id_entrada,
           c.nombre_banda,
           e.cantidad,
           e.total,
           e.fecha_compra
      from entrada e
      inner join concierto c on c.id_concierto = e.fk_concierto
     order by e.fecha_compra desc, e.id_entrada desc;
end //
delimiter ;
