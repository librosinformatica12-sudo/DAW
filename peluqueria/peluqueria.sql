-- =========================================================
-- PROYECTO PELUQUERIA - BASE DE DATOS Y PROCEDIMIENTOS
-- Importar este archivo desde phpMyAdmin.
-- =========================================================

CREATE DATABASE IF NOT EXISTS peluqueria
  CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
USE peluqueria;

CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    telefono VARCHAR(15) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS peluqueros (
    id_peluquero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    especialidad VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservas (
    id_reserva INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_peluquero INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    servicio VARCHAR(50) NOT NULL,
    CONSTRAINT fk_reserva_cliente FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente),
    CONSTRAINT fk_reserva_peluquero FOREIGN KEY (id_peluquero)
        REFERENCES peluqueros(id_peluquero)
) ENGINE=InnoDB;

-- Datos de prueba (solo se insertan si las tablas están vacías)
INSERT INTO clientes(nombre, telefono)
SELECT 'Ana López', '600111222'
WHERE NOT EXISTS (SELECT 1 FROM clientes);

INSERT INTO peluqueros(nombre, especialidad)
SELECT 'Laura García', 'Color'
WHERE NOT EXISTS (SELECT 1 FROM peluqueros);

-- =========================================================
-- CLIENTES
-- =========================================================
DELIMITER $$

DROP PROCEDURE IF EXISTS AltaCliente$$
CREATE PROCEDURE AltaCliente(
    IN _nombre VARCHAR(50),
    IN _telefono VARCHAR(15),
    OUT _res INT
)
BEGIN
    IF (_nombre IS NULL OR TRIM(_nombre) = '') THEN
        SET _res = -2;
    ELSEIF (_telefono IS NULL OR TRIM(_telefono) = '') THEN
        SET _res = -3;
    ELSE
        INSERT INTO clientes(nombre, telefono)
        VALUES (_nombre, _telefono);
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS BajaCliente$$
CREATE PROCEDURE BajaCliente(
    IN _id_cliente INT,
    OUT _res INT
)
BEGIN
    IF (_id_cliente IS NULL OR _id_cliente < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM clientes WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -3;
    ELSEIF EXISTS (
        SELECT 1 FROM reservas WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -4;
    ELSE
        DELETE FROM clientes WHERE id_cliente = _id_cliente;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ModificarCliente$$
CREATE PROCEDURE ModificarCliente(
    IN _id_cliente INT,
    IN _nombre VARCHAR(50),
    IN _telefono VARCHAR(15),
    OUT _res INT
)
BEGIN
    IF (_id_cliente IS NULL OR _id_cliente < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM clientes WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -3;
    ELSEIF (_nombre IS NULL OR TRIM(_nombre) = '') THEN
        SET _res = -4;
    ELSEIF (_telefono IS NULL OR TRIM(_telefono) = '') THEN
        SET _res = -5;
    ELSE
        UPDATE clientes
        SET nombre = _nombre, telefono = _telefono
        WHERE id_cliente = _id_cliente;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarClientes$$
CREATE PROCEDURE ConsultarClientes(OUT _res INT)
BEGIN
    SELECT * FROM clientes ORDER BY nombre ASC;
    SET _res = 0;
END$$

DROP PROCEDURE IF EXISTS ConsultarClientePorID$$
CREATE PROCEDURE ConsultarClientePorID(
    IN _id_cliente INT,
    OUT _res INT
)
BEGIN
    IF (_id_cliente IS NULL OR _id_cliente < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM clientes WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -3;
    ELSE
        SELECT * FROM clientes WHERE id_cliente = _id_cliente;
        SET _res = 0;
    END IF;
END$$

-- =========================================================
-- PELUQUEROS
-- =========================================================
DROP PROCEDURE IF EXISTS AltaPeluquero$$
CREATE PROCEDURE AltaPeluquero(
    IN _nombre VARCHAR(50),
    IN _especialidad VARCHAR(50),
    OUT _res INT
)
BEGIN
    IF (_nombre IS NULL OR TRIM(_nombre) = '') THEN
        SET _res = -2;
    ELSEIF (_especialidad IS NULL OR TRIM(_especialidad) = '') THEN
        SET _res = -3;
    ELSE
        INSERT INTO peluqueros(nombre, especialidad)
        VALUES (_nombre, _especialidad);
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS BajaPeluquero$$
CREATE PROCEDURE BajaPeluquero(
    IN _id_peluquero INT,
    OUT _res INT
)
BEGIN
    IF (_id_peluquero IS NULL OR _id_peluquero < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -3;
    ELSEIF EXISTS (
        SELECT 1 FROM reservas WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -4;
    ELSE
        DELETE FROM peluqueros WHERE id_peluquero = _id_peluquero;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ModificarPeluquero$$
CREATE PROCEDURE ModificarPeluquero(
    IN _id_peluquero INT,
    IN _nombre VARCHAR(50),
    IN _especialidad VARCHAR(50),
    OUT _res INT
)
BEGIN
    IF (_id_peluquero IS NULL OR _id_peluquero < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -3;
    ELSEIF (_nombre IS NULL OR TRIM(_nombre) = '') THEN
        SET _res = -4;
    ELSEIF (_especialidad IS NULL OR TRIM(_especialidad) = '') THEN
        SET _res = -5;
    ELSE
        UPDATE peluqueros
        SET nombre = _nombre, especialidad = _especialidad
        WHERE id_peluquero = _id_peluquero;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarPeluqueros$$
CREATE PROCEDURE ConsultarPeluqueros(OUT _res INT)
BEGIN
    SELECT * FROM peluqueros ORDER BY nombre ASC;
    SET _res = 0;
END$$

DROP PROCEDURE IF EXISTS ConsultarPeluqueroPorID$$
CREATE PROCEDURE ConsultarPeluqueroPorID(
    IN _id_peluquero INT,
    OUT _res INT
)
BEGIN
    IF (_id_peluquero IS NULL OR _id_peluquero < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -3;
    ELSE
        SELECT * FROM peluqueros WHERE id_peluquero = _id_peluquero;
        SET _res = 0;
    END IF;
END$$

-- =========================================================
-- RESERVAS
-- =========================================================
DROP PROCEDURE IF EXISTS AltaReserva$$
CREATE PROCEDURE AltaReserva(
    IN _id_cliente INT,
    IN _id_peluquero INT,
    IN _fecha_hora DATETIME,
    IN _servicio VARCHAR(50),
    OUT _res INT
)
BEGIN
    IF (_id_cliente IS NULL OR _id_cliente < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM clientes WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -3;
    ELSEIF (_id_peluquero IS NULL OR _id_peluquero < 1) THEN
        SET _res = -4;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -5;
    ELSEIF (_fecha_hora IS NULL) THEN
        SET _res = -6;
    ELSEIF (_servicio IS NULL OR TRIM(_servicio) = '') THEN
        SET _res = -7;
    ELSEIF EXISTS (
        SELECT 1 FROM reservas
        WHERE id_peluquero = _id_peluquero
          AND fecha_hora = _fecha_hora
    ) THEN
        SET _res = -8;
    ELSE
        INSERT INTO reservas(id_cliente, id_peluquero, fecha_hora, servicio)
        VALUES (_id_cliente, _id_peluquero, _fecha_hora, _servicio);
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS BajaReserva$$
CREATE PROCEDURE BajaReserva(
    IN _id_reserva INT,
    OUT _res INT
)
BEGIN
    IF (_id_reserva IS NULL OR _id_reserva < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM reservas WHERE id_reserva = _id_reserva
    ) THEN
        SET _res = -3;
    ELSE
        DELETE FROM reservas WHERE id_reserva = _id_reserva;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ModificarReserva$$
CREATE PROCEDURE ModificarReserva(
    IN _id_reserva INT,
    IN _id_cliente INT,
    IN _id_peluquero INT,
    IN _fecha_hora DATETIME,
    IN _servicio VARCHAR(50),
    OUT _res INT
)
BEGIN
    IF (_id_reserva IS NULL OR _id_reserva < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM reservas WHERE id_reserva = _id_reserva
    ) THEN
        SET _res = -3;
    ELSEIF (_id_cliente IS NULL OR NOT EXISTS (
        SELECT 1 FROM clientes WHERE id_cliente = _id_cliente
    )) THEN
        SET _res = -4;
    ELSEIF (_id_peluquero IS NULL OR NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    )) THEN
        SET _res = -5;
    ELSEIF (_fecha_hora IS NULL) THEN
        SET _res = -6;
    ELSEIF (_servicio IS NULL OR TRIM(_servicio) = '') THEN
        SET _res = -7;
    ELSEIF EXISTS (
        SELECT 1 FROM reservas
        WHERE id_peluquero = _id_peluquero
          AND fecha_hora = _fecha_hora
          AND id_reserva <> _id_reserva
    ) THEN
        SET _res = -8;
    ELSE
        UPDATE reservas
        SET id_cliente = _id_cliente,
            id_peluquero = _id_peluquero,
            fecha_hora = _fecha_hora,
            servicio = _servicio
        WHERE id_reserva = _id_reserva;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservas$$
CREATE PROCEDURE ConsultarReservas(OUT _res INT)
BEGIN
    SELECT * FROM reservas ORDER BY fecha_hora ASC;
    SET _res = 0;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservaPorID$$
CREATE PROCEDURE ConsultarReservaPorID(
    IN _id_reserva INT,
    OUT _res INT
)
BEGIN
    IF (_id_reserva IS NULL OR _id_reserva < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM reservas WHERE id_reserva = _id_reserva
    ) THEN
        SET _res = -3;
    ELSE
        SELECT * FROM reservas WHERE id_reserva = _id_reserva;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservasPorCliente$$
CREATE PROCEDURE ConsultarReservasPorCliente(
    IN _id_cliente INT,
    OUT _res INT
)
BEGIN
    IF (_id_cliente IS NULL OR _id_cliente < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM clientes WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -3;
    ELSE
        SELECT * FROM reservas WHERE id_cliente = _id_cliente
        ORDER BY fecha_hora ASC;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservasPorPeluquero$$
CREATE PROCEDURE ConsultarReservasPorPeluquero(
    IN _id_peluquero INT,
    OUT _res INT
)
BEGIN
    IF (_id_peluquero IS NULL OR _id_peluquero < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -3;
    ELSE
        SELECT * FROM reservas WHERE id_peluquero = _id_peluquero
        ORDER BY fecha_hora ASC;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservasPorFecha$$
CREATE PROCEDURE ConsultarReservasPorFecha(
    IN _fecha DATE,
    OUT _res INT
)
BEGIN
    IF (_fecha IS NULL) THEN
        SET _res = -2;
    ELSE
        SELECT * FROM reservas
        WHERE DATE(fecha_hora) = _fecha
        ORDER BY fecha_hora ASC;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservasConJOIN$$
CREATE PROCEDURE ConsultarReservasConJOIN(OUT _res INT)
BEGIN
    SELECT
        reservas.id_reserva,
        clientes.nombre AS cliente,
        clientes.telefono,
        peluqueros.nombre AS peluquero,
        peluqueros.especialidad,
        reservas.fecha_hora,
        reservas.servicio
    FROM reservas
    INNER JOIN clientes
        ON reservas.id_cliente = clientes.id_cliente
    INNER JOIN peluqueros
        ON reservas.id_peluquero = peluqueros.id_peluquero
    ORDER BY reservas.fecha_hora ASC;
    SET _res = 0;
END$$

DROP PROCEDURE IF EXISTS ConsultarReservasEntreFechas$$
CREATE PROCEDURE ConsultarReservasEntreFechas(
    IN _fecha_inicio DATETIME,
    IN _fecha_fin DATETIME,
    OUT _res INT
)
BEGIN
    IF (_fecha_inicio IS NULL OR _fecha_fin IS NULL) THEN
        SET _res = -2;
    ELSEIF (_fecha_inicio > _fecha_fin) THEN
        SET _res = -3;
    ELSE
        SELECT * FROM reservas
        WHERE fecha_hora BETWEEN _fecha_inicio AND _fecha_fin
        ORDER BY fecha_hora ASC;
        SET _res = 0;
    END IF;
END$$

DROP PROCEDURE IF EXISTS ContarReservas$$
CREATE PROCEDURE ContarReservas(OUT _res INT)
BEGIN
    SELECT COUNT(*) AS total FROM reservas;
    SET _res = 0;
END$$

DROP PROCEDURE IF EXISTS ConsultarDisponibilidadPeluquero$$
CREATE PROCEDURE ConsultarDisponibilidadPeluquero(
    IN _id_peluquero INT,
    IN _fecha_hora DATETIME,
    OUT _res INT
)
BEGIN
    IF (_id_peluquero IS NULL OR _id_peluquero < 1) THEN
        SET _res = -2;
    ELSEIF NOT EXISTS (
        SELECT 1 FROM peluqueros WHERE id_peluquero = _id_peluquero
    ) THEN
        SET _res = -3;
    ELSEIF (_fecha_hora IS NULL) THEN
        SET _res = -4;
    ELSE
        SELECT *
        FROM reservas
        WHERE id_peluquero = _id_peluquero
          AND fecha_hora = _fecha_hora;
        SET _res = 0;
    END IF;
END$$

DELIMITER ;

-- Prueba manual:
-- CALL AltaReserva(1, 1, '2026-11-15 10:00:00', 'Corte', @res);
-- SELECT @res;
-- CALL ConsultarReservasConJOIN(@res);
-- SELECT @res;
