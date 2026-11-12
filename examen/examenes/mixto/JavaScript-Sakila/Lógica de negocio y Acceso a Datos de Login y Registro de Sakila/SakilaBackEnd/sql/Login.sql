USE sakila;

DROP PROCEDURE IF EXISTS Login;
DELIMITER $$
CREATE PROCEDURE Login(
    IN  l_username VARCHAR(50),
    IN  l_password VARCHAR(40),   -- ya hasheada desde PHP
    OUT _res       INT
)
main: BEGIN
    /*
      > 0 -> login correcto (staff_id) y se devuelve el registro del empleado
       -1 -> usuario o contraseña vacíos
       -2 -> usuario o contraseña incorrectos (o usuario inactivo)
       -3 -> cuenta bloqueada (3 intentos fallidos, se libera a los 60 segundos)
      -99 -> error inesperado de base de datos
    */
    DECLARE v_id       INT DEFAULT NULL;
    DECLARE v_pass     VARCHAR(40) DEFAULT NULL;
    DECLARE v_active   TINYINT DEFAULT 0;
    DECLARE v_intentos INT DEFAULT 0;
    DECLARE v_ultimo   DATETIME DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION SET _res = -99;

    -- 1) Datos vacíos
    IF (l_username IS NULL OR TRIM(l_username) = ''
        OR l_password IS NULL OR l_password = '') THEN
        SET _res = -1;
        LEAVE main;
    END IF;

    -- 2) Buscar al usuario
    SELECT staff_id, password, active, intentos_fallidos, ultimo_intento
      INTO v_id, v_pass, v_active, v_intentos, v_ultimo
      FROM staff
     WHERE username = TRIM(l_username) OR email = TRIM(l_username)
     ORDER BY (username = TRIM(l_username)) DESC
     LIMIT 1;

    -- 3) No existe o inactivo (no cuenta como intento)
    IF v_id IS NULL OR v_active = 0 THEN
        SET _res = -2;
        LEAVE main;
    END IF;

    -- 4) ¿Está bloqueado?
    IF v_intentos >= 3 THEN
        IF TIMESTAMPDIFF(SECOND, v_ultimo, NOW()) < 300 THEN -- 5 minutos
            SET _res = -3;              -- sigue bloqueado
            LEAVE main;
        ELSE
            -- BONUS++: pasó 1 minuto, se desbloquea solo
            UPDATE staff
               SET intentos_fallidos = 0, ultimo_intento = NULL
             WHERE staff_id = v_id;
            SET v_intentos = 0;
        END IF;
    END IF;

    -- 5) Comprobar contraseña
    IF v_pass = l_password THEN
        UPDATE staff
           SET intentos_fallidos = 0, ultimo_intento = NULL
         WHERE staff_id = v_id;

        SELECT staff_id, first_name, last_name, email, store_id, active, username
          FROM staff
         WHERE staff_id = v_id;

        SET _res = v_id;
    ELSE
        UPDATE staff
           SET intentos_fallidos = intentos_fallidos + 1,
               ultimo_intento = NOW()
         WHERE staff_id = v_id;

        IF v_intentos + 1 >= 3 THEN
            SET _res = -3;
        ELSE
            SET _res = -2;
        END IF;
    END IF;
END main $$
DELIMITER ;