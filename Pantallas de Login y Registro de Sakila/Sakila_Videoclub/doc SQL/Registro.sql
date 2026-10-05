USE sakila;

DROP PROCEDURE IF EXISTS Registro;
DELIMITER $$
CREATE PROCEDURE Registro(
    IN  r_first_name VARCHAR(45),
    IN  r_last_name  VARCHAR(45),
    IN  r_email      VARCHAR(50),
<<<<<<< HEAD
    IN  r_store_id   TINYINT UNSIGNED,
=======
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2
    IN  r_username   VARCHAR(16),
    IN  r_password   VARCHAR(40),   -- ya hasheada desde PHP
    OUT _res         INT
)
BEGIN
    /*
      > 0 -> OK, staff_id del nuevo empleado
       -1 -> usuario vacío
       -2 -> usuario ya existe
       -3 -> contraseña vacía
       -4 -> email ya existe
       -5 -> nombre, apellido o email vacíos
<<<<<<< HEAD
       -6 -> tienda no válida
=======
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2
      -99 -> error inesperado de base de datos
    */
    DECLARE v_address SMALLINT UNSIGNED;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION SET _res = -99;

    IF (r_username IS NULL OR TRIM(r_username) = '') THEN
        SET _res = -1;
    ELSEIF (r_password IS NULL OR r_password = '') THEN
        SET _res = -3;
    ELSEIF (r_first_name IS NULL OR TRIM(r_first_name) = ''
         OR r_last_name  IS NULL OR TRIM(r_last_name)  = ''
         OR r_email      IS NULL OR TRIM(r_email)      = '') THEN
        SET _res = -5;
<<<<<<< HEAD
    ELSEIF (r_store_id IS NULL OR r_store_id < 1) THEN
        SET _res = -6;
=======
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2
    ELSEIF EXISTS (SELECT 1 FROM staff WHERE username = TRIM(r_username)) THEN
        SET _res = -2;
    ELSEIF EXISTS (SELECT 1 FROM staff WHERE email = TRIM(r_email)) THEN
        SET _res = -4;
    ELSE
        SELECT MIN(address_id) INTO v_address FROM address;

        INSERT INTO staff (first_name, last_name, address_id, email,
                           store_id, active, username, password)
        VALUES (TRIM(r_first_name), TRIM(r_last_name), v_address, TRIM(r_email),
<<<<<<< HEAD
                r_store_id, 1, TRIM(r_username), r_password);
=======
                1, 1, TRIM(r_username), r_password);
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2

        SET _res = LAST_INSERT_ID();
    END IF;
END $$
<<<<<<< HEAD
DELIMITER ;
=======
DELIMITER ;


	-- PARA BORRAR FILA
	SET FOREIGN_KEY_CHECKS = 0;
	DELETE FROM staff WHERE staff_id = 12;
	SET FOREIGN_KEY_CHECKS = 1;
>>>>>>> 2b3ebca87df4b4846a6c762070ecb9d5d3fd06c2
