-- ============================================================
-- generador_clinia.sql (portal_citasV2 - versión buena)
--
-- ÍNDICE RÁPIDO (Ctrl+F):
--   BASE DE DATOS
--   TABLA doctores
--   TABLA citasMedicas
--   PROCEDURE ListaDoctores
--   PROCEDURE DameDoctor
--   PROCEDURE AltaDoctor
--   PROCEDURE ListaCitas
--   PROCEDURE AltaCita
--   PROCEDURE ModificarCita
--
-- Orden típico: crear BD -> crear tablas -> crear procedures.
-- ============================================================

-- ===== BASE DE DATOS =====
-- SCRIPT de Generacion de BBDD en MySQL
CREATE DATABASE IF NOT EXISTS Clinica_PROFE;
USE Clinica_PROFE;
-- DROPS

DROP TABLE IF EXISTS citasMedicas;
DROP TABLE IF EXISTS doctores;
-- ===== TABLA doctores =====
-- Tabla padre: cada doctor tiene una licencia única (primary key).
-- CREATE TABLES
CREATE TABLE doctores(n_licencia INT primary key,
						nombre_doctor VARCHAR(50) NOT NULL,
                        apellidos_doctor VARCHAR(50) NOT NULL
                        );
-- ===== TABLA citasMedicas =====
-- Tabla hija: fk_doctor apunta a doctores(n_licencia).
CREATE TABLE citasMedicas(id_cita INT primary key auto_increment,
						   nombre_paciente VARCHAR(50) NOT NULL,
                           especialidad  ENUM('Medicina General',
											'Pediatría',
                                            'Traumatología',
                                            'Dermatología'),
						   fecha DATETIME,
                           fk_doctor INT NOT NULL,
			FOREIGN KEY (fk_doctor) REFERENCES doctores(n_licencia)
                           );

-- ALTER TABLE citasMedicas ADD COLUMN(fk_doctor INT NOT NULL);
-- ALTER TABLE citasMedicas ADD CONSTRAINT FOREIGN FOREIGN KEY (fk_doctor) REFERENCES doctores(n_licencia);

-- ===== PROCEDURE ListaDoctores =====
-- Devuelve todos los doctores (SELECT *).
DROP PROCEDURE IF EXISTS ListaDoctores;
DELIMITER //
CREATE PROCEDURE ListaDoctores()
BEGIN
	-- CASO 0 - Todo OK. Instruccion nuclear 
		SELECT * FROM doctores;
END//
DELIMITER ;

-- ===== PROCEDURE DameDoctor =====
-- Devuelve los datos de un doctor concreto por licencia.
DROP PROCEDURE IF EXISTS DameDoctor;
DELIMITER //
CREATE PROCEDURE DameDoctor(IN _n_licencia INT)
BEGIN
	-- CASO 0 - Todo OK. Instruccion nuclear 
		SELECT * FROM doctores
        WHERE n_licencia = _n_licencia;
END//
DELIMITER ;

-- ===== PROCEDURE AltaDoctor =====
-- Parámetros IN + OUT _res. Casos: -1 nombre, -2 apellidos, -3 duplicado, 0 OK.
DROP PROCEDURE IF EXISTS AltaDoctor;
DELIMITER //
CREATE PROCEDURE AltaDoctor(IN _n_licencia INT,
						  IN _nombre_doctor VARCHAR(50),
						  IN _apellidos_doctor VARCHAR(50),
                          OUT _res INT)
BEGIN

	--  CASO -1 - Caso de Nombre vacio
    IF (_nombre_doctor LIKE "" or _nombre_doctor IS NULL) THEN
		SET _res = -1;
    --  CASO -2 - Caso de apellidos vacios
	ELSEIF (_apellidos_doctor LIKE "" or _apellidos_doctor IS NULL) THEN
		SET _res = -2;
	-- CASO -3 - Caso de doctor duplicado
	ELSEIF ( (SELECT n_licencia FROM doctores WHERE n_licencia LIKE _n_licencia) IS NOT NULL) THEN
		SET _res = -3;
	ELSE
	-- CASO 0 - Todo OK. Instruccion nuclear 
		INSERT INTO doctores(n_licencia,nombre_doctor,apellidos_doctor)
						VALUES(_n_licencia, _nombre_doctor,_apellidos_doctor);
		SET _res = 0;
	END IF;
END//
DELIMITER ;

-- ===== PROCEDURE ListaCitas =====
-- Devuelve todas las citas.
DROP PROCEDURE IF EXISTS ListaCitas;
DELIMITER //
CREATE PROCEDURE ListaCitas()
BEGIN
	-- CASO 0 - Todo OK. Instruccion nuclear 
		SELECT * FROM citasmedicas;
END//
DELIMITER ;

-- ===== PROCEDURE AltaCita =====
-- Parámetros IN + OUT _res. Casos: -1 nombre, -2 especialidad, -3 fecha,
-- -4 fecha pasada, 0 OK.
DROP PROCEDURE IF EXISTS AltaCita;
DELIMITER //
CREATE PROCEDURE AltaCita(IN _nombre_paciente VARCHAR(50),
						  IN _especialidad VARCHAR(20),
						  IN _fecha DATETIME,
                          IN _doctor_asignado INT,
                          OUT _res INT)
BEGIN

	--  CASO -1 - Nombre vacio
    IF (_nombre_paciente LIKE "" or _nombre_paciente IS NULL) THEN
		SET _res = -1;
    --  CASO -2 - Especialidad vacio
	ELSEIF (_especialidad LIKE "" or _especialidad IS NULL) THEN
		SET _res = -2;
    --  CASO -3 - Fecha vacia
    ELSEIF (_fecha IS NULL or _fecha LIKE "") THEN
		SET _res = -3;
    --  CASO -4 - Fecha es anterior al presente
	ELSEIF (_fecha <= NOW()) THEN
		SET _res = -4;
	ELSE
	-- CASO 0 - Todo OK. Instruccion nuclear 
		INSERT INTO citasmedicas(nombre_paciente,especialidad,fecha, fk_doctor)
						VALUES(_nombre_paciente,_especialidad,_fecha,_doctor_asignado);
		SET _res = 0;
	END IF;
END//
DELIMITER ;

-- ===== PROCEDURE ModificarCita =====
-- Actualiza una cita por id_cita. Mismos casos de error que AltaCita.
DROP PROCEDURE IF EXISTS ModificarCita;
DELIMITER //
CREATE PROCEDURE ModificarCita(IN _id_cita INT,
						IN _nombre_paciente VARCHAR(50),
						  IN _especialidad VARCHAR(20),
						  IN _fecha DATETIME,
                          IN _doctor_asignado INT,
                          OUT _res INT)
BEGIN
	--  CASO -1 - Nombre vacio
    IF (_nombre_paciente LIKE "" or _nombre_paciente IS NULL) THEN
		SET _res = -1;
    --  CASO -2 - Especialidad vacio
	ELSEIF (_especialidad LIKE "" or _especialidad IS NULL) THEN
		SET _res = -2;
    --  CASO -3 - Fecha vacia
    ELSEIF (_fecha IS NULL or _fecha LIKE "") THEN
		SET _res = -3;
    --  CASO -4 - Fecha es anterior al presente
	ELSEIF (_fecha <= NOW()) THEN
		SET _res = -4;
	ELSE
	-- CASO 0 - Todo OK. Instruccion nuclear 
		UPDATE citasmedicas SET nombre_paciente = _nombre_paciente,
								especialidad = _especialidad,
                                fecha = _fecha,
                                fk_doctor = _doctor_asignado
				WHERE id_cita = _id_cita;
		SET _res = 0;
	END IF;
END//
DELIMITER ;








