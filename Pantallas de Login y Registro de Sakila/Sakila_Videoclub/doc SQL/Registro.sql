USE sakila;

DROP PROCEDURE IF EXISTS Registro;
DELIMITER $$
CREATE PROCEDURE Registro(
    IN  r_first_name VARCHAR(45),
    IN  r_last_name  VARCHAR(45),
    IN  r_email      VARCHAR(50),
    IN  r_store_id   TINYINT UNSIGNED,
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
       -6 -> tienda no válida
       -7 -> no quedan staff_id libres (máximo 255)
      -99 -> error inesperado de base de datos
    */
    DECLARE v_address SMALLINT UNSIGNED;
    DECLARE v_id      INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        DO RELEASE_LOCK('registro_staff');
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
    ELSEIF (r_store_id IS NULL OR r_store_id < 1
         OR NOT EXISTS (SELECT 1 FROM store WHERE store_id = r_store_id)) THEN
        SET _res = -6;
    ELSEIF EXISTS (SELECT 1 FROM staff WHERE username = TRIM(r_username)) THEN
        SET _res = -2;
    ELSEIF EXISTS (SELECT 1 FROM staff WHERE email = TRIM(r_email)) THEN
        SET _res = -4;
    ELSE
        -- Evita que dos registros simultáneos cojan el mismo id
        DO GET_LOCK('registro_staff', 5);

        -- Primer id libre: el 1 si falta, o el primer hueco, o el siguiente al último
        IF NOT EXISTS (SELECT 1 FROM staff WHERE staff_id = 1) THEN
            SET v_id = 1;
        ELSE
            SELECT MIN(t.staff_id + 1) INTO v_id
            FROM staff t
            LEFT JOIN staff s ON s.staff_id = t.staff_id + 1
            WHERE s.staff_id IS NULL;
        END IF;

        IF v_id > 255 THEN
            SET _res = -7;
        ELSE
            SELECT MIN(address_id) INTO v_address FROM address;

            INSERT INTO staff (staff_id, first_name, last_name, address_id, email,
                               store_id, active, username, password)
            VALUES (v_id, TRIM(r_first_name), TRIM(r_last_name), v_address, TRIM(r_email),
                    r_store_id, 1, TRIM(r_username), r_password);

            SET _res = v_id;
        END IF;

        DO RELEASE_LOCK('registro_staff');
    END IF;
END $$
DELIMITER ;

-- Limpieza
DELETE FROM sakila.staff WHERE staff_id = 3;