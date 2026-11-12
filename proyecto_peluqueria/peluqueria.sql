-- =========================================================
-- PROYECTO PELUQUERIA - BASE DE DATOS Y PROCEDIMIENTOS
-- Importar este archivo desde phpMyAdmin.
-- =========================================================

-- [EXAMEN] Tres bloques: 1) CREATE DATABASE/TABLE  2) INSERT de datos de prueba
-- 3) CREATE PROCEDURE. Lo que suelen quitar: un campo o una tabla, una
-- clave ajena, un IF del procedimiento o un procedimiento completo.
--
-- Crea la BD solo si no existe (IF NOT EXISTS). utf8mb4 permite tildes y
-- emojis; spanish_ci ordena y compara según el español sin distinguir mayúsculas.
CREATE DATABASE IF NOT EXISTS peluqueria
  CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
-- USE: selecciona la BD sobre la que trabajarán las siguientes sentencias.
USE peluqueria;

-- TABLA CLIENTES. id_cliente es clave primaria (PRIMARY KEY, no se repite)
-- y AUTO_INCREMENT (MySQL le asigna 1, 2, 3...). NOT NULL = obligatorio.
-- ENGINE=InnoDB es necesario para poder usar claves ajenas (FOREIGN KEY).
CREATE TABLE IF NOT EXISTS clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    telefono VARCHAR(15) NOT NULL
) ENGINE=InnoDB;

-- TABLA PELUQUEROS: misma idea que clientes, con especialidad en lugar de teléfono.
CREATE TABLE IF NOT EXISTS peluqueros (
    id_peluquero INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    especialidad VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- TABLA RESERVAS (tabla hija). Relaciona un cliente con un peluquero en una
-- fecha. Tipos de dato: DATETIME guarda fecha y hora; INT los ids.
-- CLAVES AJENAS (FOREIGN KEY): id_cliente solo puede tener valores que existan
-- en clientes(id_cliente), y lo mismo con peluqueros. Por eso no se puede
-- borrar un cliente o peluquero con reservas (lo controlan las bajas).
-- Sintaxis: CONSTRAINT nombre FOREIGN KEY (columna_local) REFERENCES tabla(columna).
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
-- INSERT ... SELECT ... WHERE NOT EXISTS: inserta la fila solo si la tabla
-- está vacía, así reimportar el archivo no duplica los datos.
INSERT INTO clientes(nombre, telefono)
SELECT 'Ana López', '600111222'
WHERE NOT EXISTS (SELECT 1 FROM clientes);

INSERT INTO peluqueros(nombre, especialidad)
SELECT 'Laura García', 'Color'
WHERE NOT EXISTS (SELECT 1 FROM peluqueros);

-- =========================================================
-- CLIENTES
-- =========================================================
-- DELIMITER: normalmente ; termina una sentencia, pero dentro de un procedimiento
-- hay muchos ; internos. Se cambia el delimitador a doble dólar para que MySQL
-- no corte el procedimiento por la mitad. Al final se restaura a ;
--
-- ESTRUCTURA COMÚN de todos los procedimientos:
--   DROP PROCEDURE IF EXISTS X;  -> lo borra si ya existía (permite reimportar)
--   CREATE PROCEDURE X(IN _datos..., OUT _res INT)
--   BEGIN ... END
--   IN = parámetro de entrada. OUT _res = código de resultado que lee PHP
--   (PHP lo recibe como @resultado). 0 = correcto, negativo = error.
--   Dentro: cadena IF / ELSEIF de validaciones y, si todo va bien, el ELSE
--   ejecuta la operación real (INSERT / UPDATE / DELETE / SELECT).
--
-- TRUCO DE EXAMEN: los parámetros empiezan por _ (_id_cliente) para que no
-- coincidan con el nombre de la columna. Si se llamaran igual, un
-- WHERE id_cliente = id_cliente sería siempre verdadero y afectaría a TODAS las filas.
DELIMITER $$

-- ALTA CLIENTE  (INSERT)    códigos _res:
--   0 = alta correcta   -2 = nombre vacío   -3 = teléfono vacío
DROP PROCEDURE IF EXISTS AltaCliente$$
CREATE PROCEDURE AltaCliente(
    IN _nombre VARCHAR(50),
    IN _telefono VARCHAR(15),
    OUT _res INT
)
BEGIN
    -- Validación: IS NULL (sin valor) o TRIM(...) igual a cadena vacía (solo espacios) -> error -2.
    -- El orden importa: se comprueban los errores de arriba abajo y se detiene en el
    -- primero que se cumple.
    IF (_nombre IS NULL OR TRIM(_nombre) = '') THEN
        SET _res = -2;
    ELSEIF (_telefono IS NULL OR TRIM(_telefono) = '') THEN
        SET _res = -3;
    ELSE
        -- ELSE = todas las validaciones pasaron: se hace el INSERT y se devuelve 0.
        -- No se indica id_cliente porque es AUTO_INCREMENT.
        INSERT INTO clientes(nombre, telefono)
        VALUES (_nombre, _telefono);
        SET _res = 0;
    END IF;
END$$

-- BAJA CLIENTE  (DELETE)    códigos _res:
--   0 = borrado   -2 = id no válido   -3 = no existe   -4 = tiene reservas
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
    -- EXISTS (SELECT 1 ...) = ¿hay alguna fila? Aquí: ¿el cliente tiene reservas?
    -- Si las tiene no se borra (la clave ajena daría error) y se devuelve -4.
    -- NOT EXISTS es lo contrario (se usa antes para comprobar que el cliente existe).
    ELSEIF EXISTS (
        SELECT 1 FROM reservas WHERE id_cliente = _id_cliente
    ) THEN
        SET _res = -4;
    ELSE
        DELETE FROM clientes WHERE id_cliente = _id_cliente;
        SET _res = 0;
    END IF;
END$$

-- MODIFICAR CLIENTE  (UPDATE)    códigos _res:
--   0 = ok   -2 = id no válido   -3 = no existe   -4 = nombre vacío   -5 = teléfono vacío
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
        -- UPDATE ... SET columna = valor ... WHERE id: el WHERE es OBLIGATORIO;
        -- sin él se modificarían todos los clientes.
        UPDATE clientes
        SET nombre = _nombre, telefono = _telefono
        WHERE id_cliente = _id_cliente;
        SET _res = 0;
    END IF;
END$$

-- CONSULTAR CLIENTES  (SELECT): sin parámetros IN, solo OUT. Devuelve todas las
-- filas ordenadas por nombre (ORDER BY ... ASC = ascendente). _res siempre 0.
-- Este SELECT es el que PHP recoge con fetch_all (por eso el 4º argumento true).
DROP PROCEDURE IF EXISTS ConsultarClientes$$
CREATE PROCEDURE ConsultarClientes(OUT _res INT)
BEGIN
    SELECT * FROM clientes ORDER BY nombre ASC;
    SET _res = 0;
END$$

-- CONSULTAR CLIENTE POR ID: -2 = id no válido, -3 = no existe, 0 = ok (devuelve 1 fila).
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
-- ALTA PELUQUERO: 0 = ok   -2 = nombre vacío   -3 = especialidad vacía
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

-- BAJA PELUQUERO: 0 = ok  -2 = id no válido  -3 = no existe  -4 = tiene reservas
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

-- MODIFICAR PELUQUERO: 0 = ok  -2 = id no válido  -3 = no existe
--   -4 = nombre vacío  -5 = especialidad vacía
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

-- CONSULTAR PELUQUEROS: todos, ordenados por nombre.
DROP PROCEDURE IF EXISTS ConsultarPeluqueros$$
CREATE PROCEDURE ConsultarPeluqueros(OUT _res INT)
BEGIN
    SELECT * FROM peluqueros ORDER BY nombre ASC;
    SET _res = 0;
END$$

-- CONSULTAR PELUQUERO POR ID: -2 id no válido, -3 no existe, 0 ok.
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
-- ALTA RESERVA (INSERT). Es el procedimiento con más validaciones:
--   -2 id_cliente no válido     -3 cliente no existe
--   -4 id_peluquero no válido   -5 peluquero no existe
--   -6 fecha nula               -7 servicio vacío
--   -8 el peluquero YA tiene una reserva en esa fecha y hora exacta
--    0 reserva creada
-- IN _fecha_hora DATETIME: aunque PHP la envíe como texto, MySQL la convierte.
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
    -- CONTROL DE DISPONIBILIDAD: busca si ese peluquero ya tiene una reserva con
    -- la misma fecha y hora exactas. Si existe -> error -8 (doble reserva).
    -- Limitación: solo compara la hora exacta, no solapamientos por duración.
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

-- BAJA RESERVA (DELETE): -2 id no válido, -3 no existe, 0 borrada.
-- Las reservas se pueden borrar siempre: son tablas hijas, nadie depende de ellas.
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

-- MODIFICAR RESERVA (UPDATE). Mismas validaciones que el alta, con estas
-- diferencias de numeración:
--   -2 id_reserva no válido   -3 la reserva no existe
--   -4 cliente no válido o inexistente   -5 peluquero no válido o inexistente
--   -6 fecha nula  -7 servicio vacío  -8 hueco ocupado   0 ok
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
    -- Igual que en el alta, pero añade AND id_reserva <> _id_reserva: <> significa
    -- DISTINTO. Así la propia reserva que se está editando no cuenta como ocupada,
    -- y se puede guardar sin cambiar la hora.
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

-- CONSULTAR RESERVAS: todas las columnas de reservas (ids, sin nombres), por fecha ascendente.
DROP PROCEDURE IF EXISTS ConsultarReservas$$
CREATE PROCEDURE ConsultarReservas(OUT _res INT)
BEGIN
    SELECT * FROM reservas ORDER BY fecha_hora ASC;
    SET _res = 0;
END$$

-- CONSULTAR RESERVA POR ID: -2 id no válido, -3 no existe, 0 ok (1 fila).
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

-- RESERVAS DE UN CLIENTE: -2 id no válido, -3 cliente no existe, 0 ok.
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

-- RESERVAS DE UN PELUQUERO: -2 id no válido, -3 peluquero no existe, 0 ok.
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

-- RESERVAS DE UN DÍA: el parámetro es DATE (solo fecha).
-- DATE(fecha_hora) quita la parte de la hora para compararla con _fecha.
-- -2 = fecha nula, 0 = ok.
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

-- CONSULTA CON JOIN: es la que muestra la tabla de gestionar_reservas.php.
-- JOIN une tablas por una columna común (aquí por las claves ajenas).
-- INNER JOIN solo devuelve las reservas que tienen cliente Y peluquero asociados.
-- AS crea un ALIAS de columna (cliente, peluquero): PHP lee $fila["cliente"].
-- Hacen falta alias porque clientes.nombre y peluqueros.nombre se llamarían igual.
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
    -- Tabla principal; luego se unen las otras dos con INNER JOIN ... ON condición.
    FROM reservas
    -- ON dice cómo se relacionan: la reserva guarda el id del cliente y
    -- coincide con el id_cliente de la tabla clientes.
    INNER JOIN clientes
        ON reservas.id_cliente = clientes.id_cliente
    INNER JOIN peluqueros
        ON reservas.id_peluquero = peluqueros.id_peluquero
    ORDER BY reservas.fecha_hora ASC;
    SET _res = 0;
END$$

-- RESERVAS ENTRE DOS FECHAS: BETWEEN ... AND incluye ambos extremos.
-- -2 = alguna fecha nula   -3 = fecha inicio mayor que fin   0 = ok
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

-- CONTAR RESERVAS: COUNT(*) cuenta filas. AS total da nombre a la columna
-- (PHP lo lee en $r["datos"][0]["total"]).
DROP PROCEDURE IF EXISTS ContarReservas$$
CREATE PROCEDURE ContarReservas(OUT _res INT)
BEGIN
    SELECT COUNT(*) AS total FROM reservas;
    SET _res = 0;
END$$

-- DISPONIBILIDAD DE UN PELUQUERO: devuelve las reservas de ese peluquero a esa
-- hora exacta. Sin filas = libre, con filas = ocupado.
-- -2 id no válido, -3 peluquero no existe, -4 fecha nula, 0 ok.
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

-- Se restaura el delimitador normal (punto y coma).
DELIMITER ;

-- Prueba manual:
    -- Para probar a mano en phpMyAdmin (pestaña SQL): CALL con los valores y, en
    -- el último parámetro, una variable de sesión @res donde queda el código.
    -- Luego SELECT @res; muestra ese código. Es lo mismo que hace PHP internamente.
-- CALL AltaReserva(1, 1, '2026-11-15 10:00:00', 'Corte', @res);
-- SELECT @res;
-- CALL ConsultarReservasConJOIN(@res);
-- SELECT @res;
