# DinoCards — Proyecto FrontEnd PHP

Aplicación web de colección de cartas de dinosaurios. Incluye acceso y registro, panel principal, colección, DinoPedia, perfil e intercambio.

## Requisitos

- XAMPP (Apache, PHP 8 recomendado y MySQL/MariaDB).
- Extensiones PHP `mysqli` y `mbstring`.
- Navegador actualizado.

## Instalación en XAMPP

1. Descomprime la carpeta `dinocards` dentro de `C:\xampp\htdocs\`.
2. Abre el panel de XAMPP e inicia **Apache** y **MySQL**.
3. Entra en `http://localhost/phpmyadmin`.
4. Selecciona **Importar** y carga `SQL/instalar.sql`. El script crea la base de datos `dinocards_actu`, las tablas, el catálogo de dinosaurios y todos los procedimientos almacenados. No incluye cuentas personales ni contraseñas de ejemplo.
5. La configuración de MySQL se lee de variables de entorno. En `.htaccess` ya están definidas `DINOCARDS_DB_HOST`, `DINOCARDS_DB_PORT`, `DINOCARDS_DB_NAME`, `DINOCARDS_DB_USER` y `DINOCARDS_DB_PASS` para este XAMPP (`root` / `1234`). Si cambias de equipo, ajusta ese archivo o las variables del servidor (usa `.env.example` como referencia).
6. Abre `http://localhost/ejer/dinocards_actu/`.
7. Crea una cuenta desde **Registrarse** y después inicia sesión.

> **Importante:** `SQL/instalar.sql` está pensado para una instalación nueva: elimina y vuelve a crear las tablas del proyecto. Si ya tienes datos de DinoCards, haz una copia de seguridad antes de importarlo.
>
> Este proyecto usa su propia base `dinocards_actu` para no sobrescribir la base `dinocards` de las otras versiones. Tablas, procedimientos y conexión usan la misma collation (`utf8mb4_unicode_ci`).

## Estructura principal

- `index.php`: entrada, login y registro.
- `inicio.php`: panel principal del coleccionista.
- `coleccion.php`: cartas del usuario.
- `dinopedia.php`: catálogo de dinosaurios.
- `perfil.php`: datos del usuario.
- `intercambio.php`: panel de intercambio.
- `styles/`: hojas de estilo CSS.
- `script/script.js`: pestañas, validaciones, peticiones AJAX y contadores.
- `php/`: endpoints delgados de login, registro, sobres y cierre de sesión.
- `src/AccesoDatos.php`: arranque de sesión, funciones de ayuda y carga de servicios.
- `src/datos/`: única capa PHP que habla con MySQL; solo invoca procedimientos (`Conexion.php`, `UsuarioDAL.php`, `ColeccionDAL.php`, `DinosaurioDAL.php`, `SobreDAL.php`).
- `src/AuthService.php`, `src/UsuarioService.php`, `src/ColeccionService.php`, `src/SobreService.php`: lógica de negocio, sin SQL.
- `src/ReglasColeccion.php`: cálculos puros de progreso y cooldowns.
- `src/Usuario.php` y `src/Dinosaurio.php`: modelos del proyecto.
- `src/Respuesta.php`: respuestas JSON de los endpoints.
- `SQL/instalar.sql`: instalación limpia (tablas, catálogo y procedimientos almacenados).

El flujo es siempre `vista o endpoint → servicio → DAL → procedimiento MySQL`. No hay sentencias `SELECT`, `INSERT`, `UPDATE`, `DELETE` ni transacciones en ningún archivo `.php`: todo el SQL vive en `SQL/instalar.sql`.

## Seguridad incluida

- Las contraseñas nuevas se almacenan con `password_hash()` y se verifican con `password_verify()`; los hashes md5 antiguos se migran al iniciar sesión.
- El PHP solo llama a procedimientos almacenados mediante sentencias preparadas y una lista blanca de nombres.
- Los sobres se abren dentro de una transacción del procedimiento con reserva atómica para evitar dobles clics.
- Se regenera el identificador de sesión al iniciar sesión.
- Los datos mostrados en HTML se escapan mediante `escapar()`.

## Navegación

La pantalla de entrada cambia entre login y registro con JavaScript sin recargar la página. Las secciones privadas están separadas en paneles PHP y comparten el menú de navegación, manteniendo la sesión del usuario.
