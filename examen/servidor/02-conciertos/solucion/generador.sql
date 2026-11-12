-- =====================================================
-- BASE DE DATOS: gestor_conciertos
-- Versión terminada de tu proyecto de conciertos
-- =====================================================
CREATE DATABASE IF NOT EXISTS gestor_conciertos_examen;
USE gestor_conciertos_examen;

DROP TABLE IF EXISTS entradas;
DROP TABLE IF EXISTS concierto;

CREATE TABLE concierto (
    id_concierto    INT AUTO_INCREMENT PRIMARY KEY,
    nombre_banda    VARCHAR(60) NOT NULL,
    fecha_concierto DATETIME NOT NULL,
    precio          DECIMAL(6,2) NOT NULL,
    aforo           INT NOT NULL DEFAULT 50
);

CREATE TABLE entradas (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    fecha_adquisicion  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fk_concierto       INT NOT NULL,
    CONSTRAINT fk_entradas_concierto
        FOREIGN KEY (fk_concierto) REFERENCES concierto(id_concierto)
        ON DELETE CASCADE
);

INSERT INTO concierto (nombre_banda, fecha_concierto, precio, aforo) VALUES
('Los Prueba',   '2030-06-15 21:00:00', 25.50, 100),
('Banda Examen', '2030-07-20 20:30:00', 18.00, 50);

-- =====================================================
-- LISTAR / CONSULTAR
-- =====================================================

DROP PROCEDURE IF EXISTS ListaConciertos;
DELIMITER //
CREATE PROCEDURE ListaConciertos()
BEGIN
    SELECT c.id_concierto,
           c.nombre_banda,
           c.fecha_concierto,
           c.precio,
           c.aforo,
           (SELECT COUNT(*) FROM entradas e
             WHERE e.fk_concierto = c.id_concierto) AS entradas_vendidas
    FROM concierto c
    ORDER BY c.fecha_concierto;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS DameConcierto;
DELIMITER //
CREATE PROCEDURE DameConcierto(IN _id INT)
BEGIN
    SELECT * FROM concierto WHERE id_concierto = _id;
END//
DELIMITER ;

-- =====================================================
-- ALTA
-- =====================================================

DROP PROCEDURE IF EXISTS AltaConcierto;
DELIMITER //
CREATE PROCEDURE AltaConcierto(
    IN  _nombre VARCHAR(60),
    IN  _fecha  DATETIME,
    IN  _precio DECIMAL(6,2),
    IN  _aforo  INT,
    OUT _res    INT
)
BEGIN
    IF (_nombre = '' OR _nombre IS NULL) THEN
        SET _res = -1;                                  -- nombre vacío
    ELSEIF (_fecha IS NULL) THEN
        SET _res = -2;                                  -- fecha vacía
    ELSEIF (_fecha <= NOW()) THEN
        SET _res = -3;                                  -- fecha pasada
    ELSEIF (_precio <= 0) THEN
        SET _res = -4;                                  -- precio no válido
    ELSEIF (_aforo <= 0) THEN
        SET _res = -5;                                  -- aforo no válido
    ELSE
        INSERT INTO concierto (nombre_banda, fecha_concierto, precio, aforo)
        VALUES (_nombre, _fecha, _precio, _aforo);

        SET _res = 0;
    END IF;
END//
DELIMITER ;

-- =====================================================
-- MODIFICAR
-- =====================================================

DROP PROCEDURE IF EXISTS ModificarConcierto;
DELIMITER //
CREATE PROCEDURE ModificarConcierto(
    IN  _id     INT,
    IN  _nombre VARCHAR(60),
    IN  _fecha  DATETIME,
    IN  _precio DECIMAL(6,2),
    IN  _aforo  INT,
    OUT _res    INT
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM concierto WHERE id_concierto = _id) THEN
        SET _res = -6;                                  -- no existe
    ELSEIF (_nombre = '' OR _nombre IS NULL) THEN
        SET _res = -1;
    ELSEIF (_fecha IS NULL) THEN
        SET _res = -2;
    ELSEIF (_fecha <= NOW()) THEN
        SET _res = -3;
    ELSEIF (_precio <= 0) THEN
        SET _res = -4;
    ELSEIF (_aforo <= 0) THEN
        SET _res = -5;
    ELSE
        UPDATE concierto
        SET nombre_banda = _nombre,
            fecha_concierto = _fecha,
            precio = _precio,
            aforo = _aforo
        WHERE id_concierto = _id;

        SET _res = 0;
    END IF;
END//
DELIMITER ;

-- =====================================================
-- BORRAR
-- =====================================================

DROP PROCEDURE IF EXISTS BorrarConcierto;
DELIMITER //
CREATE PROCEDURE BorrarConcierto(IN _id INT, OUT _res INT)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM concierto WHERE id_concierto = _id) THEN
        SET _res = -1;                                  -- no existe
    ELSEIF EXISTS (SELECT 1 FROM entradas WHERE fk_concierto = _id) THEN
        SET _res = -2;                                  -- ya tiene entradas
    ELSE
        DELETE FROM concierto WHERE id_concierto = _id;
        SET _res = 0;
    END IF;
END//
DELIMITER ;

-- =====================================================
-- COMPRAR ENTRADA
-- =====================================================

DROP PROCEDURE IF EXISTS ComprarEntrada;
DELIMITER //
CREATE PROCEDURE ComprarEntrada(IN _id_concierto INT, OUT _res INT)
BEGIN
    DECLARE _fecha DATETIME;
    DECLARE _aforo INT;
    DECLARE _vendidas INT DEFAULT 0;

    IF NOT EXISTS (SELECT 1 FROM concierto WHERE id_concierto = _id_concierto) THEN
        SET _res = -1;                                  -- concierto no existe
    ELSE
        SELECT fecha_concierto, aforo
          INTO _fecha, _aforo
          FROM concierto
         WHERE id_concierto = _id_concierto;

        SELECT COUNT(*)
          INTO _vendidas
          FROM entradas
         WHERE fk_concierto = _id_concierto;

        IF (_fecha <= NOW()) THEN
            SET _res = -2;                              -- concierto ya pasado
        ELSEIF (_vendidas >= _aforo) THEN
            SET _res = -3;                              -- aforo completo
        ELSE
            INSERT INTO entradas (fk_concierto)
            VALUES (_id_concierto);

            SET _res = 0;
        END IF;
    END IF;
END//
DELIMITER ;
