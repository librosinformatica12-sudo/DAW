-- ============================================================
-- generador_conciertos.sql
-- Script para crear la base de datos, las tablas y los procedures
-- del ejercicio de conciertos.
-- Se puede pegar en phpMyAdmin -> pestaña SQL -> Ejecutar.
-- ============================================================

-- Crea la base de datos si no existe.
create database if not exists gestor_conciertos;

-- Selecciona la base de datos para trabajar con ella.
use gestor_conciertos;

-- Se borran primero las tablas hijas (entradas) y luego la padre (concierto),
-- porque entradas tiene una clave foránea que apunta a concierto.
drop table if exists entradas;
drop table if exists concierto;

-- Tabla principal: guarda los conciertos.
create table concierto(
    id_concierto int unsigned not null auto_increment primary key,
    nombre_banda varchar(60) not null,
    fecha_concierto datetime not null,
    precio decimal(6,2) not null
);

-- Tabla de entradas: cada entrada pertenece a un concierto.
create table entradas(
    id int unsigned not null auto_increment primary key,
    fecha_adquisision datetime not null default current_timestamp,
    fk_concierto int unsigned not null,
    foreign key (fk_concierto) references concierto(id_concierto)
);

-- ------------------------------------------------------------
-- Procedure AltaConcierto
-- Inserta un concierto y devuelve un código de resultado en _res.
-- No recibe id_concierto porque es AUTO_INCREMENT.
-- ------------------------------------------------------------
drop procedure if exists AltaConcierto;

-- DELIMITER cambia el separador para poder escribir ; dentro del procedure.
delimiter //
create procedure AltaConcierto(
    in _nombre_banda varchar(60),
    in _fecha_concierto datetime,
    in _precio decimal(6,2),
    out _res int
)
begin
    -- Caso 1: nombre vacío o nulo.
    if (_nombre_banda is null or trim(_nombre_banda) = '') then
        set _res = -1;

    -- Caso 2: fecha nula. No se compara con '' porque en DATETIME puede dar error.
    elseif (_fecha_concierto is null) then
        set _res = -2;

    -- Caso 3: la fecha debe ser posterior al momento actual.
    elseif (_fecha_concierto <= now()) then
        set _res = -3;

    -- Caso 4: el precio debe ser mayor que 0.
    elseif (_precio <= 0) then
        set _res = -4;

    -- Caso 0: todo correcto. El ELSE es importante para que el INSERT
    -- no se ejecute cuando ha fallado una validación anterior.
    else
        insert into concierto(nombre_banda, fecha_concierto, precio)
        values (_nombre_banda, _fecha_concierto, _precio);

        set _res = 0;
    end if;
end //
delimiter ;

-- ------------------------------------------------------------
-- Procedure ListaConcierto
-- Devuelve todos los conciertos ordenados por fecha.
-- ------------------------------------------------------------
delimiter //
create procedure ListaConcierto()
begin
    select id_concierto, nombre_banda, fecha_concierto, precio
    from concierto
    order by fecha_concierto;
end //
delimiter ;
