-- ==============================================================
-- DinoCards - Integración del módulo de intercambio
-- Base de datos: dinocards
--
-- NO HAY CAMBIOS DDL OBLIGATORIOS. La integración utiliza las tablas
-- ya existentes: usuarios, colecciones y dinosaurios.
--
-- El endpoint intercambio.php valida la propiedad de cada carta y
-- actualiza colecciones.usuario_id dentro de una transacción.
--
-- No importar el procedimiento Trade del módulo amateur: su definición
-- contiene referencias incorrectas a tablas/columnas inexistentes.
-- Tampoco se crean solicitudes/negociaciones: la interfaz integrada
-- realiza el intercambio directo, sin un flujo de aceptación.
--
-- Este archivo es una comprobación no destructiva: no borra ni modifica
-- datos. Todos los indicadores deben devolver 1.
-- ==============================================================

USE dinocards;

SELECT 'usuarios.id' AS requisito,
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'usuarios' AND column_name = 'id') AS disponible
UNION ALL
SELECT 'usuarios.username',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'usuarios' AND column_name = 'username')
UNION ALL
SELECT 'colecciones.id',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'colecciones' AND column_name = 'id')
UNION ALL
SELECT 'colecciones.usuario_id',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'colecciones' AND column_name = 'usuario_id')
UNION ALL
SELECT 'colecciones.dinosaurio_id',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'colecciones' AND column_name = 'dinosaurio_id')
UNION ALL
SELECT 'dinosaurios.id',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'dinosaurios' AND column_name = 'id')
UNION ALL
SELECT 'dinosaurios.nombre',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'dinosaurios' AND column_name = 'nombre')
UNION ALL
SELECT 'dinosaurios.image_url',
       EXISTS(SELECT 1 FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = 'dinosaurios' AND column_name = 'image_url');
