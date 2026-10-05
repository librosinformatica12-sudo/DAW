USE sakila;

-- Requisito (ya ejecutado):
-- ALTER TABLE sakila.staff
--   ADD COLUMN intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
--   ADD COLUMN ultimo_intento DATETIME NULL;

DROP PROCEDURE IF EXISTS Login;
delimiter $$
create procedure Login(
in login varchar(45),
in contraseña varchar(45),
out _res int
)
begin
declare exit handler for sqlexception set _res = -99;

set _res = null;

select staff_id into _res from staff where (username = trim(r_login) or email = trim(r_login))
	and password = r_password
    and active = 1
limit 1;

if _res is null then
	set _res = -1; 
end if;

end $$
delimiter ;


-- confirmar q se creo bien
SELECT ROUTINE_NAME FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'sakila' AND ROUTINE_NAME = 'Login';