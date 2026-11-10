create database if not exists gestor_conciertos;
use gestor_conciertos;

drop table if exists entradas;
drop table if exists concierto;

create table concierto(
	id_concierto int unsigned not null primary key,
    nombre_banda varchar(60) not null,
    fecha_concierto datetime not null,
    precio decimal(6,2) not null
);

create table entradas(
	id int unsigned not null auto_increment primary key,
    fecha_adquisision datetime not null default current_timestamp,
    fk_concierto int unsigned not null,
    foreign key (fk_concierto) references concierto(id_concierto)
);

-- procedure registrar_conciertos
drop procedure if exists AltaConcierto;

delimiter //
create procedure AltaConcierto( 
	in _id_concierto int, 
    in _nombre_banda varchar(50), 
    in _fecha_concierto datetime,
    in _precio decimal,
    out _res int)
    
begin
	-- CASO 1: Si no hay nombre de banda
    if(_nombre_banda = "" or _nombre_banda is null) then
		set _res = -1;
    -- CASO 2: Si la fecha está vacia
    elseif (_fecha_concierto is null or _fecha_concierto = "") then
		set _res = -2;
    -- CASO 3: Si la fecha del concierto es anterior al present
    elseif(_fecha_concierto <= now()) then
		set _res = -3;
    -- CASO 4: Si el precio es mejor o igual a o
    elseif (_precio <= 0) then
		set _res = -4;

	-- CASO 0 - TODO OK. Instruccion nuclear
    insert into concierto(id_concierto, nombre_banda, fecha_concierto, precio)
		values(_id_concierto, _nombre_banda, _fecha_concierto, _precio);
	set _res = 0;
    
    end if;
end //
delimiter ;