CREATE DATABASE IF NOT EXISTS aerolinea;
USE aerolinea;

DROP TABLE IF EXISTS billetes;
DROP TABLE IF EXISTS viajeros;

CREATE TABLE viajeros (
    documento VARCHAR(20) PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL
);

CREATE TABLE billetes (
    id_billete INT PRIMARY KEY AUTO_INCREMENT,
    destino VARCHAR(80) NOT NULL,
    fecha DATETIME,
    fk_viajero VARCHAR(20) NOT NULL,
    FOREIGN KEY (fk_viajero) REFERENCES viajeros(documento)
);

DROP PROCEDURE IF EXISTS ListaViajeros;
DELIMITER //
CREATE PROCEDURE ListaViajeros()
BEGIN
    SELECT * FROM viajeros;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS DameViajero;
DELIMITER //
CREATE PROCEDURE DameViajero(IN _documento VARCHAR(20))
BEGIN
    SELECT * FROM viajeros WHERE documento = _documento;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaViajero;
DELIMITER //
CREATE PROCEDURE AltaViajero(
    IN _documento VARCHAR(20),
    IN _nombre VARCHAR(50),
    IN _apellidos VARCHAR(50),
    OUT _resultado INT
)
BEGIN
    -- PREGUNTA 3 -- Implementa la comprobacion de error -4,
					-- que mira si el viajero ya existe
					-- para evitar que el INSERT falle
    
    IF (_nombre IS NULL OR TRIM(_nombre) LIKE '') THEN
        SET _resultado = -1;
    ELSEIF (_apellidos IS NULL OR TRIM(_apellidos) LIKE '') THEN
        SET _resultado = -2;
    ELSEIF (_documento IS NULL OR TRIM(_documento) LIKE '') THEN
        SET _resultado = -3;
	elseif exists(select * from viajeros where documento = _documento) then
		set _resultado = -4;
    ELSE
        INSERT INTO viajeros (documento, nombre, apellidos)
        VALUES (_documento, _nombre, _apellidos);
        SET _resultado = 0;
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS ListaBilletes;
DELIMITER //
CREATE PROCEDURE ListaBilletes()
BEGIN
    SELECT * FROM billetes;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaBillete;
DELIMITER //
CREATE PROCEDURE AltaBillete(
    IN _destino VARCHAR(80),
    IN _fecha DATETIME,
    IN _documento_viajero VARCHAR(20),
    OUT _resultado INT
)
BEGIN
    IF (_destino IS NULL OR TRIM(_destino) = '') THEN
        SET _resultado = -1;
    ELSEIF (_fecha IS NULL OR _fecha LIKE "") THEN
        SET _resultado = -2;
    ELSEIF (_fecha <= NOW()) THEN
        SET _resultado = -3;
    ELSE
        INSERT INTO billetes (destino, fecha, fk_viajero)
        VALUES (_destino, _fecha, _documento_viajero);
        SET _resultado = 0;
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS ModificarBillete;
DELIMITER //
CREATE PROCEDURE ModificarBillete(
    IN _id_billete INT,
    IN _destino VARCHAR(80),
    IN _fecha DATETIME,
    IN _documento_viajero VARCHAR(20),
    OUT _resultado INT
)
BEGIN
    IF (_destino IS NULL OR TRIM(_destino) = '') THEN
        SET _resultado = -1;
    ELSEIF (_fecha IS NULL OR _fecha LIKE "") THEN
        SET _resultado = -2;
    ELSEIF (_fecha <= NOW()) THEN
        SET _resultado = -3;
    ELSE
		-- PREGUNTA 4 - Coloca aquí la instrucción nuclear que actualizaria el billete
		update billete set _id_billete = _id_billete, _destino = _destino, _fecha = _fecha, _documento_viajero = _documento_viajero
			where _id_billete = _id_billete;
        SET _resultado = 0;
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS CancelarTodosBilletes;
DELIMITER //
CREATE PROCEDURE CancelarTodosBilletes()
BEGIN
    DELETE FROM billetes;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS BorrarBillete;
DELIMITER //
-- PREGUNTA 8 - Genera un Procedimiento Almacenado que borre un billete dando su id
-- CREATE PROCEDURE BorrarBillete(.....)
CREATE PROCEDURE BorrarBillete(
	in id_billete int
)
BEGIN
        delete from billetes where id_billete = id_billete;
END//
DELIMITER ;
    

