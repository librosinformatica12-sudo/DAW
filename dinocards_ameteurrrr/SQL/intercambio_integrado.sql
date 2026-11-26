-- DinoCards: requisitos del módulo de intercambio
-- La base de datos principal ya contiene todas las tablas necesarias.
-- No importar el SQL del proyecto amateur ni crear el procedimiento Trade antiguo.
-- El intercambio se ejecuta desde intercambio.php mediante una transacción PHP.
--
-- Requisitos: colecciones(id PRIMARY KEY, usuario_id, dinosaurio_id),
-- usuarios(id PRIMARY KEY, username), dinosaurios(id PRIMARY KEY, nombre, especie, image_url).
--
-- Ejecuta estas consultas de comprobación en la base de datos dinocards:
USE dinocards;
SHOW COLUMNS FROM usuarios;
SHOW COLUMNS FROM colecciones;
SHOW COLUMNS FROM dinosaurios;

-- Índice opcional si no existe otro índice que empiece por usuario_id.
-- En el dump original ya existe KEY usuario_id, por lo que no es necesario añadirlo.
