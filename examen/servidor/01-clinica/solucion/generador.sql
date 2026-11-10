-- =====================================================
-- BASE DE DATOS: clinica_examen
-- Modelo muy parecido a portal_citasV2
-- =====================================================
CREATE DATABASE IF NOT EXISTS clinica_examen;
USE clinica_examen;

DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS doctores;

CREATE TABLE doctores (
    n_licencia INT PRIMARY KEY,
    nombre     VARCHAR(50) NOT NULL,
    apellidos  VARCHAR(50) NOT NULL
);

CREATE TABLE citas (
    id_cita      INT AUTO_INCREMENT PRIMARY KEY,
    paciente     VARCHAR(50) NOT NULL,
    especialidad ENUM('Medicina General','Pediatría','Dermatología','Traumatología') NOT NULL,
    fecha        DATETIME NOT NULL,
    fk_doctor    INT NOT NULL,
    CONSTRAINT fk_citas_doctor
        FOREIGN KEY (fk_doctor) REFERENCES doctores(n_licencia)
);

-- Datos de prueba
INSERT INTO doctores (n_licencia, nombre, apellidos) VALUES
(1001, 'Ana',  'López'),
(1002, 'Luis', 'Pérez');

-- =====================================================
-- PROCEDIMIENTOS DE DOCTORES
-- =====================================================

DROP PROCEDURE IF EXISTS ListaDoctores;
DELIMITER //
CREATE PROCEDURE ListaDoctores()
BEGIN
    SELECT * FROM doctores ORDER BY n_licencia;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS DameDoctor;
DELIMITER //
CREATE PROCEDURE DameDoctor(IN _licencia INT)
BEGIN
    SELECT * FROM doctores WHERE n_licencia = _licencia;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaDoctor;
DELIMITER //
CREATE PROCEDURE AltaDoctor(
    IN  _licencia INT,
    IN  _nombre   VARCHAR(50),
    IN  _apellidos VARCHAR(50),
    OUT _res      INT
)
BEGIN
    IF (_nombre = '' OR _nombre IS NULL) THEN
        SET _res = -1;                                  -- nombre vacío
    ELSEIF (_apellidos = '' OR _apellidos IS NULL) THEN
        SET _res = -2;                                  -- apellidos vacíos
    ELSEIF EXISTS (SELECT 1 FROM doctores WHERE n_licencia = _licencia) THEN
        SET _res = -3;                                  -- licencia duplicada
    ELSE
        INSERT INTO doctores (n_licencia, nombre, apellidos)
        VALUES (_licencia, _nombre, _apellidos);

        SET _res = 0;                                   -- todo OK
    END IF;
END//
DELIMITER ;

-- =====================================================
-- PROCEDIMIENTOS DE CITAS
-- =====================================================

DROP PROCEDURE IF EXISTS ListaCitas;
DELIMITER //
CREATE PROCEDURE ListaCitas()
BEGIN
    SELECT c.id_cita,
           c.paciente,
           c.especialidad,
           c.fecha,
           c.fk_doctor,
           d.nombre AS doctor_nombre,
           d.apellidos AS doctor_apellidos
    FROM citas c
    JOIN doctores d ON c.fk_doctor = d.n_licencia
    ORDER BY c.fecha;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS AltaCita;
DELIMITER //
CREATE PROCEDURE AltaCita(
    IN  _paciente     VARCHAR(50),
    IN  _especialidad VARCHAR(30),
    IN  _fecha        DATETIME,
    IN  _doctor       INT,
    OUT _res          INT
)
BEGIN
    -- La fecha vacía se valida en PHP para no pasar '' a un DATETIME
    IF (_paciente = '' OR _paciente IS NULL) THEN
        SET _res = -1;                                  -- paciente vacío
    ELSEIF (_especialidad = '' OR _especialidad IS NULL) THEN
        SET _res = -2;                                  -- especialidad vacía
    ELSEIF (_fecha IS NULL) THEN
        SET _res = -3;                                  -- fecha vacía
    ELSEIF (_fecha <= NOW()) THEN
        SET _res = -4;                                  -- fecha pasada
    ELSEIF NOT EXISTS (SELECT 1 FROM doctores WHERE n_licencia = _doctor) THEN
        SET _res = -5;                                  -- doctor inexistente
    ELSE
        INSERT INTO citas (paciente, especialidad, fecha, fk_doctor)
        VALUES (_paciente, _especialidad, _fecha, _doctor);

        SET _res = 0;                                   -- todo OK
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS ModificarCita;
DELIMITER //
CREATE PROCEDURE ModificarCita(
    IN  _id           INT,
    IN  _paciente     VARCHAR(50),
    IN  _especialidad VARCHAR(30),
    IN  _fecha        DATETIME,
    IN  _doctor       INT,
    OUT _res          INT
)
BEGIN
    IF (_paciente = '' OR _paciente IS NULL) THEN
        SET _res = -1;
    ELSEIF (_especialidad = '' OR _especialidad IS NULL) THEN
        SET _res = -2;
    ELSEIF (_fecha IS NULL) THEN
        SET _res = -3;
    ELSEIF (_fecha <= NOW()) THEN
        SET _res = -4;
    ELSEIF NOT EXISTS (SELECT 1 FROM citas WHERE id_cita = _id) THEN
        SET _res = -6;                                  -- cita inexistente
    ELSEIF NOT EXISTS (SELECT 1 FROM doctores WHERE n_licencia = _doctor) THEN
        SET _res = -5;                                  -- doctor inexistente
    ELSE
        UPDATE citas
        SET paciente = _paciente,
            especialidad = _especialidad,
            fecha = _fecha,
            fk_doctor = _doctor
        WHERE id_cita = _id;

        SET _res = 0;
    END IF;
END//
DELIMITER ;

DROP PROCEDURE IF EXISTS BorrarCita;
DELIMITER //
CREATE PROCEDURE BorrarCita(IN _id INT, OUT _res INT)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM citas WHERE id_cita = _id) THEN
        SET _res = -1;                                  -- no existe
    ELSE
        DELETE FROM citas WHERE id_cita = _id;
        SET _res = 0;
    END IF;
END//
DELIMITER ;
