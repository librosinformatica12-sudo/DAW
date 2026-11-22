DROP PROCEDURE IF EXISTS `AnadirCartaElegida`;
DROP PROCEDURE IF EXISTS `AbrirCartaDiaria`;
DROP PROCEDURE IF EXISTS `AnadirCartasPrueba`;
DROP PROCEDURE IF EXISTS `ObtenerUsuarioLogin`;
DROP PROCEDURE IF EXISTS `MigrarPassword`;
DROP PROCEDURE IF EXISTS `ObtenerUsuarioPorId`;
DROP PROCEDURE IF EXISTS `ObtenerResumenColeccion`;
DROP PROCEDURE IF EXISTS `ObtenerUltimasCartas`;
DROP PROCEDURE IF EXISTS `ObtenerDinosauriosPorIds`;
DROP PROCEDURE IF EXISTS `ContarDinosaurios`;
DROP PROCEDURE IF EXISTS `ListarDinosaurios`;
DROP PROCEDURE IF EXISTS `ActualizarImagenDinosaurio`;

DELIMITER $$

CREATE PROCEDURE `AnadirCartaElegida`(IN _usuario_id INT, IN _carta_id INT, OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    SET _res = -99;

    IF NOT EXISTS (SELECT 1 FROM usuarios WHERE id = _usuario_id) THEN
        SET _res = -1;
    ELSEIF NOT EXISTS (SELECT 1 FROM dinosaurios WHERE id = _carta_id) THEN
        SET _res = -2;
    ELSE
        INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
        VALUES (_usuario_id, _carta_id, SYSDATE());

        UPDATE usuarios SET ultima_obtencion = SYSDATE() WHERE id = _usuario_id;

        SET _res = 0;
        SELECT d.* FROM dinosaurios d WHERE d.id = _carta_id;
    END IF;
END$$

CREATE PROCEDURE `AbrirCartaDiaria`(IN _usuario_id INT, OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE _ultima DATE;
    DECLARE _carta INT;

    SET _res = -99;
    SET _ultima = NULL;

    IF NOT EXISTS (SELECT 1 FROM usuarios WHERE id = _usuario_id) THEN
        SET _res = -1;
    ELSE
        SELECT DATE(ultima_obtencion) INTO _ultima FROM usuarios WHERE id = _usuario_id;

        IF _ultima = CURDATE() THEN
            SET _res = -4;
        ELSE
            SELECT id INTO _carta FROM dinosaurios ORDER BY RAND() LIMIT 1;

            IF _carta IS NULL THEN
                SET _res = -3;
            ELSE
                INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
                VALUES (_usuario_id, _carta, SYSDATE());

                UPDATE usuarios SET ultima_obtencion = SYSDATE() WHERE id = _usuario_id;

                SET _res = 0;
                SELECT d.* FROM dinosaurios d WHERE d.id = _carta;
            END IF;
        END IF;
    END IF;
END$$

CREATE PROCEDURE `AnadirCartasPrueba`(IN _usuario_id INT, IN _cuantas INT, OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE _i INT DEFAULT 0;
    DECLARE _carta INT;

    SET _res = -99;

    IF NOT EXISTS (SELECT 1 FROM usuarios WHERE id = _usuario_id) THEN
        SET _res = -1;
    ELSEIF _cuantas < 1 OR _cuantas > 20 THEN
        SET _res = -2;
    ELSE
        DROP TEMPORARY TABLE IF EXISTS tmp_cartas_prueba;
        CREATE TEMPORARY TABLE tmp_cartas_prueba (dinosaurio_id INT NOT NULL);

        WHILE _i < _cuantas DO
            SELECT id INTO _carta FROM dinosaurios ORDER BY RAND() LIMIT 1;

            IF _carta IS NOT NULL THEN
                INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
                VALUES (_usuario_id, _carta, SYSDATE());

                INSERT INTO tmp_cartas_prueba (dinosaurio_id) VALUES (_carta);
            END IF;

            SET _i = _i + 1;
        END WHILE;

        IF (SELECT COUNT(*) FROM tmp_cartas_prueba) = 0 THEN
            SET _res = -3;
        ELSE
            SET _res = 0;
            SELECT d.*
            FROM dinosaurios d
            INNER JOIN tmp_cartas_prueba t ON t.dinosaurio_id = d.id;
        END IF;

        DROP TEMPORARY TABLE tmp_cartas_prueba;
    END IF;
END$$

CREATE PROCEDURE `ObtenerUsuarioLogin`(IN _identificador VARCHAR(256), OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE _encontrado INT DEFAULT 0;

    SELECT COUNT(*) INTO _encontrado
    FROM usuarios
    WHERE email = _identificador OR username = _identificador;

    IF _encontrado = 0 THEN
        SET _res = -1;
    ELSE
        SET _res = 0;
        SELECT id, username, email, password_hash, fecha_registro, ultima_obtencion
        FROM usuarios
        WHERE email = _identificador OR username = _identificador
        LIMIT 1;
    END IF;
END$$

CREATE PROCEDURE `MigrarPassword`(IN _usuario_id INT, IN _hash VARCHAR(1024), OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    UPDATE usuarios SET password_hash = _hash WHERE id = _usuario_id;

    IF ROW_COUNT() > 0 THEN
        SET _res = 0;
    ELSE
        SET _res = -1;
    END IF;
END$$

CREATE PROCEDURE `ObtenerUsuarioPorId`(IN _usuario_id INT, OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE _encontrado INT DEFAULT 0;

    SELECT COUNT(*) INTO _encontrado FROM usuarios WHERE id = _usuario_id;

    IF _encontrado = 0 THEN
        SET _res = -1;
    ELSE
        SET _res = 0;
        SELECT id, username, email, fecha_registro, ultima_obtencion
        FROM usuarios WHERE id = _usuario_id LIMIT 1;
    END IF;
END$$

CREATE PROCEDURE `ObtenerResumenColeccion`(IN _usuario_id INT, OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    DECLARE _cartas INT DEFAULT 0;
    DECLARE _especies INT DEFAULT 0;

    SELECT COUNT(*), COUNT(DISTINCT dinosaurio_id)
    INTO _cartas, _especies
    FROM colecciones WHERE usuario_id = _usuario_id;

    SET _res = 0;
    SELECT _cartas AS cartas, _especies AS especies;
END$$

CREATE PROCEDURE `ObtenerUltimasCartas`(IN _usuario_id INT, IN _limite INT, OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    SET _limite = GREATEST(1, LEAST(_limite, 50));

    SELECT d.*, MAX(c.fecha_adquisicion) AS ultima
    FROM colecciones c
    INNER JOIN dinosaurios d ON d.id = c.dinosaurio_id
    WHERE c.usuario_id = _usuario_id
    GROUP BY d.id
    ORDER BY ultima DESC, d.id DESC
    LIMIT _limite;

    SET _res = 0;
END$$

CREATE PROCEDURE `ObtenerDinosauriosPorIds`(IN _ids VARCHAR(1000), OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    SET _res = 0;

    SELECT d.*
    FROM dinosaurios d
    WHERE FIND_IN_SET(d.id, _ids) > 0
    ORDER BY FIELD(d.id, _ids);
END$$

CREATE PROCEDURE `ContarDinosaurios`(OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    SET _res = 0;
    SELECT COUNT(*) AS total FROM dinosaurios;
END$$

CREATE PROCEDURE `ListarDinosaurios`(OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    SET _res = 0;
    SELECT * FROM dinosaurios ORDER BY id;
END$$

CREATE PROCEDURE `ActualizarImagenDinosaurio`(IN _id INT, IN _url VARCHAR(1024), OUT _res INT)
SQL SECURITY INVOKER
BEGIN
    UPDATE dinosaurios SET image_url = _url WHERE id = _id;

    IF ROW_COUNT() > 0 THEN
        SET _res = 0;
    ELSE
        SET _res = -1;
    END IF;
END$$

DELIMITER ;
