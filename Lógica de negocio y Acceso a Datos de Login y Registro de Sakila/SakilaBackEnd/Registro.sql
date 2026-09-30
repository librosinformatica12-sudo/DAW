USE sakila;

DROP PROCEDURE IF EXISTS Registro;
DELIMITER $$

CREATE PROCEDURE Registro(
    IN  r_first_name VARCHAR(45),
    IN  r_last_name  VARCHAR(45),
    IN  r_email      VARCHAR(50),
    IN  r_store_id   INT,
    IN  r_username   VARCHAR(16),
    IN  r_password   VARCHAR(40),
    OUT _res         INT
)
BEGIN
    /*
      CODIGOS DE SALIDA (_res):
        > 0  -> OK, es el staff_id del nuevo empleado
         -1  -> usuario vacio
         -2  -> usuario ya existe
         -3  -> contrasena vacia
         -5  -> nombre, apellido o email vacios
         -6  -> tienda fuera de rango (1 a 255)
        -99  -> error inesperado de base de datos
    */
    DECLARE existe    INT DEFAULT 0;
    DECLARE v_address INT DEFAULT NULL;
    DECLARE v_staff   INT DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET FOREIGN_KEY_CHECKS = 1;
        SET _res = -99;
    END;

    IF (r_username IS NULL OR TRIM(r_username) = '') THEN
        SET _res = -1;

    ELSEIF (r_password IS NULL OR r_password = '') THEN
        SET _res = -3;

    ELSEIF (r_first_name IS NULL OR TRIM(r_first_name) = ''
         OR r_last_name  IS NULL OR TRIM(r_last_name)  = ''
         OR r_email      IS NULL OR TRIM(r_email)      = '') THEN
        SET _res = -5;

    ELSEIF (r_store_id IS NULL OR r_store_id < 1 OR r_store_id > 255) THEN
        SET _res = -6;

    ELSE
        SELECT COUNT(*) INTO existe FROM staff WHERE username = TRIM(r_username);

        IF (existe > 0) THEN
            SET _res = -2;
        ELSE
            SELECT MIN(address_id) INTO v_address FROM address;

            SET FOREIGN_KEY_CHECKS = 0;

            INSERT INTO staff (first_name, last_name, address_id,
                               email, store_id, active, username, password)
            VALUES (TRIM(r_first_name), TRIM(r_last_name), v_address,
                    TRIM(r_email), r_store_id, 1, TRIM(r_username), r_password);

            SET v_staff = LAST_INSERT_ID();

            -- Si la tienda no existe, se crea con este empleado como encargado
            IF NOT EXISTS (SELECT 1 FROM store WHERE store_id = r_store_id) THEN
                INSERT INTO store (store_id, manager_staff_id, address_id)
                VALUES (r_store_id, v_staff, v_address);
            END IF;

            SET FOREIGN_KEY_CHECKS = 1;

            SET _res = v_staff;
        END IF;
    END IF;
END $$

DELIMITER ;
-- Bonus+: bloqueo a los 3 intentos fallidos
ALTER TABLE sakila.staff
  ADD COLUMN intentos_fallidos TINYINT UNSIGNED NOT NULL DEFAULT 0,
  ADD COLUMN ultimo_intento DATETIME NULL;

SELECT store_id FROM sakila.store;


-- PARA BORRAR FILA
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM staff WHERE staff_id = 6;
SET FOREIGN_KEY_CHECKS = 1;
