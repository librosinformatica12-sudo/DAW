-- ============================================================
-- EXPLICACIÓN GENERAL (ampliada para estudiar)
-- Este script se importa/ejecuta una vez en MySQL (phpMyAdmin o Workbench) y:
--   1) crea la BD y las tablas (borrándolas antes si ya existían),
--   2) crea los 6 procedimientos almacenados que usa AccesoDatos.php.
--
-- PATRÓN DE TODOS LOS PROCEDURES CON VALIDACIÓN (AltaDoctor, AltaCita, ModificarCita):
--   CREATE PROCEDURE nombre(IN _dato TIPO, ..., OUT _res INT)
--   BEGIN
--      IF (caso de error 1) THEN SET _res = -1;
--      ELSEIF (caso de error 2) THEN SET _res = -2;
--      ...
--      ELSE  <operación real: INSERT / UPDATE>  SET _res = 0;
--      END IF;
--   END
--   Se evalúan de arriba abajo y GANA EL PRIMERO que se cumple. 0 = todo correcto.
--
-- DELIMITER: normalmente el punto y coma termina una sentencia, pero dentro de un
-- procedure hay muchos. Por eso se cambia el delimitador a dos barras antes de
-- cada CREATE PROCEDURE (terminando con END seguido de esas dos barras) y se
-- restaura a punto y coma después.
--
-- TRUCO: los parámetros empiezan por _ (_n_licencia) para que no se llamen igual
-- que las columnas. Si se llamaran igual, WHERE n_licencia = n_licencia sería
-- siempre verdadero y afectaría a TODAS las filas.
--
-- [EXAMEN] Lo típico que te quitan: un campo o la FOREIGN KEY de una tabla, un
-- ELSEIF de validación (con su SET _res), el INSERT / UPDATE final, o un
-- procedure completo (copia el esquema de otro).
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
-- IF NOT EXISTS: solo la crea si no existe (no da error al repetir el script).
-- OJO: aquí se llama Clinica y en PHP se conecta a 'clinica'. En Windows da igual
-- mayúsculas/minúsculas, pero en Linux MySQL SÍ las distingue.
CREATE DATABASE IF NOT EXISTS Clinica;
-- USE: indica sobre qué BD trabajan las sentencias siguientes.
USE Clinica;
-- DROPS

-- DROP TABLE IF EXISTS: borra la tabla si existe. ORDEN IMPORTANTE: primero la
-- tabla HIJA (citasMedicas, tiene la clave ajena) y luego la PADRE (doctores).
-- Al revés daría error porque citasMedicas depende de doctores.
-- OJO: cada vez que ejecutas el script SE PIERDEN todos los datos.
DROP TABLE IF EXISTS citasMedicas;
DROP TABLE IF EXISTS doctores;
-- ===== TABLA doctores =====
-- Tabla padre: cada doctor tiene una licencia única (primary key).
-- CREATE TABLES
-- TABLA PADRE. n_licencia INT es PRIMARY KEY (única y obligatoria) y SIN
-- auto_increment: la licencia la escribe el usuario. NOT NULL = obligatorio.
-- Sintaxis: CREATE TABLE nombre(columna TIPO restricciones, ...);
CREATE TABLE doctores(n_licencia INT primary key,
						nombre_doctor VARCHAR(50) NOT NULL,
                        apellidos_doctor VARCHAR(50) NOT NULL
                        );
-- ===== TABLA citasMedicas =====
-- Tabla hija: fk_doctor apunta a doctores(n_licencia).
-- TABLA HIJA. id_cita es PRIMARY KEY con auto_increment (MySQL pone 1, 2, 3...).
-- especialidad es un ENUM: solo admite uno de los valores de la lista (con
-- tildes y mayúsculas exactas). Por eso los <option value=...> de los
-- formularios deben coincidir con estos valores.
-- fecha DATETIME guarda fecha y hora (formato AAAA-MM-DD HH:MM:SS).
CREATE TABLE citasMedicas(id_cita INT primary key auto_increment,
						   nombre_paciente VARCHAR(50) NOT NULL,
                           especialidad  ENUM('Medicina General',
											'Pediatría',
                                            'Traumatología',
                                            'Dermatología'),
						   fecha DATETIME,
                           fk_doctor INT NOT NULL,
			-- CLAVE AJENA: fk_doctor solo puede contener licencias que EXISTAN en
			-- doctores(n_licencia). Si no existe, MySQL rechaza el INSERT (error de
			-- restricción). Sintaxis: FOREIGN KEY (columna_local) REFERENCES tabla(columna).
			FOREIGN KEY (fk_doctor) REFERENCES doctores(n_licencia)
                           );

-- Estas dos líneas (comentadas) muestran cómo añadir la columna y la clave ajena
-- DESPUÉS de crear la tabla, con ALTER TABLE. Típico de examen.
-- OJO: en la segunda línea hay un FOREIGN repetido; la sintaxis correcta es:
--   ALTER TABLE citasMedicas ADD CONSTRAINT nombre FOREIGN KEY (fk_doctor)
--   REFERENCES doctores(n_licencia);
-- ALTER TABLE citasMedicas ADD COLUMN(fk_doctor INT NOT NULL);
-- ALTER TABLE citasMedicas ADD CONSTRAINT FOREIGN FOREIGN KEY (fk_doctor) REFERENCES doctores(n_licencia);

-- ===== PROCEDURE ListaDoctores =====
-- Devuelve todos los doctores (SELECT *).
-- PROCEDURE ListaDoctores (SELECT sin parámetros, sin OUT).
-- Siempre el mismo orden: DROP PROCEDURE IF EXISTS (permite reimportar),
-- cambiar DELIMITER, CREATE PROCEDURE ... BEGIN ... END, y restaurar DELIMITER.
DROP PROCEDURE IF EXISTS ListaDoctores;
DELIMITER //
CREATE PROCEDURE ListaDoctores()
BEGIN
	-- CASO 0 - Todo OK. Instruccion nuclear 
		-- SELECT * devuelve todas las columnas. Este resultado es el que PHP lee con
		-- get_result(). No hay OUT: por eso PA_ListaDoctores llama CALL ListaDoctores().
		SELECT * FROM doctores;
END//
DELIMITER ;

-- ===== PROCEDURE DameDoctor =====
-- Devuelve los datos de un doctor concreto por licencia.
DROP PROCEDURE IF EXISTS DameDoctor;
DELIMITER //
-- PROCEDURE DameDoctor: un parámetro IN y un SELECT con WHERE para un solo doctor.
-- Devuelve 0 filas si la licencia no existe (PHP lo detecta con count == 0).
CREATE PROCEDURE DameDoctor(IN _n_licencia INT)
BEGIN
	-- CASO 0 - Todo OK. Instruccion nuclear 
		SELECT * FROM doctores
        WHERE n_licencia = _n_licencia;
END//
DELIMITER ;

-- ===== PROCEDURE AltaDoctor =====
-- Parámetros IN + OUT _res. Casos: -1 nombre, -2 apellidos, -3 duplicado, 0 OK.
-- PROCEDURE AltaDoctor (INSERT con validaciones). Códigos de _res:
--   -1 nombre vacío   -2 apellidos vacíos   -3 licencia duplicada   0 alta correcta
DROP PROCEDURE IF EXISTS AltaDoctor;
DELIMITER //
CREATE PROCEDURE AltaDoctor(IN _n_licencia INT,
						  IN _nombre_doctor VARCHAR(50),
						  IN _apellidos_doctor VARCHAR(50),
                          OUT _res INT)
BEGIN

	--  CASO -1 - Caso de Nombre vacio
    -- LIKE "" compara con cadena vacía; IS NULL comprueba que no sea nulo.
    -- Se comprueban las dos cosas porque un campo puede llegar vacío o nulo.
    -- Aquí no se valida la licencia (si llega vacía, MySQL la convierte a 0).
    IF (_nombre_doctor LIKE "" or _nombre_doctor IS NULL) THEN
		SET _res = -1;
    --  CASO -2 - Caso de apellidos vacios
	ELSEIF (_apellidos_doctor LIKE "" or _apellidos_doctor IS NULL) THEN
		SET _res = -2;
	-- CASO -3 - Caso de doctor duplicado
	-- DUPLICADO: la subconsulta devuelve la licencia si ya existe y NULL si no.
	-- IS NOT NULL = ya existe -> error -3 (la PRIMARY KEY no permite repetirla).
	-- Alternativa equivalente: ELSEIF EXISTS (SELECT 1 FROM doctores WHERE ...).
	ELSEIF ( (SELECT n_licencia FROM doctores WHERE n_licencia LIKE _n_licencia) IS NOT NULL) THEN
		SET _res = -3;
	ELSE
	-- CASO 0 - Todo OK. Instruccion nuclear 
		-- ELSE: pasó todas las validaciones. INSERT INTO tabla(columnas) VALUES(valores):
		-- los valores van en el MISMO orden que las columnas. Después SET _res = 0.
		INSERT INTO doctores(n_licencia,nombre_doctor,apellidos_doctor)
						VALUES(_n_licencia, _nombre_doctor,_apellidos_doctor);
		SET _res = 0;
	END IF;
END//
DELIMITER ;

-- ===== PROCEDURE ListaCitas =====
-- Devuelve todas las citas.
-- PROCEDURE ListaCitas (SELECT sin parámetros).
-- OJO: aquí se usa citasmedicas en minúsculas pero la tabla se creó como
-- citasMedicas. En Windows funciona; en Linux daría error (nombres de tabla
-- sensibles a mayúsculas). Conviene escribirlo siempre igual.
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
-- PROCEDURE AltaCita (INSERT con validaciones). Códigos de _res:
--   -1 nombre vacío   -2 especialidad vacía   -3 fecha vacía
--   -4 fecha pasada (anterior o igual a ahora)   0 cita creada
-- NO valida que el doctor exista: eso lo hace PHP antes con PA_DameDoctor.
-- Si el doctor no existiera, la FOREIGN KEY haría fallar el INSERT.
DROP PROCEDURE IF EXISTS AltaCita;
DELIMITER //
CREATE PROCEDURE AltaCita(IN _nombre_paciente VARCHAR(50),
						  IN _especialidad VARCHAR(20),
						  IN _fecha DATETIME,
                          IN _doctor_asignado INT,
                          OUT _res INT)
BEGIN

	--  CASO -1 - Nombre vacio
    -- Primera validación: nombre del paciente vacío o nulo -> -1.
    IF (_nombre_paciente LIKE "" or _nombre_paciente IS NULL) THEN
		SET _res = -1;
    --  CASO -2 - Especialidad vacio
	ELSEIF (_especialidad LIKE "" or _especialidad IS NULL) THEN
		SET _res = -2;
    --  CASO -3 - Fecha vacia
    ELSEIF (_fecha IS NULL or _fecha LIKE "") THEN
		SET _res = -3;
    --  CASO -4 - Fecha es anterior al presente
	-- NOW() = fecha y hora actuales del servidor. Si la fecha de la cita es
	-- anterior o igual a ahora no se permite -> -4 (no se pueden pedir citas pasadas).
	ELSEIF (_fecha <= NOW()) THEN
		SET _res = -4;
	ELSE
	-- CASO 0 - Todo OK. Instruccion nuclear 
		-- No se indica id_cita porque es auto_increment. El doctor se guarda en fk_doctor.
		INSERT INTO citasmedicas(nombre_paciente,especialidad,fecha, fk_doctor)
						VALUES(_nombre_paciente,_especialidad,_fecha,_doctor_asignado);
		SET _res = 0;
	END IF;
END//
DELIMITER ;

-- ===== PROCEDURE ModificarCita =====
-- Actualiza una cita por id_cita. Mismos casos de error que AltaCita.
-- PROCEDURE ModificarCita (UPDATE). Recibe el id de la cita + los datos nuevos.
-- Mismos códigos de error que AltaCita (-1, -2, -3, -4) y 0 si todo va bien.
-- OJO: no comprueba que la cita exista: con un id inexistente el UPDATE no
-- cambia nada y aun así devuelve 0.
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
		-- UPDATE tabla SET columna = valor, ... WHERE condición. El WHERE es OBLIGATORIO
		-- en la práctica: sin él se modificarían TODAS las citas.
		UPDATE citasmedicas SET nombre_paciente = _nombre_paciente,
								especialidad = _especialidad,
                                fecha = _fecha,
                                fk_doctor = _doctor_asignado
				WHERE id_cita = _id_cita;
		SET _res = 0;
	END IF;
END//
DELIMITER ;








