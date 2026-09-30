use sakila;

drop procedure if exists Registro;
delimiter $$
create procedure Registro(
    in r_first_name varchar(45),
    in r_last_name varchar(45),
    in r_email varchar(50),
    in r_store_id int,
    in r_username varchar(16),
    in r_password varchar(40),
    out _res int
)
begin
    declare aux varchar(16) default null;

    -- CASO 1: username vacío
    if r_username = '' then
        set _res = -1;

    else
        select username into aux
        from staff
        where username = r_username
        limit 1;

        -- CASO 2: el usuario ya existe
        if aux is not null then
            set _res = -2;

        -- CASO 3: contraseña vacía
        elseif r_password = '' then
            set _res = -3;

        -- CASO 0: todo OK
        else
            insert into staff(first_name, last_name, address_id, email,
                              store_id, active, username, password)
            values (r_first_name, r_last_name, 1, r_email,
                    r_store_id, 1, r_username, r_password);
            set _res = 0;
        end if;
    end if;
end $$
delimiter ;

CALL Registro('Juan', 'Perez', 'juan@mail.com', 3, 'jperez', '1234', @resultado);
SELECT @resultado;