-- base de datos
use sakila;

-- BORRAR
drop procedure if exists Registro;
delimiter $$
create procedure Registro(
	in r_first_name varchar(45),
    in r_last_name varchar(45),
    in r_email varchar(40),
    in r_store_id int,
    in r_username varchar(16),
    in r_password varchar(16),
    out _res int -- CODIGO DE ERROR DE SALIDA
)
begin
	declare aux varchar(40);
    set aux = null;
    
-- CASO 1 - El parametro _username esta vacio
	if(_username like "") then
		set _res = -1;
        
-- CASO 2 - El usuario que se pretende registrar YA EXISTE
    set _res = -2;
    select username from staff where username like _username into aux;
    
-- CASO 3 - Contraseña vacia
	elseif (_password like "") then
    set _res = -3;

-- CASO 0 - Instruccion nuclear, Todo esta OK
    else
    insert into staff(first_name, last_name, address_id, 
	email, store_id, active, username, password)
    
    values (r_first_name, r_last_name, 1, r_email, r_store_id, r_username, r_password);
	set _res = 0;
    end if ;
-- DATOS

end $$
delimiter ;

CALL Registro(
    'Juan',              -- r_first_name
    'Perez',             -- r_last_name
    'juan@mail.com',     -- r_email
    3,                   -- r_store_id
    'jperez',            -- r_username
    '1234',              -- r_password
    @resultado           -- variable para el OUT
);