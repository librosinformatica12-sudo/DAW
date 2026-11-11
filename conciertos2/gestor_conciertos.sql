-- SCRIPT de Generacion de BBDD en MySQL
CREATE DATABASE IF NOT EXISTS GestorConciertos
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE GestorConciertos;

-- DROPS (primero la tabla hija, luego la padre)
DROP TABLE IF EXISTS entradas;
DROP TABLE IF EXISTS conciertos;

-- CREATE TABLES
CREATE TABLE conciertos(
    id_concierto    INT PRIMARY KEY AUTO_INCREMENT,
    nombre_banda    VARCHAR(100) NOT NULL,
    fecha_concierto DATE NOT NULL,
    precio          DECIMAL(8,2) NOT NULL,
    CONSTRAINT uq_banda_fecha UNIQUE (nombre_banda, fecha_concierto),
    CONSTRAINT chk_precio CHECK (precio >= 0)
) ENGINE=InnoDB;

CREATE TABLE entradas(
    id_entrada        INT PRIMARY KEY AUTO_INCREMENT,
    fecha_adquisicion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fk_concierto      INT NOT NULL,
    FOREIGN KEY (fk_concierto) REFERENCES conciertos(id_concierto)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =====================================================
-- PROCEDIMIENTOS: CONCIERTOS
-- =====================================================

DROP PROCEDURE IF EXISTS ListaConciertos;
DELIMITER //
CREATE PROCEDURE ListaConciertos()
BEGIN
    -- CASO 0 - Todo OK. Instruccion nuclear
    SELECT * FROM conciertos ORDER BY fecha_concierto;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS DameConcierto;
DELIMITER //
CREATE PROCEDURE DameConcierto(IN _id_concierto INT)
BEGIN
    -- CASO 0 - Todo OK. Instruccion nuclear
    SELECT * FROM conciertos
    WHERE id_concierto = _id_concierto;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaConcierto;
DELIMITER //
CREATE PROCEDURE AltaConcierto(IN _nombre_banda VARCHAR(100),
                               IN _fecha_concierto DATE,
                               IN _precio DECIMAL(8,2),
                               OUT _res INT)
BEGIN
    -- CASO -1 - Nombre de banda vacio
    IF (_nombre_banda IS NULL OR TRIM(_nombre_banda) = '') THEN
        SET _res = -1;
    -- CASO -2 - Fecha vacia
    ELSEIF (_fecha_concierto IS NULL) THEN
        SET _res = -2;
    -- CASO -3 - Fecha anterior o igual a hoy
    ELSEIF (_fecha_concierto <= CURDATE()) THEN
        SET _res = -3;
    -- CASO -4 - Precio vacio o negativo
    ELSEIF (_precio IS NULL OR _precio < 0) THEN
        SET _res = -4;
    -- CASO -5 - Concierto duplicado (misma banda y fecha)
    ELSEIF EXISTS (SELECT 1 FROM conciertos
                   WHERE nombre_banda = TRIM(_nombre_banda)
                     AND fecha_concierto = _fecha_concierto) THEN
        SET _res = -5;
    ELSE
    -- CASO 0 - Todo OK. Instruccion nuclear
        INSERT INTO conciertos(nombre_banda, fecha_concierto, precio)
                        VALUES(TRIM(_nombre_banda), _fecha_concierto, _precio);
        SET _res = 0;
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS ModificarConcierto;
DELIMITER //
CREATE PROCEDURE ModificarConcierto(IN _id_concierto INT,
                                    IN _nombre_banda VARCHAR(100),
                                    IN _fecha_concierto DATE,
                                    IN _precio DECIMAL(8,2),
                                    OUT _res INT)
BEGIN
    -- CASO -1 - Nombre de banda vacio
    IF (_nombre_banda IS NULL OR TRIM(_nombre_banda) = '') THEN
        SET _res = -1;
    -- CASO -2 - Fecha vacia
    ELSEIF (_fecha_concierto IS NULL) THEN
        SET _res = -2;
    -- CASO -3 - Fecha anterior o igual a hoy
    ELSEIF (_fecha_concierto <= CURDATE()) THEN
        SET _res = -3;
    -- CASO -4 - Precio vacio o negativo
    ELSEIF (_precio IS NULL OR _precio < 0) THEN
        SET _res = -4;
    -- CASO -5 - El concierto a modificar no existe
    ELSEIF NOT EXISTS (SELECT 1 FROM conciertos WHERE id_concierto = _id_concierto) THEN
        SET _res = -5;
    -- CASO -6 - Ya existe OTRO concierto con esa banda y fecha
    ELSEIF EXISTS (SELECT 1 FROM conciertos
                   WHERE nombre_banda = TRIM(_nombre_banda)
                     AND fecha_concierto = _fecha_concierto
                     AND id_concierto <> _id_concierto) THEN
        SET _res = -6;
    ELSE
    -- CASO 0 - Todo OK. Instruccion nuclear
        UPDATE conciertos SET nombre_banda = TRIM(_nombre_banda),
                              fecha_concierto = _fecha_concierto,
                              precio = _precio
        WHERE id_concierto = _id_concierto;
        SET _res = 0;
    END IF;
END//
DELIMITER ;

-- =====================================================
-- PROCEDIMIENTOS: ENTRADAS
-- =====================================================

DROP PROCEDURE IF EXISTS ListaEntradas;
DELIMITER //
CREATE PROCEDURE ListaEntradas()
BEGIN
    -- CASO 0 - Todo OK. Entradas con los datos de su concierto
    SELECT e.id_entrada, e.fecha_adquisicion, e.fk_concierto,
           c.nombre_banda, c.fecha_concierto, c.precio
    FROM entradas e
    INNER JOIN conciertos c ON c.id_concierto = e.fk_concierto
    ORDER BY e.id_entrada DESC;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaEntrada;
DELIMITER //
CREATE PROCEDURE AltaEntrada(IN _fk_concierto INT,
                             OUT _res INT)
BEGIN
    -- CASO -1 - Concierto no indicado
    IF (_fk_concierto IS NULL) THEN
        SET _res = -1;
    -- CASO -2 - El concierto no existe
    ELSEIF NOT EXISTS (SELECT 1 FROM conciertos WHERE id_concierto = _fk_concierto) THEN
        SET _res = -2;
    -- CASO -3 - El concierto ya se ha celebrado
    ELSEIF ((SELECT fecha_concierto FROM conciertos
             WHERE id_concierto = _fk_concierto) < CURDATE()) THEN
        SET _res = -3;
    ELSE
    -- CASO 0 - Todo OK. Instruccion nuclear
        INSERT INTO entradas(fecha_adquisicion, fk_concierto)
                      VALUES(NOW(), _fk_concierto);
        SET _res = 0;
    END IF;
END//
DELIMITER ;