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
    OUT _res       INT,
    OUT _extra     INT            -- -3: intentos que quedan | -4: minutos de bloqueo que faltan
)
BEGIN
    /*
      CODIGOS DE SALIDA (_res):
         1  -> login correcto (ademas devuelve el registro del empleado)
        -1  -> usuario o contrasena vacios
        -2  -> el usuario no existe
        -3  -> contrasena incorrecta
        -4  -> usuario bloqueado (3 fallos, se libera a los 5 minutos)
       -99  -> error inesperado de base de datos
    */
    DECLARE v_id        INT          DEFAULT NULL;
    DECLARE v_pass      VARCHAR(40)  DEFAULT NULL;
    DECLARE v_intentos  INT          DEFAULT 0;
    DECLARE v_ultimo    DATETIME     DEFAULT NULL;
    DECLARE v_segundos  INT          DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        SET _res = -99;
        SET _extra = 0;
    END;

    SET _res = 0;
    SET _extra = 0;

    IF (l_username IS NULL OR TRIM(l_username) = '' OR l_password IS NULL OR l_password = '') THEN
        SET _res = -1;

    ELSE
        SELECT staff_id, password, intentos_fallidos, ultimo_intento
          INTO v_id, v_pass, v_intentos, v_ultimo
          FROM staff
         WHERE username = TRIM(l_username)
         LIMIT 1;

        IF (v_id IS NULL) THEN
            SET _res = -2;

        ELSE
            -- BONUS++: si estaba bloqueado y ya pasaron 5 minutos, se libera solo
            IF (v_intentos >= 3
                AND (v_ultimo IS NULL OR TIMESTAMPDIFF(SECOND, v_ultimo, NOW()) >= 300)) THEN
                UPDATE staff SET intentos_fallidos = 0 WHERE staff_id = v_id;
                SET v_intentos = 0;
            END IF;

            IF (v_intentos >= 3) THEN
                -- BONUS+: sigue bloqueado, ni se mira la contrasena
                SET v_segundos = 300 - TIMESTAMPDIFF(SECOND, v_ultimo, NOW());
                SET _extra = CEIL(v_segundos / 60);
                SET _res = -4;

            ELSEIF (v_pass = l_password) THEN
                UPDATE staff
                   SET intentos_fallidos = 0, ultimo_intento = NULL
                 WHERE staff_id = v_id;
                SET _res = 1;

                SELECT staff_id, first_name, last_name, email, store_id, username
                  FROM staff
                 WHERE staff_id = v_id;

            ELSE
                SET v_intentos = v_intentos + 1;
                UPDATE staff
                   SET intentos_fallidos = v_intentos, ultimo_intento = NOW()
                 WHERE staff_id = v_id;

                IF (v_intentos >= 3) THEN
                    SET _extra = 5;
                    SET _res = -4;
                ELSE
                    SET _extra = 3 - v_intentos;
                    SET _res = -3;
                END IF;
            END IF;
        END IF;
    END IF;
END $$

DELIMITER ;
-- confirmar q se creo bien
SELECT ROUTINE_NAME FROM information_schema.ROUTINES
WHERE ROUTINE_SCHEMA = 'sakila' AND ROUTINE_NAME = 'Login';

CALL Login('JoelOscar', MD5('contraseña_mala'), @res, @extra);
SELECT @res, @extra;

UPDATE sakila.staff SET intentos_fallidos = 0, ultimo_intento = NULL WHERE username = 'JoelOscar';
CALL Login('JoelOscar', MD5('tu_contraseña_real'), @res, @extra);
SELECT @res, @extra;

DELETE FROM staff WHERE staff_id = 17;