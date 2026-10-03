USE sakila;

-- Requisito (ya ejecutado):
-- ALTER TABLE sakila.staff
--   ADD COLUMN intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
--   ADD COLUMN ultimo_intento DATETIME NULL;

DROP PROCEDURE IF EXISTS Login;
DELIMITER $$
CREATE PROCEDURE Login(
    IN  l_username VARCHAR(50),
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

	        SELECT staff_id INTO v_id
          FROM staff
         WHERE username = TRIM(l_username)
            OR email    = TRIM(l_username)
         ORDER BY (username = TRIM(l_username)) DESC
         LIMIT 1;
           
           
		IF (l_username IS NULL OR TRIM(l_username) = '' OR l_password IS NULL OR l_password = '') THEN
				SET _res = -1;
			ELSEIF (v_id IS NULL) THEN
				SET _res = -2;
			ELSE
				SET _res = v_id;
			END IF;
END $$
DELIMITER ;

SHOW CREATE PROCEDURE Login;
UPDATE staff SET password = MD5('1234'), active = 1 WHERE username = 'oscar';

CALL Login('oscar', '1234', @res);
SELECT @res;