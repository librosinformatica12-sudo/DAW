-- SCRIPT de Generacion de BBDD en MySQL
CREATE DATABASE IF NOT EXISTS DinoCards;

-- DROPS
DROP TABLE IF EXISTS colecciones;
DROP TABLE IF EXISTS dinosaurios;
DROP TABLE IF EXISTS usuarios;

-- CREATE TABLES
CREATE TABLE usuarios(id INT primary key auto_increment,
					  username VARCHAR(64) UNIQUE,
                      email VARCHAR(256) UNIQUE,
                      password_hash VARCHAR(1024),
                      fecha_registro DATETIME,
                      ultima_obtencion DATETIME);
CREATE TABLE dinosaurios(id INT primary key auto_increment,
					  nombre VARCHAR(64),
                      especie VARCHAR(1024),
                      periodo VARCHAR(1024),
                      image_url VARCHAR(1024),
                      altura DECIMAL(4,2),
                      largo DECIMAL(4,2),
                      peso DECIMAL(6,3),
                      hp INT,
                      vigor INT,
                      ataque INT,
                      defensa INT,
                      agilidad INT);
CREATE TABLE colecciones(id INT primary key auto_increment,
						usuario_id INT,
						dinosaurio_id INT,
                        fecha_adquisicion DATETIME,
              foreign key (usuario_id) REFERENCES usuarios(id),
              foreign key (dinosaurio_id) REFERENCES dinosaurios(id)
			);

-- PROCEDIMIENTOS ALMACENADOS
	-- PA REGISTRO
DROP PROCEDURE IF EXISTS Registro;
DELIMITER $$
CREATE PROCEDURE Registro(IN _username VARCHAR(64),
                          IN _email VARCHAR(256),
                          IN _password VARCHAR(1024),
                          OUT _res INT -- CODIGO DE ERROR DE SALIDA
                          )
BEGIN
	DECLARE aux VARCHAR(16);
    SET aux = NULL;
    
    SELECT username FROM usuarios WHERE username LIKE _username or email LIKE _email
    INTO aux;
    -- CASO -1 - El parametro _username o _email esta vacio
    IF(_username LIKE "" or _email LIKE "") THEN
		SET _res = -1;
    -- CASO -2 - El usuario o correo que se pretende registrar YA EXISTE
    ELSEIF(aux IS NOT NULL) THEN
		SET _res = -2;
    -- CASO -3 - Contraseña vacia
	ELSEIF(_password LIKE "") THEN
		SET _res = -3;
	ELSE
	-- CASO 0 - Instruccion nuclear. Todo esta OK
		INSERT INTO usuarios(username,
							email,
                            password_hash,
                            fecha_registro
							)
					VALUES(_username,
							_email,
							_password,
							SYSDATE()
							);
		SET _res = 0;
	end if;
END$$
DELIMITER ;
    -- PA LOGIN
DROP PROCEDURE IF EXISTS Login;
DELIMITER $$
CREATE PROCEDURE Login(IN _email VARCHAR(256),
						IN _password VARCHAR(1024),
						OUT _res INT
                        )
BEGIN
	DECLARE aux VARCHAR(16);
    SET aux = NULL;
    
    -- CASO -1 - El parametro _email (usuario o correo) esta vacio
    IF(_email LIKE "") THEN
		SET _res = -1;
    -- CASO -2 - Contraseña vacia
	ELSEIF(_password LIKE "") THEN
		SET _res = -2;
	ELSE
		SELECT username INTO aux FROM usuarios
		WHERE (email = _email OR username = _email) AND password_hash = _password;
		-- CASO -3 - El usuario o contraseña no es correcto
		IF(aux IS NULL) THEN
			SET _res = -3;
		ELSE
		-- CASO 0 - Instruccion nuclear. Todo esta OK. Devolver el Perfil de usuario
			SET _res = 0;
			SELECT * FROM usuarios
			WHERE (email = _email OR username = _email) AND password_hash = _password;
		end if;
	end if;
END$$
DELIMITER ;

    -- PA AñadirNuevaCartaAColeccion
DROP PROCEDURE IF EXISTS AñadirNuevaCartaAColeccion;
DELIMITER $$
CREATE PROCEDURE AñadirNuevaCartaAColeccion(IN _usuario_id INT,
											IN _carta_id INT,
											OUT _res INT
											)
BEGIN
	DECLARE auxUsuarioExiste INT;
    DECLARE auxDinoExiste INT;
    SET auxUsuarioExiste = NULL;
    SET auxDinoExiste = NULL;
    
    SELECT id FROM usuarios WHERE id = _usuario_id
    INTO auxUsuarioExiste;
    
    SELECT id FROM dinosaurios WHERE id = _carta_id
    INTO auxDinoExiste;
    
    IF(auxUsuarioExiste IS NULL) THEN -- CASO -1 - Usuario no existe
		SET _res = -1;
    ELSEIF(auxDinoExiste IS NULL) THEN -- CASO -2 - Id de carta inexistente
		SET _res = -2;
    ELSE -- CASO 0 - Todo OK
		INSERT INTO colecciones(usuario_id,dinosaurio_id,fecha_adquisicion)
						VALUES(_usuario_id, _carta_id,SYSDATE());
		SET _res = 0;
	END IF;
END$$   
DELIMITER ;   
    -- PA ObtenerColeccion
DROP PROCEDURE IF EXISTS ObtenerColeccion;
DELIMITER $$
CREATE PROCEDURE ObtenerColeccion(IN _usuario_id INT,
								  OUT _res INT
								  )
BEGIN
	DECLARE aux VARCHAR(16);
    SET aux = NULL;
    
    -- Caso 0 - Todo OK, devuelvo la coleccion
		SET _res = 0;
        SELECT dinosaurio_id FROM colecciones WHERE usuario_id LIKE _usuario_id
        ORDER BY dinosaurio_id ASC;
END$$
DELIMITER ;


-- CARGA DE DATOS INICIAL (Primeros INSERTS) (Esto puede estar en otro .sql aparte)
INSERT INTO `dinosaurios` (`id`, `nombre`, `especie`, `periodo`, `image_url`, `altura`, `largo`, `peso`, `hp`, `vigor`, `ataque`, `defensa`, `agilidad`) VALUES
(1, 'Tiranosaurio Rex', 'Tyrannosaurus rex', 'Cretácico Superior (68-66 Ma)', NULL, 4, 12.5, 8.4, 92, 85, 98, 72, 45),
(2, 'Triceratops', 'Triceratops horridus', 'Cretácico Superior (67-66 Ma)', NULL, 2.8, 9, 6.5, 88, 80, 78, 92, 38),
(3, 'Velociraptor', 'Velociraptor mongoliensis', 'Cretácico Superior (75-71 Ma)', NULL, 0.5, 1.8, 0.015, 32, 48, 82, 42, 96),
(4, 'Estegosaurio', 'Stegosaurus stenops', 'Jurásico Superior (155-150 Ma)', NULL, 2.9, 9, 2, 85, 75, 68, 96, 35),
(5, 'Braquiosaurio', 'Brachiosaurus altithorax', 'Jurásico Superior (154-153 Ma)', NULL, 13, 26, 56, 98, 92, 45, 88, 22),
(6, 'Espinosauro', 'Spinosaurus aegyptiacus', 'Cretácico Superior (112-97 Ma)', NULL, 5.2, 17, 7, 94, 87, 96, 75, 42),
(7, 'Parasaurolofus', 'Parasaurolophus walkeri', 'Cretácico Superior (75-72 Ma)', NULL, 2.5, 9.5, 2.5, 72, 70, 48, 65, 62),
(8, 'Anquilosaurio', 'Ankylosaurus magniventris', 'Cretácico Superior (68-66 Ma)', NULL, 1.7, 10.5, 4.8, 86, 78, 52, 98, 28),
(9, 'Pteranodon', 'Pteranodon longiceps', 'Cretácico Superior (88-86 Ma)', NULL, 1.8, 7.5, 0.02, 38, 52, 58, 38, 88),
(10, 'Allosaurio', 'Allosaurus fragilis', 'Jurásico Superior (155-145 Ma)', NULL, 2.7, 8.5, 2.3, 78, 75, 92, 62, 68),
(11, 'Diplodoco', 'Diplodocus longus', 'Jurásico Superior (154-152 Ma)', NULL, 3.6, 27, 16, 82, 85, 38, 72, 35),
(12, 'Trictops Bebé', 'Triceratops prorsus', 'Cretácico Superior (68-66 Ma)', NULL, 1.2, 2.5, 0.15, 42, 48, 45, 72, 58),
(13, 'Compsognato', 'Compsognathus longipes', 'Jurásico Superior (150-148 Ma)', NULL, 0.25, 0.9, 0.003, 18, 35, 68, 22, 98),
(14, 'Argentinosaurio', 'Argentinosaurus huinculensis', 'Cretácico Superior (95-100 Ma)', NULL, 18, 35, 70, 99, 95, 42, 92, 18),
(15, 'Ictiosaurio', 'Ichthyosaurus communis', 'Jurásico Inferior (194-189 Ma)', NULL, 1.8, 2, 0.19, 62, 68, 72, 52, 78),
(16, 'Plesiosaurio', 'Plesiosaurus dolichodeirus', 'Jurásico Inferior (200-175 Ma)', NULL, 1.5, 11.5, 4.5, 75, 72, 76, 65, 74),
(17, 'Mosasaurio', 'Mosasaurus missouriensis', 'Cretácico Superior (82-66 Ma)', NULL, 3, 17.5, 15, 90, 82, 94, 68, 58),
(18, 'Iguanodonte', 'Iguanodon bernissartensis', 'Cretácico Inferior (130-125 Ma)', NULL, 4, 10, 3.5, 74, 72, 62, 68, 52),
(19, 'Ceratosaurio', 'Carnotaurus sastrei', 'Cretácico Inferior (100-95 Ma)', NULL, 2.5, 7.5, 1.95, 76, 73, 88, 58, 72),
(20, 'Tarbosauro', 'Tarbosaurus bataar', 'Cretácico Superior (70-66 Ma)', NULL, 3.8, 12, 5, 86, 80, 94, 68, 52),
(21, 'Protosaurio', 'Protosauropod theodor', 'Triásico Superior (228-220 Ma)', NULL, 1.2, 2.5, 0.08, 35, 42, 42, 38, 68),
(22, 'Pterodáctilo', 'Pterodactyl kochi', 'Jurásico Superior (150-148 Ma)', NULL, 0.8, 3.5, 0.008, 28, 42, 52, 32, 92),
(23, 'Quetzalcóatl', 'Quetzalcoatlus northropi', 'Cretácico Superior (70-66 Ma)', NULL, 3.5, 11, 0.15, 52, 62, 68, 45, 85),
(24, 'Sinornitosaurio', 'Sinosauropteryx prima', 'Cretácico Inferior (125-122 Ma)', NULL, 0.6, 1, 0.002, 25, 38, 65, 28, 95),
(25, 'Apatosaurio', 'Apatosaurus louisae', 'Jurásico Superior (154-150 Ma)', NULL, 8.5, 21, 18, 88, 88, 48, 82, 28),
(26, 'Tricerátope de Larga Corna', 'Torosaurus latus', 'Cretácico Superior (68-66 Ma)', NULL, 3.2, 8.5, 5.5, 85, 78, 72, 88, 42),
(27, 'Ovirraptor', 'Oviraptor philceratops', 'Cretácico Superior (81-75 Ma)', NULL, 1.5, 2.5, 0.025, 38, 52, 78, 48, 88),
(28, 'Gallimimo', 'Gallimimus bullatus', 'Cretácico Superior (75-71 Ma)', NULL, 2, 6, 0.35, 48, 58, 54, 42, 92),
(29, 'Iguanodón Mayor', 'Iguanodon mantelli', 'Cretácico Inferior (135-125 Ma)', NULL, 4.5, 11, 4.2, 78, 75, 65, 72, 48),
(30, 'Deinoquiro', 'Deinochirus mirificus', 'Cretácico Superior (80-73 Ma)', NULL, 3.5, 11, 0.6, 62, 68, 72, 52, 78),
(31, 'Tiranosaurio Bebé', 'Tyrannosaurus rex (juvenil)', 'Cretácico Superior (68-66 Ma)', NULL, 2, 5, 0.5, 52, 58, 72, 52, 68),
(32, 'Placerias', 'Placerias hesternus', 'Triásico Superior (227-220 Ma)', NULL, 1.1, 3.5, 0.2, 48, 55, 48, 62, 35),
(33, 'Nothosauro', 'Nothosaurus mirabilis', 'Triásico Superior (235-228 Ma)', NULL, 0.8, 1.5, 0.015, 42, 50, 68, 38, 80),
(34, 'Ópistocélico', 'Opisthoteuthis granulosa', 'Jurásico Medio (175-160 Ma)', NULL, 2.2, 7.5, 0.4, 55, 60, 62, 48, 75),
(35, 'Saurópodo Pequeño', 'Camarasaurus supremus', 'Jurásico Superior (155-145 Ma)', NULL, 6, 18, 20, 84, 86, 45, 78, 32),
(36, 'Dimetrodonte', 'Dimetrodon grandis', 'Pérmico Superior (260-254 Ma)', NULL, 0.4, 1.2, 0.008, 32, 45, 58, 35, 72),
(37, 'Giganotosauro', 'Giganotosaurus carolinii', 'Cretácico Inferior (100-93 Ma)', NULL, 4.2, 13.2, 8.8, 93, 86, 97, 70, 48),
(38, 'Maniraptorá', 'Deinonychus antirrhopus', 'Cretácico Inferior (125-120 Ma)', NULL, 1.1, 3.4, 0.073, 42, 55, 85, 45, 92),
(39, 'Therizinosaurio', 'Therizinosaurus cheloniformis', 'Cretácico Superior (90-88 Ma)', NULL, 3, 5.5, 5, 78, 75, 68, 85, 35),
(40, 'Trodonte', 'Troodon formosus', 'Cretácico Superior (77-75 Ma)', NULL, 0.6, 2, 0.05, 35, 48, 72, 38, 94),
(41, 'Sauroposeidón', 'Sauroposeidon proteles', 'Cretácico Inferior (125-120 Ma)', NULL, 15.5, 34, 60, 98, 93, 48, 89, 20),
(42, 'Ictiteria', 'Ichthyomis dispar', 'Cretácico Superior (85-80 Ma)', NULL, 0.3, 0.4, 0.001, 20, 38, 58, 25, 88),
(43, 'Paquicefalosauro', 'Pachycephalosaurus wyomingensis', 'Cretácico Superior (69-66 Ma)', NULL, 2.4, 4.6, 0.4, 68, 70, 75, 82, 58),
(44, 'Ornitorrinco Prehistórico', 'Stegoceras validum', 'Cretácico Superior (75-72 Ma)', NULL, 1.8, 2.1, 0.15, 48, 55, 62, 68, 68),
(45, 'Saurópodo Acuático', 'Rebbachisaurus gaundi', 'Cretácico Inferior (125-113 Ma)', NULL, 8, 21, 25, 90, 88, 42, 80, 30),
(46, 'Raptor Mayor', 'Utahraptor ostrommaysorum', 'Cretácico Inferior (125-120 Ma)', NULL, 1.8, 7, 0.5, 58, 65, 88, 55, 85),
(47, 'Dinosaurio Volador', 'Archaeopteryx lithographica', 'Jurásico Superior (150-148 Ma)', NULL, 0.4, 0.5, 0.001, 28, 42, 48, 32, 90),
(48, 'Rinoceronte Marino', 'Leedsichthys problematics', 'Jurásico Medio (168-155 Ma)', NULL, 4, 16.5, 45, 92, 84, 76, 72, 42),
(49, 'Espinosaurido Acuático', 'Baryonyx walkeri', 'Cretácico Inferior (130-125 Ma)', NULL, 2.5, 8.5, 1.7, 74, 72, 84, 58, 66),
(50, 'Microrraptor', 'Microraptor zhaoianus', 'Cretácico Inferior (125-122 Ma)', NULL, 0.35, 1.2, 0.001, 24, 40, 76, 35, 97),
(51, 'Ankylosaurus Coludo', 'Edmontosaurus regalis', 'Cretácico Superior (76-74 Ma)', NULL, 2.2, 7.5, 2, 68, 66, 48, 85, 56),
(52, 'Titanosaurio Enano', 'Diamantinasaurus matildae', 'Cretácico Inferior (105-100 Ma)', NULL, 5, 16, 12, 82, 84, 40, 76, 26),
(53, 'Aviador Antiguo', 'Eudimorphodon ranzii', 'Triásico Superior (223-220 Ma)', NULL, 0.3, 0.75, 0.001, 22, 38, 50, 28, 89),
(54, 'Mosasaurio Gigante', 'Tylosaurus proriger', 'Cretácico Superior (85-80 Ma)', NULL, 3.5, 20, 20, 92, 84, 95, 70, 55),
(55, 'Ceratopsio Menor', 'Protoceratops andrewsi', 'Cretácico Superior (80-75 Ma)', NULL, 0.9, 1.8, 0.18, 38, 48, 52, 65, 72),
(56, 'Plesiosaurio Largo', 'Cryptocleidus oxoniensis', 'Jurásico Medio (165-155 Ma)', NULL, 1.2, 13, 5, 78, 75, 74, 68, 72),
(57, 'Pterosaurio Dentado', 'Anhanguera blittersdorfi', 'Cretácico Inferior (112-108 Ma)', NULL, 1.5, 4.2, 0.02, 36, 50, 62, 40, 87),
(58, 'Dromeosaurio Primitivo', 'Rahonavis ostromi', 'Cretácico Superior (70-66 Ma)', NULL, 0.5, 0.8, 0.008, 28, 44, 74, 36, 96),
(59, 'Saurópodo de Cuello Corto', 'Mamenchisaurus sinocanadorum', 'Jurásico Superior (160-155 Ma)', NULL, 7.5, 25, 22, 86, 87, 44, 80, 25),
(60, 'Espinosaurio Menor', 'Sigilmassasaurus brevicollis', 'Cretácico Inferior (120-116 Ma)', NULL, 3.8, 12, 4, 78, 75, 88, 68, 55),
(61, 'Ictiosaurio Gigante', 'Temnodontosaurus platyodon', 'Jurásico Inferior (195-190 Ma)', NULL, 2.5, 9, 5, 82, 78, 85, 62, 75),
(62, 'Ornitomímido', 'Ornithomimus edmontonensis', 'Cretácico Superior (75-71 Ma)', NULL, 1.8, 4.5, 0.15, 42, 62, 48, 38, 94),
(63, 'Ceratopsio Gigante', 'Pentaceratops sternbergii', 'Cretácico Superior (75-72 Ma)', NULL, 3.5, 8.2, 8, 86, 82, 75, 90, 42),
(64, 'Pteranodon Gigante', 'Pteranodon ingens', 'Cretácico Superior (89-85 Ma)', NULL, 2.5, 10.5, 0.035, 44, 58, 64, 42, 86),
(65, 'Eosimio Volador', 'Icaronycteris index', 'Cretácico Superior (50-48 Ma)', NULL, 0.15, 0.35, 0.001, 18, 35, 45, 22, 92),
(66, 'Paquicefalosaurio Pequeño', 'Homalocephale calathocercos', 'Cretácico Superior (75-72 Ma)', NULL, 1.5, 2, 0.08, 44, 52, 58, 72, 65),
(67, 'Nodosaurio Blindado', 'Polacanthus foxii', 'Cretácico Inferior (130-125 Ma)', NULL, 1.6, 8, 3.5, 82, 76, 48, 96, 32),
(68, 'Reptil Marino Corto', 'Nothosaurus longispinus', 'Triásico Medio (242-235 Ma)', NULL, 1.2, 2.2, 0.028, 48, 54, 72, 42, 78),
(69, 'Carnosaurio Emplumado', 'Guanlong wucaii', 'Jurásico Superior (155-150 Ma)', NULL, 2.2, 6, 1.1, 68, 70, 85, 55, 75),
(70, 'Estegosaurio Temprano', 'Huayangosaurus taibaii', 'Jurásico Medio (170-165 Ma)', NULL, 1.8, 4, 0.4, 58, 62, 52, 82, 52),
(71, 'Ictiosaurio Pequeño', 'Mixosaurus cornalianus', 'Triásico Medio (245-242 Ma)', NULL, 0.8, 1, 0.012, 35, 46, 62, 38, 82),
(72, 'Tiranosáurido Enano', 'Dilong paradoxus', 'Cretácico Inferior (130-125 Ma)', NULL, 1.8, 3.8, 0.4, 48, 54, 78, 48, 72),
(73, 'Arqueopterix', 'Archaeopteryx siemensii', 'Jurásico Superior (150-148 Ma)', NULL, 0.25, 0.6, 0.001, 26, 40, 52, 35, 92),
(74, 'Troodón', 'Troodon inequalis', 'Cretácico Superior (77-74 Ma)', NULL, 0.5, 2.4, 0.05, 36, 50, 75, 40, 95),
(75, 'Eoraptor', 'Eoraptor lunensis', 'Triásico Superior (228-225 Ma)', NULL, 0.8, 1.6, 0.008, 32, 45, 82, 38, 94),
(76, 'Tiranosáurido Plumado', 'Yutyrannus huali', 'Cretácico Inferior (125-122 Ma)', NULL, 2.8, 9, 1.4, 68, 70, 88, 58, 65),
(77, 'Paquicéfalo', 'Dracorex hogwartsia', 'Cretácico Superior (75-72 Ma)', NULL, 1.6, 2.2, 0.1, 46, 54, 65, 75, 68),
(78, 'Camposaurio Acuático', 'Suchomimus tenerensis', 'Cretácico Inferior (121-112 Ma)', NULL, 3.5, 11, 3.2, 76, 72, 86, 60, 64),
(79, 'Tiranosáurido Primitivo', 'Eotyrannus lengi', 'Cretácico Inferior (130-125 Ma)', NULL, 2.4, 4.6, 0.5, 54, 62, 85, 52, 68),
(80, 'Ceratopsio de Cuerno Largo', 'Kosmoceratops richardsoni', 'Cretácico Superior (76-74 Ma)', NULL, 2.8, 6.5, 3.5, 82, 78, 72, 88, 48),
(81, 'Pterosaurio Corto', 'Dimorphodon macronyx', 'Jurásico Inferior (199-196 Ma)', NULL, 0.6, 1, 0.003, 28, 42, 58, 36, 90),
(82, 'Carnosaurio Ágil', 'Megalosaurus bucklandii', 'Jurásico Medio (170-166 Ma)', NULL, 2.5, 7.5, 1.1, 72, 74, 90, 54, 68),
(83, 'Dinosaurio Pico de Pato Corto', 'Hypacrosaurus altispinus', 'Cretácico Superior (76-74 Ma)', NULL, 2.7, 9.5, 2.2, 74, 72, 48, 78, 56),
(84, 'Reptil Marino Primitivo', 'Placodus gigas', 'Triásico Medio (242-235 Ma)', NULL, 1.4, 2.8, 0.045, 52, 58, 68, 48, 72),
(85, 'Sauropodomorfo Primitivo', 'Panphagia protos', 'Triásico Superior (228-225 Ma)', NULL, 0.9, 1.2, 0.01, 34, 48, 42, 56, 78),
(86, 'Ictiosaurio Largo', 'Shonisaurus popularis', 'Triásico Superior (228-220 Ma)', NULL, 3.2, 15.5, 18, 90, 84, 82, 68, 48),
(87, 'Nodosaurio Gigante', 'Edmontonia longicauda', 'Cretácico Superior (75-72 Ma)', NULL, 1.5, 7.5, 2.8, 80, 74, 42, 94, 28),
(88, 'Pterosaurio Gigante', 'Azhdarchidae rex', 'Cretácico Superior (68-66 Ma)', NULL, 4.2, 10.8, 0.18, 58, 64, 72, 52, 74),
(89, 'Plesiosaurio Marino Gigante', 'Plesiosauru macrocephalus', 'Jurásico Inferior (195-190 Ma)', NULL, 2.2, 14.5, 8.5, 84, 80, 76, 72, 64),
(90, 'Dromeosaurio Volador', 'Archaeoraptor liaoningensis', 'Cretácico Inferior (122-120 Ma)', NULL, 0.45, 1.3, 0.006, 26, 42, 76, 38, 94),
(91, 'Carnosaurio Temprano', 'Herrerasaurus ischigualastensis', 'Triásico Superior (228-225 Ma)', NULL, 1.6, 4.2, 0.21, 46, 52, 84, 44, 82),
(92, 'Anquilosauro con Pica', 'Saichania chulsanensis', 'Cretácico Superior (80-75 Ma)', NULL, 1.7, 8, 3.5, 82, 76, 48, 96, 26),
(93, 'Terópodo de Cuello Largo', 'Erlikoaurus andrewsi', 'Cretácico Superior (98-92 Ma)', NULL, 3.5, 7.8, 8.2, 78, 65, 72, 45, 68),
(94, 'Ornitomímido Veloz', 'Struthiomimus altus', 'Cretácico Superior (99-66 Ma)', NULL, 1.8, 2.6, 0.45, 35, 75, 28, 25, 92),
(95, 'Iguanodon Primitivo', 'Hypsilophodon foxii', 'Cretácico Inferior (130-125 Ma)', NULL, 1.2, 1.8, 0.18, 38, 68, 32, 35, 85),
(96, 'Ceratopsio sin Cuernos', 'Leptoceratops gracilis', 'Cretácico Superior (100-66 Ma)', NULL, 1.5, 2.1, 0.35, 42, 60, 30, 40, 78),
(97, 'Plesiosaurio Depredador', 'Kronosaurus queenslandicus', 'Cretácico Inferior (130-100 Ma)', NULL, 1.8, 10.5, 12.8, 88, 55, 82, 68, 42),
(98, 'Saurópodo Gigante', 'Patagotitan mayorum', 'Cretácico Superior (100-95 Ma)', NULL, 18.5, 37, 77, 98, 40, 48, 85, 12),
(99, 'Ictiosaurio Depredador', 'Stenopterygius quadriscissus', 'Jurásico Medio (175-168 Ma)', NULL, 1.2, 4.2, 1.8, 48, 70, 65, 40, 80),
(100, 'Pterosaurio Cazador', 'Anhanguera piscator', 'Cretácico Superior (112-66 Ma)', NULL, 1.5, 3.8, 0.85, 42, 72, 58, 38, 88);

INSERT INTO `usuarios` (`id`, `username`, `email`, `password_hash`, `fecha_registro`, `ultima_obtencion`) VALUES
(1, 'triceraantonio75', 'antonio.suarez36@icloud.com', '263767ec6d91aa58da1c2ad5fda96476', '2025-10-21T02:47:43', '2026-09-25T15:33:58'),
(2, 'eva_saurio93', 'eva.gallardo@gmail.com', '0d4acc123a9b88e689ad66f653f8cf62', '2025-12-05T19:49:05', '2026-08-26T12:32:25'),
(3, 'estherdel55', 'esther.delgado@gmail.com', 'e1a3582bbd2f9c4a52def22f0d5d480a', '2026-01-29T01:38:21', '2026-08-28T11:23:03'),
(4, 'cretacicosalvador55', 'salvador.sanz@icloud.com', '0e471cac6e3bbab3dd538fd9f328f186', '2026-02-09T13:41:26', '2026-09-27T19:22:13'),
(5, 'stegojulia30', 'julia.ferrer@icloud.com', '38d1512f32820763c30ade6d691cae94', '2026-02-18T08:36:53', '2026-08-09T03:31:49'),
(6, 'mariaflo59', 'maria.flores@gmail.com', '6d5a5cce9e2ab3b33cb7a92f2543e574', '2026-03-28T08:46:57', '2026-06-12T05:29:43'),
(7, 'araceli_raptor6', 'araceli.martinez@gmail.com', 'a2e4bcade15582a5457fcc0bf99366e9', '2026-06-05T02:01:48', '2026-09-21T17:50:06'),
(8, 'armando_tricera57', 'armando.ortiz@icloud.com', '0fca78eb359b46b6fad87389360ce143', '2026-06-23T16:21:35', '2026-09-26T02:36:52'),
(9, 'rexleire49', 'leire.guerrero@comcast.net', '9fbfb391dcd78c072784de93d78f89e4', '2026-06-25T11:07:53', '2026-09-27T09:41:51'),
(10, 'miguel_rex91', 'miguel.ortiz@icloud.com', 'b579717c9d3458ac8637bdcdf493b0cd', '2026-09-08T00:33:12', '2026-09-28T01:00:53');

INSERT INTO `colecciones` (`id`, `usuario_id`, `dinosaurio_id`, `fecha_adquisicion`) VALUES
(1, 1, 74, '2025-10-21T21:54:39'),
(2, 1, 91, '2025-10-22T14:33:01'),
(3, 1, 84, '2025-11-06T02:00:05'),
(4, 1, 81, '2025-11-11T12:34:21'),
(5, 2, 65, '2025-12-07T06:51:21'),
(6, 2, 47, '2025-12-08T13:07:24'),
(7, 2, 41, '2025-12-11T17:38:00'),
(8, 1, 22, '2025-12-18T01:21:30'),
(9, 1, 90, '2026-01-11T07:17:42'),
(10, 1, 42, '2026-01-14T08:29:12'),
(11, 1, 57, '2026-01-15T03:35:52'),
(12, 1, 62, '2026-01-21T10:52:55'),
(13, 3, 85, '2026-01-30T13:31:29'),
(14, 1, 85, '2026-02-04T23:35:11'),
(15, 1, 91, '2026-02-05T02:12:27'),
(16, 4, 71, '2026-02-09T20:20:54'),
(17, 4, 90, '2026-02-10T15:50:45'),
(18, 4, 28, '2026-02-11T08:44:57'),
(19, 4, 29, '2026-02-12T10:39:47'),
(20, 4, 100, '2026-02-17T11:21:31'),
(21, 5, 56, '2026-02-18T17:13:54'),
(22, 4, 21, '2026-02-19T08:35:25'),
(23, 4, 96, '2026-02-19T14:10:52'),
(24, 4, 71, '2026-02-20T22:16:11'),
(25, 1, 58, '2026-02-22T20:53:20'),
(26, 1, 83, '2026-02-28T02:47:52'),
(27, 4, 79, '2026-02-28T03:52:49'),
(28, 4, 21, '2026-03-07T12:38:38'),
(29, 4, 31, '2026-03-07T17:31:15'),
(30, 5, 79, '2026-03-09T12:22:56'),
(31, 4, 65, '2026-03-09T21:30:27'),
(32, 4, 31, '2026-03-10T04:52:37'),
(33, 4, 53, '2026-03-10T16:47:14'),
(34, 4, 19, '2026-03-11T12:24:01'),
(35, 4, 96, '2026-03-13T08:32:52'),
(36, 4, 34, '2026-03-14T12:28:44'),
(37, 4, 59, '2026-03-14T12:32:47'),
(38, 4, 80, '2026-03-15T03:30:51'),
(39, 1, 53, '2026-03-15T09:28:46'),
(40, 4, 13, '2026-03-19T06:22:15'),
(41, 1, 33, '2026-03-19T14:46:23'),
(42, 4, 7, '2026-03-19T18:49:04'),
(43, 1, 47, '2026-03-22T13:23:44'),
(44, 4, 36, '2026-03-23T17:59:51'),
(45, 4, 77, '2026-03-23T23:39:47'),
(46, 4, 38, '2026-03-25T12:10:54'),
(47, 4, 95, '2026-03-26T05:24:05'),
(48, 4, 81, '2026-03-27T13:59:00'),
(49, 4, 100, '2026-03-30T03:18:48'),
(50, 6, 13, '2026-03-30T04:24:24'),
(51, 4, 41, '2026-04-01T21:01:53'),
(52, 4, 51, '2026-04-04T07:00:18'),
(53, 4, 15, '2026-04-10T12:06:31'),
(54, 5, 76, '2026-04-15T19:43:34'),
(55, 5, 85, '2026-04-15T21:41:48'),
(56, 4, 64, '2026-04-16T22:42:30'),
(57, 4, 65, '2026-04-17T00:26:00'),
(58, 4, 2, '2026-04-18T17:15:30'),
(59, 4, 7, '2026-04-20T07:46:09'),
(60, 4, 95, '2026-04-21T05:50:20'),
(61, 4, 62, '2026-04-25T13:12:25'),
(62, 4, 64, '2026-04-29T04:50:35'),
(63, 4, 53, '2026-05-01T02:53:51'),
(64, 4, 82, '2026-05-01T19:54:44'),
(65, 4, 27, '2026-05-04T00:28:14'),
(66, 5, 14, '2026-05-04T15:41:13'),
(67, 4, 36, '2026-05-04T17:21:02'),
(68, 4, 21, '2026-05-04T23:26:33'),
(69, 4, 89, '2026-05-05T06:03:04'),
(70, 4, 58, '2026-05-06T11:43:58'),
(71, 1, 31, '2026-05-06T17:03:00'),
(72, 4, 89, '2026-05-07T16:05:32'),
(73, 4, 65, '2026-05-08T20:31:42'),
(74, 4, 11, '2026-05-09T17:43:31'),
(75, 4, 14, '2026-05-10T23:32:02'),
(76, 4, 64, '2026-05-11T15:33:23'),
(77, 4, 34, '2026-05-12T11:00:07'),
(78, 4, 25, '2026-05-12T19:47:04'),
(79, 4, 99, '2026-05-13T06:35:00'),
(80, 5, 17, '2026-05-14T17:04:03'),
(81, 4, 61, '2026-05-15T04:10:19'),
(82, 4, 41, '2026-05-19T14:37:12'),
(83, 4, 95, '2026-05-21T06:07:55'),
(84, 4, 19, '2026-05-23T12:52:18'),
(85, 4, 91, '2026-05-24T04:58:40'),
(86, 4, 24, '2026-05-24T12:50:27'),
(87, 1, 50, '2026-05-25T03:22:00'),
(88, 4, 4, '2026-05-25T20:16:18'),
(89, 4, 60, '2026-05-26T02:47:07'),
(90, 4, 47, '2026-05-26T16:26:09'),
(91, 4, 33, '2026-05-28T19:56:33'),
(92, 4, 24, '2026-06-05T04:34:28'),
(93, 7, 50, '2026-06-05T04:39:57'),
(94, 4, 83, '2026-06-08T03:39:55'),
(95, 7, 99, '2026-06-08T05:31:07'),
(96, 4, 96, '2026-06-08T06:26:13'),
(97, 6, 16, '2026-06-12T05:29:43'),
(98, 1, 48, '2026-06-14T10:32:28'),
(99, 7, 8, '2026-06-14T15:44:00'),
(100, 1, 100, '2026-06-14T20:22:25'),
(101, 4, 18, '2026-06-15T20:53:10'),
(102, 2, 46, '2026-06-16T14:05:00'),
(103, 4, 76, '2026-06-18T16:02:20'),
(104, 7, 71, '2026-06-19T15:52:19'),
(105, 7, 93, '2026-06-20T02:01:16'),
(106, 7, 52, '2026-06-20T05:58:07'),
(107, 4, 2, '2026-06-22T03:51:19'),
(108, 7, 55, '2026-06-22T04:57:26'),
(109, 7, 35, '2026-06-23T07:42:23'),
(110, 7, 38, '2026-06-23T21:46:50'),
(111, 4, 84, '2026-06-24T02:46:19'),
(112, 4, 97, '2026-06-24T14:38:01'),
(113, 8, 39, '2026-06-25T07:48:31'),
(114, 8, 83, '2026-06-25T08:16:00'),
(115, 7, 48, '2026-06-25T17:53:31'),
(116, 8, 12, '2026-06-25T19:11:31'),
(117, 7, 50, '2026-06-26T07:06:12'),
(118, 9, 53, '2026-06-26T09:44:05'),
(119, 9, 8, '2026-06-26T15:10:11'),
(120, 5, 90, '2026-06-26T21:48:48'),
(121, 8, 94, '2026-06-27T09:00:52'),
(122, 4, 95, '2026-06-27T10:17:46'),
(123, 8, 73, '2026-06-27T20:25:41'),
(124, 8, 66, '2026-06-27T20:58:22'),
(125, 7, 56, '2026-06-28T11:30:23'),
(126, 8, 74, '2026-06-28T16:26:40'),
(127, 8, 85, '2026-06-30T12:21:08'),
(128, 9, 90, '2026-07-01T04:17:44'),
(129, 8, 51, '2026-07-02T02:11:25'),
(130, 9, 38, '2026-07-02T02:58:06'),
(131, 8, 74, '2026-07-02T07:57:13'),
(132, 7, 9, '2026-07-04T19:51:42'),
(133, 8, 90, '2026-07-05T02:12:39'),
(134, 8, 100, '2026-07-05T05:57:57'),
(135, 8, 84, '2026-07-08T14:01:06'),
(136, 7, 37, '2026-07-09T20:00:36'),
(137, 9, 52, '2026-07-11T05:04:41'),
(138, 8, 23, '2026-07-11T05:39:40'),
(139, 1, 34, '2026-07-11T16:31:12'),
(140, 9, 53, '2026-07-12T23:54:33'),
(141, 4, 13, '2026-07-13T00:46:32'),
(142, 8, 96, '2026-07-13T03:33:47'),
(143, 9, 7, '2026-07-13T04:03:22'),
(144, 1, 6, '2026-07-14T01:13:13'),
(145, 8, 26, '2026-07-15T14:35:51'),
(146, 9, 80, '2026-07-15T15:21:59'),
(147, 7, 17, '2026-07-15T19:06:41'),
(148, 8, 47, '2026-07-15T22:23:53'),
(149, 8, 71, '2026-07-15T23:37:35'),
(150, 9, 94, '2026-07-16T02:20:12'),
(151, 8, 25, '2026-07-16T10:49:29'),
(152, 9, 15, '2026-07-16T12:07:12'),
(153, 7, 98, '2026-07-16T15:02:53'),
(154, 9, 3, '2026-07-16T17:37:39'),
(155, 7, 80, '2026-07-17T11:29:06'),
(156, 8, 87, '2026-07-17T21:24:16'),
(157, 7, 84, '2026-07-18T01:41:19'),
(158, 8, 28, '2026-07-18T13:48:01'),
(159, 8, 65, '2026-07-20T03:57:28'),
(160, 4, 18, '2026-07-21T01:08:22'),
(161, 8, 74, '2026-07-21T01:31:02'),
(162, 8, 87, '2026-07-21T15:37:45'),
(163, 8, 90, '2026-07-22T08:46:32'),
(164, 8, 1, '2026-07-22T08:49:42'),
(165, 8, 12, '2026-07-22T15:48:44'),
(166, 3, 35, '2026-07-22T22:48:24'),
(167, 4, 42, '2026-07-23T11:49:43'),
(168, 3, 100, '2026-07-23T18:21:29'),
(169, 1, 80, '2026-07-24T09:26:39'),
(170, 8, 89, '2026-07-24T11:49:57'),
(171, 4, 53, '2026-07-25T07:26:04'),
(172, 4, 56, '2026-07-25T09:30:11'),
(173, 8, 58, '2026-07-26T18:36:28'),
(174, 8, 42, '2026-07-26T20:21:33'),
(175, 4, 86, '2026-07-27T05:25:10'),
(176, 4, 51, '2026-07-27T18:57:24'),
(177, 5, 94, '2026-07-28T02:42:30'),
(178, 4, 52, '2026-07-29T08:31:17'),
(179, 9, 26, '2026-07-29T15:01:38'),
(180, 4, 26, '2026-07-31T19:19:35'),
(181, 8, 70, '2026-08-01T10:30:13'),
(182, 7, 42, '2026-08-01T17:07:00'),
(183, 8, 37, '2026-08-02T07:23:43'),
(184, 9, 49, '2026-08-02T21:42:57'),
(185, 8, 66, '2026-08-03T14:51:07'),
(186, 8, 67, '2026-08-04T00:57:17'),
(187, 8, 68, '2026-08-04T04:39:57'),
(188, 9, 80, '2026-08-04T10:44:21'),
(189, 4, 81, '2026-08-05T05:19:52'),
(190, 9, 60, '2026-08-05T11:14:50'),
(191, 8, 34, '2026-08-05T20:38:24'),
(192, 7, 50, '2026-08-06T09:30:18'),
(193, 9, 13, '2026-08-07T03:14:55'),
(194, 5, 26, '2026-08-07T14:28:38'),
(195, 9, 36, '2026-08-08T00:19:04'),
(196, 8, 65, '2026-08-08T12:38:05'),
(197, 5, 81, '2026-08-09T03:31:49'),
(198, 8, 98, '2026-08-09T18:17:29'),
(199, 4, 47, '2026-08-10T02:28:59'),
(200, 4, 24, '2026-08-11T00:07:26'),
(201, 8, 37, '2026-08-11T01:06:42'),
(202, 9, 59, '2026-08-11T08:58:23'),
(203, 4, 28, '2026-08-12T02:46:45'),
(204, 4, 36, '2026-08-12T14:05:15'),
(205, 4, 41, '2026-08-12T20:11:43'),
(206, 9, 82, '2026-08-13T19:47:18'),
(207, 1, 67, '2026-08-14T05:57:19'),
(208, 8, 8, '2026-08-14T21:29:54'),
(209, 9, 99, '2026-08-15T05:37:43'),
(210, 8, 81, '2026-08-15T14:00:57'),
(211, 7, 65, '2026-08-15T23:26:26'),
(212, 8, 91, '2026-08-16T07:10:22'),
(213, 8, 65, '2026-08-16T08:38:45'),
(214, 8, 96, '2026-08-16T09:47:57'),
(215, 8, 58, '2026-08-16T20:56:36'),
(216, 4, 85, '2026-08-17T06:18:27'),
(217, 9, 90, '2026-08-17T16:48:51'),
(218, 7, 70, '2026-08-17T21:32:23'),
(219, 1, 72, '2026-08-18T06:01:19'),
(220, 3, 89, '2026-08-18T18:33:06'),
(221, 8, 21, '2026-08-18T23:40:06'),
(222, 4, 93, '2026-08-19T07:55:58'),
(223, 8, 55, '2026-08-19T13:58:03'),
(224, 8, 73, '2026-08-19T23:58:50'),
(225, 8, 46, '2026-08-20T04:34:05'),
(226, 8, 66, '2026-08-21T14:18:36'),
(227, 8, 44, '2026-08-21T22:42:44'),
(228, 7, 50, '2026-08-22T11:41:04'),
(229, 4, 42, '2026-08-24T04:31:12'),
(230, 8, 3, '2026-08-25T08:40:53'),
(231, 7, 53, '2026-08-25T09:52:55'),
(232, 8, 65, '2026-08-25T12:49:45'),
(233, 8, 54, '2026-08-25T18:28:24'),
(234, 8, 35, '2026-08-25T19:10:46'),
(235, 7, 16, '2026-08-26T10:09:21'),
(236, 2, 45, '2026-08-26T12:32:25'),
(237, 8, 66, '2026-08-26T21:31:58'),
(238, 9, 13, '2026-08-27T01:48:58'),
(239, 8, 28, '2026-08-28T01:56:40'),
(240, 7, 13, '2026-08-28T02:03:55'),
(241, 3, 24, '2026-08-28T11:23:03'),
(242, 4, 82, '2026-08-28T21:51:03'),
(243, 8, 24, '2026-08-29T12:45:20'),
(244, 9, 36, '2026-08-29T15:03:58'),
(245, 8, 49, '2026-08-30T10:18:49'),
(246, 9, 23, '2026-08-31T18:47:26'),
(247, 7, 59, '2026-08-31T18:51:27'),
(248, 1, 42, '2026-09-01T11:02:20'),
(249, 8, 72, '2026-09-01T13:42:25'),
(250, 1, 17, '2026-09-02T11:21:42'),
(251, 8, 22, '2026-09-03T02:46:50'),
(252, 7, 43, '2026-09-03T15:14:55'),
(253, 9, 65, '2026-09-05T04:39:26'),
(254, 8, 34, '2026-09-05T10:14:29'),
(255, 7, 51, '2026-09-06T10:21:47'),
(256, 8, 47, '2026-09-08T10:34:03'),
(257, 10, 95, '2026-09-08T19:53:51'),
(258, 7, 59, '2026-09-09T05:06:44'),
(259, 9, 47, '2026-09-09T09:38:53'),
(260, 9, 33, '2026-09-10T15:13:55'),
(261, 9, 77, '2026-09-10T21:50:53'),
(262, 8, 65, '2026-09-10T23:40:42'),
(263, 1, 53, '2026-09-11T00:26:54'),
(264, 7, 57, '2026-09-11T12:20:12'),
(265, 8, 83, '2026-09-11T12:31:41'),
(266, 8, 96, '2026-09-11T13:24:24'),
(267, 7, 55, '2026-09-11T15:07:59'),
(268, 9, 73, '2026-09-11T16:22:53'),
(269, 10, 40, '2026-09-11T16:34:24'),
(270, 7, 65, '2026-09-11T22:45:34'),
(271, 4, 50, '2026-09-12T19:37:26'),
(272, 9, 74, '2026-09-12T20:04:27'),
(273, 8, 57, '2026-09-12T22:43:44'),
(274, 7, 34, '2026-09-13T02:58:33'),
(275, 9, 48, '2026-09-14T01:43:07'),
(276, 4, 27, '2026-09-14T04:43:47'),
(277, 10, 68, '2026-09-15T02:45:20'),
(278, 10, 75, '2026-09-15T16:59:47'),
(279, 7, 89, '2026-09-15T17:17:40'),
(280, 9, 75, '2026-09-15T19:51:10'),
(281, 9, 13, '2026-09-16T04:23:30'),
(282, 8, 98, '2026-09-17T23:20:36'),
(283, 4, 38, '2026-09-18T03:58:11'),
(284, 4, 91, '2026-09-18T18:00:23'),
(285, 4, 22, '2026-09-19T02:22:00'),
(286, 8, 100, '2026-09-19T16:40:42'),
(287, 10, 56, '2026-09-19T19:10:57'),
(288, 10, 36, '2026-09-20T12:53:49'),
(289, 7, 19, '2026-09-20T18:16:21'),
(290, 7, 24, '2026-09-21T14:34:30'),
(291, 7, 73, '2026-09-21T17:50:06'),
(292, 8, 21, '2026-09-22T02:10:24'),
(293, 10, 81, '2026-09-22T09:20:56'),
(294, 4, 18, '2026-09-22T09:45:53'),
(295, 8, 59, '2026-09-23T08:31:36'),
(296, 8, 65, '2026-09-23T17:20:38'),
(297, 9, 29, '2026-09-24T08:00:26'),
(298, 9, 65, '2026-09-25T03:08:00'),
(299, 4, 97, '2026-09-25T03:15:51'),
(300, 1, 96, '2026-09-25T15:33:58'),
(301, 10, 7, '2026-09-25T19:18:37'),
(302, 8, 46, '2026-09-26T02:36:52'),
(303, 9, 98, '2026-09-26T21:33:13'),
(304, 9, 74, '2026-09-27T09:41:51'),
(305, 4, 24, '2026-09-27T19:22:13'),
(306, 10, 42, '2026-09-28T01:00:53');