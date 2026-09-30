USE sakila;

-- Requisito (ya ejecutado):
-- ALTER TABLE sakila.staff
--   ADD COLUMN intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
--   ADD COLUMN ultimo_intento DATETIME NULL;

drop procedure

DROP PROCEDURE IF EXISTS Registro;
delimiter $$


delimiter ;








-- confirmar q se creo bien
SELECT ROUTINE_NAME FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'sakila' AND ROUTINE_NAME = 'Login';