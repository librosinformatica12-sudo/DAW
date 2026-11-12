-- =====================================================
-- BASE DE DATOS: biblioteca_examen
-- Libros + socios + préstamos (JOIN y UPDATE)
-- =====================================================
CREATE DATABASE IF NOT EXISTS biblioteca_examen;
USE biblioteca_examen;

DROP TABLE IF EXISTS prestamos;
DROP TABLE IF EXISTS libros;
DROP TABLE IF EXISTS socios;

CREATE TABLE libros (
    id_libro   INT AUTO_INCREMENT PRIMARY KEY,
    titulo     VARCHAR(80) NOT NULL,
    autor      VARCHAR(60) NOT NULL,
    ejemplares INT NOT NULL DEFAULT 1
);

CREATE TABLE socios (
    id_socio INT AUTO_INCREMENT PRIMARY KEY,
    nombre   VARCHAR(60) NOT NULL
);

CREATE TABLE prestamos (
    id_prestamo       INT AUTO_INCREMENT PRIMARY KEY,
    fecha_prestamo    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion  DATETIME NULL,
    devuelto          TINYINT(1) NOT NULL DEFAULT 0,
    fk_libro          INT NOT NULL,
    fk_socio          INT NOT NULL,
    CONSTRAINT fk_prestamo_libro
        FOREIGN KEY (fk_libro) REFERENCES libros(id_libro),
    CONSTRAINT fk_prestamo_socio
        FOREIGN KEY (fk_socio) REFERENCES socios(id_socio)
);

INSERT INTO libros (titulo, autor, ejemplares) VALUES
('El Quijote',        'Cervantes', 3),
('La Regenta',        'Clarín',    1),
('Cien años de soledad', 'García Márquez', 2);

INSERT INTO socios (nombre) VALUES
('Ana'),
('Luis');

-- =====================================================
-- LIBROS
-- =====================================================

DROP PROCEDURE IF EXISTS ListaLibros;
DELIMITER //
CREATE PROCEDURE ListaLibros()
BEGIN
    SELECT l.id_libro,
           l.titulo,
           l.autor,
           l.ejemplares,
           (l.ejemplares - (SELECT COUNT(*)
                              FROM prestamos p
                             WHERE p.fk_libro = l.id_libro
                               AND p.devuelto = 0)) AS disponibles
    FROM libros l
    ORDER BY l.titulo;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaLibro;
DELIMITER //
CREATE PROCEDURE AltaLibro(
    IN  _titulo     VARCHAR(80),
    IN  _autor      VARCHAR(60),
    IN  _ejemplares INT,
    OUT _res        INT
)
BEGIN
    IF (_titulo = '' OR _titulo IS NULL) THEN
        SET _res = -1;                                  -- título vacío
    ELSEIF (_autor = '' OR _autor IS NULL) THEN
        SET _res = -2;                                  -- autor vacío
    ELSEIF (_ejemplares <= 0) THEN
        SET _res = -3;                                  -- ejemplares no válidos
    ELSE
        INSERT INTO libros (titulo, autor, ejemplares)
        VALUES (_titulo, _autor, _ejemplares);

        SET _res = 0;
    END IF;
END//
DELIMITER ;

-- =====================================================
-- SOCIOS
-- =====================================================

DROP PROCEDURE IF EXISTS AltaSocio;
DELIMITER //
CREATE PROCEDURE AltaSocio(IN _nombre VARCHAR(60), OUT _res INT)
BEGIN
    IF (_nombre = '' OR _nombre IS NULL) THEN
        SET _res = -1;                                  -- nombre vacío
    ELSE
        INSERT INTO socios (nombre) VALUES (_nombre);
        SET _res = 0;
    END IF;
END//
DELIMITER ;

-- =====================================================
-- PRÉSTAMOS
-- =====================================================

DROP PROCEDURE IF EXISTS ListaPrestamos;
DELIMITER //
CREATE PROCEDURE ListaPrestamos()
BEGIN
    SELECT p.id_prestamo,
           p.fecha_prestamo,
           l.titulo,
           s.nombre AS socio
    FROM prestamos p
    JOIN libros l ON p.fk_libro = l.id_libro
    JOIN socios s ON p.fk_socio = s.id_socio
    WHERE p.devuelto = 0
    ORDER BY p.fecha_prestamo;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS PrestarLibro;
DELIMITER //
CREATE PROCEDURE PrestarLibro(
    IN  _libro INT,
    IN  _socio INT,
    OUT _res   INT
)
BEGIN
    DECLARE _disponibles INT DEFAULT 0;

    IF NOT EXISTS (SELECT 1 FROM libros WHERE id_libro = _libro) THEN
        SET _res = -1;                                  -- libro no existe
    ELSEIF NOT EXISTS (SELECT 1 FROM socios WHERE id_socio = _socio) THEN
        SET _res = -2;                                  -- socio no existe
    ELSE
        SELECT (l.ejemplares - (SELECT COUNT(*)
                                  FROM prestamos p
                                 WHERE p.fk_libro = l.id_libro
                                   AND p.devuelto = 0))
          INTO _disponibles
          FROM libros l
         WHERE l.id_libro = _libro;

        IF (_disponibles <= 0) THEN
            SET _res = -3;                              -- sin ejemplares
        ELSE
            INSERT INTO prestamos (fk_libro, fk_socio)
            VALUES (_libro, _socio);

            SET _res = 0;
        END IF;
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS DevolverLibro;
DELIMITER //
CREATE PROCEDURE DevolverLibro(IN _id_prestamo INT, OUT _res INT)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM prestamos WHERE id_prestamo = _id_prestamo) THEN
        SET _res = -1;                                  -- préstamo no existe
    ELSEIF (SELECT devuelto FROM prestamos WHERE id_prestamo = _id_prestamo) = 1 THEN
        SET _res = -2;                                  -- ya devuelto
    ELSE
        UPDATE prestamos
        SET devuelto = 1,
            fecha_devolucion = NOW()
        WHERE id_prestamo = _id_prestamo;

        SET _res = 0;
    END IF;
END//
DELIMITER ;
