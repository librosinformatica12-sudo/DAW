# DinoCards – Informe de auditoría y refactorización

## 1. Inventario revisado (128 archivos, ninguno sin revisar)

| Grupo | Archivos |
|---|---|
| Páginas (vistas) | `index.php`, `inicio.php`, `coleccion.php`, `dinopedia.php`, `perfil.php`, `menu.php` |
| Endpoints | `php/login.php`, `php/registro.php`, `php/cerrar.php`, `php/cartaDiaria.php`, `php/abrirSobrePrueba.php` |
| Lógica / datos (antes) | `src/AccesoDatos.php`, `src/ColeccionService.php`, `src/UsuarioService.php`, `src/ReglasColeccion.php`, `src/Respuesta.php` |
| Front | `script/script.js`, `styles/comun.css`, `styles/paginas.css`, `styles/perfil.css` |
| SQL | `SQL/dinocards_usuarios.sql`, `dinocards_dinosaurios.sql`, `dinocards_colecciones.sql`, `instalar.sql` |
| Otros | `sincronizar.bat`, `img/img-sobres/` (3 PNG), `img/img-cartas/` (100 PNG) |

## 2. Estructura: antes y después

**Antes:** `src/AccesoDatos.php` hacía cuatro cosas a la vez (sesión + conexión con credenciales + helpers + carga de servicios), y `src/*Service.php` contenían SQL. Cinco páginas/endpoints (`coleccion`, `dinopedia`, `menu`, `perfil`, `login`, `registro`) abrían consultas por su cuenta.

**Después (rutas públicas, formularios y nombres de campos intactos):**

```
dinocards/
├─ index.php inicio.php coleccion.php dinopedia.php perfil.php menu.php   ← vistas (solo HTML + llamadas a funciones)
├─ php/        login.php registro.php cerrar.php cartaDiaria.php abrirSobrePrueba.php   ← controladores finos
├─ src/        bootstrap.php  AuthService.php  SobreService.php  ReglasColeccion.php  Respuesta.php   ← negocio / arranque
├─ dal/        conexion.php  UsuarioDAL.php  ColeccionDAL.php  DinosaurioDAL.php   ← ÚNICO sitio con mysqli y SQL
├─ config/     database.php  database.local.php.example   ← credenciales (la real, fuera de git)
├─ SQL/  img/  script/  styles/  docs/
```

Flujo: `vista/endpoint → src/*Service → dal/*DAL → MySQL`.

## 3. Qué estaba mal y qué se ha cambiado

### Organización (requisito del revisor: SQL solo en la capa de datos)

| Archivo / línea (original) | Qué estaba mal | Cambio aplicado |
|---|---|---|
| `coleccion.php` L15-22, 39-43, 46 | 3 consultas directas (`prepare`, `query`) en la vista | Usa `obtenerIdsColeccion`, `dinosauriosPorIds`, `contarDinosaurios` (DAL) y `agruparColeccion`/`calcularProgreso` (reglas) |
| `dinopedia.php` L19-22 | `query("SELECT * FROM dinosaurios")` en la vista | `obtenerTodosLosDinosaurios()` |
| `menu.php` L22-34 | Consulta preparada dentro de la cabecera + dependía de la global `$conexion` | `obtenerUltimaObtencion()` |
| `perfil.php` L15-22, 40-52, 58-62 | 3 consultas en la vista | `obtenerUsuarioPorId`, `resumenColeccion`, `contarDinosaurios` |
| `perfil.php` L95-122 | 2 consultas cuyo resultado **nunca se mostraba** (`$ultimas`, `$copiasColeccion`): trabajo inútil en cada carga | Eliminadas (`ultimasCartas()` sigue disponible en el DAL por si se quiere mostrar) |
| `perfil.php` L73-82, `coleccion.php` L24-33, `inicio.php` L51 | Lógica de progreso/nivel/copias duplicada, ignorando `ReglasColeccion.php` | Reutilizan `calcularProgreso`, `calcularNivel`, `agruparColeccion` |
| `php/login.php` L32-58 | SQL de login y de migración md5 dentro del endpoint | Endpoint fino → `AuthService::autenticarUsuario` → `UsuarioDAL` |
| `php/registro.php` L41-65 | SQL de comprobación e inserción dentro del endpoint | Endpoint fino → `registrarUsuario` → `UsuarioDAL` |
| `php/cartaDiaria.php`, `abrirSobrePrueba.php` | Lógica de negocio en el controlador | Movida a `src/SobreService.php` (constantes de espera y nº de cartas centralizadas) |
| `src/AccesoDatos.php` | Sesión + conexión + helpers + carga de servicios en un solo archivo | Dividido: `src/bootstrap.php` (sesión/helpers), `dal/conexion.php` (conexión) |
| `src/UsuarioService.php`, `src/ColeccionService.php` | Funciones SQL con nombre de "Service" y `global $conexion` | Movidas a `dal/UsuarioDAL.php`, `dal/ColeccionDAL.php`, `dal/DinosaurioDAL.php` (mismos nombres de función donde existían) |
| `SQL/instalar.sql` | Duplica los tres volcados, desactualizado (AUTO_INCREMENT 398 vs 411) y en UTF-16 mientras los otros están en UTF-8 | **Sin cambios** (son tus datos). Recomendado: dejar un único script de instalación |
| `sincronizar.bat` | Script de git dentro del directorio web | No se mueve (rompería su `cd %~dp0`); bloqueado desde la web con `.htaccess` |

### Seguridad

| Sev. | Archivo | Qué estaba mal | Cambio aplicado |
|---|---|---|---|
| **Alta** | `AccesoDatos.php` L35-36 | Usuario/contraseña de BD (`oscar`/`1234`) escritos en el código y subidos a GitHub con `sincronizar.bat` | Movidos a `config/database.php` (admite variables de entorno y `config/database.local.php`, ignorado por git). **Ojo:** los valores por defecto siguen siendo los antiguos para que funcione sin tocar nada; cámbialos (y considera la contraseña antigua ya expuesta) |
| **Alta** | `SQL/*.sql` | Volcados con usuarios reales (correos y hashes, 11 de ellos **md5**) descargables por URL (`/SQL/…`) y subidos a GitHub | `.htaccess` que bloquea `SQL/` y los `.sql`/`.bat` desde la web. **No he borrado datos**: recomiendo sustituirlos por datos de ejemplo antes de entregar/publicar |
| **Alta** | `php/cerrar.php` L1 | Empezaba con la línea de Markdown ```` ```php ````: se enviaba al navegador antes de `header()` → "headers already sent", la redirección podía fallar | Eliminada |
| Media | `AccesoDatos.php` L44-46 | `die("Error de conexion: " . connect_error)` mostraba detalles internos | Excepciones + manejador global (`bootstrap.php`): detalle al log, mensaje genérico al usuario |
| Media | Todos los accesos | `prepare()` sin comprobar → un error SQL producía un fatal con información interna | `MYSQLI_REPORT_STRICT` + manejador global; `crearUsuario` captura el duplicado (índice UNIQUE) y devuelve error limpio |
| Media | 4 formularios POST | Sin protección CSRF | Token CSRF en `index.php` (login/registro) e `inicio.php` (2 sobres), validado en los 4 endpoints |
| Media | Sesión | Cookie sin `HttpOnly`/`SameSite`/`Secure`, sin modo estricto | Ajustado en `bootstrap.php` |
| Baja | `ColeccionService::dinosauriosPorIds`, `LIMIT` | SQL con valores concatenados (eran enteros, pero mala práctica) | Marcadores `?` y `bind_param` |
| Baja | `SELECT * FROM usuarios` | Cargaba `password_hash` en las páginas sin necesitarlo | Columnas explícitas, sin el hash |
| Baja | `registro.php` | `maxlength` 16/100 solo en el HTML | Límites también en servidor |
| Baja | Sobre de prueba | 4 inserciones sueltas: si fallaba una, el sobre quedaba a medias | `anadirCartas()` con transacción |

### Recomendaciones que NO he aplicado (cambiarían el comportamiento o requieren decisión tuya)
- Limitar intentos de login (fuerza bruta). 
- Subir la contraseña mínima de 4 a 8+ (el formulario dice "Mínimo 4").
- Forzar el cambio de las contraseñas md5 que aún no han iniciado sesión.
- Cierre de sesión por POST (ahora es un enlace GET).
- Condición de carrera: dos clics rápidos podrían abrir dos sobres; se arregla con un `UPDATE … WHERE` atómico.
- Crear un usuario MySQL con solo `SELECT/INSERT/UPDATE` sobre `dinocards`.
- `display_errors=Off` en producción y SRI para el CSS de boxicons (unpkg).

## 4. Cómo comprobar que todo funciona
1. Copia el proyecto a `htdocs` (XAMPP) y, si tus credenciales no son `oscar`/`1234`, crea `config/database.local.php` a partir del `.example`.
2. Abre `index.php`: **registro** (usuario nuevo) → mensaje verde; **login** → entra a `inicio.php`.
3. `inicio.php`: abre el **sobre del día** (1 carta) y el **de prueba** (4 cartas); el botón queda en espera.
4. `coleccion.php`: aparecen las cartas, con `xN` en las repetidas y el buscador funcionando. `dinopedia.php`: las que faltan en gris. `perfil.php`: estadísticas y barra.
5. **Salir** → vuelve a `index.php` sin avisos de cabeceras.
6. Seguridad: `…/SQL/dinocards_usuarios.sql` y `…/dal/UsuarioDAL.php` deben dar 403.
7. Comprobación automática de la regla: `grep -rniE "mysqli|->prepare\(|->query\(" --include=*.php . | grep -v "^./dal/"` no debe devolver nada.
