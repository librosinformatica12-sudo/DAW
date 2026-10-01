USE sakila;

-- Requisito (ya ejecutado):
-- ALTER TABLE sakila.staff
--   ADD COLUMN intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
--   ADD COLUMN ultimo_intento DATETIME NULL;

DROP PROCEDURE IF EXISTS Login;
DELIMITER $$
CREATE PROCEDURE Login(
    IN  l_username VARCHAR(16),
    IN  l_password VARCHAR(40),   -- ya hasheada desde PHP (md5)
    OUT _res       INT
)
BEGIN
    /*
       > 0 -> login correcto (es el staff_id)
        -1 -> usuario o contrasena vacios
        -2 -> usuario o contrasena incorrectos (o usuario inactivo)
       -99 -> error inesperado de base de datos
    */
    DECLARE v_id INT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION SET _res = -99;

	        SELECT password INTO v_id
          FROM staff
         WHERE username = TRIM(l_username)
           AND password = l_password = password;
           
           
    IF (l_username IS NULL OR TRIM(l_username) = '' OR l_password IS NULL OR l_password = '') THEN
        SET _res = -1;
    ELSE

	-- si es la misma
		-- o diferente 
        IF (v_id IS NULL) THEN
            SET _res = -2;
        ELSE
            SET _res = v_id;
        END IF;
    END IF;
END $$
DELIMITER ;


-- confirmar q se creo bien
SELECT ROUTINE_NAME FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'sakila' AND ROUTINE_NAME = 'Login';

-- probar
select count(*) from staff;
select * from staff order by staff_id desc limit 5;


update staff set password = md5('1234'), active = 1 where username = 'oscar';