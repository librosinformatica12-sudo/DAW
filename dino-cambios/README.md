# DinoCards

Aplicación web de coleccionismo de cartas de dinosaurios. Cada jugador crea su
cuenta y va reuniendo cartas de un catálogo de **100 dinosaurios**, cada uno con
sus estadísticas (HP, vigor, ataque, defensa y agilidad).

El objetivo es **completar la colección**: consigues cartas nuevas abriendo
sobres y puedes consultar toda la información de cada especie en la **DinoPedia**.
Las cartas repetidas también se guardan, así que poco a poco vas llenando el álbum.

Proyecto de **Desarrollo de Aplicaciones Web (nivel medio)** realizado con
**HTML, CSS y JavaScript** en el frontend y **PHP 8 + MySQL/MySQLi** en el backend.

---

## Cómo funciona (reglas del juego)

- **Sobre del día**: una carta gratis cada día. Si ya lo has abierto hoy, el
  botón queda desactivado hasta el día siguiente.
- **Sobre de prueba**: entrega **4 cartas aleatorias** de golpe, con espera corta
  controlada por sesión.
- **Mi colección**: todas las cartas conseguidas, agrupadas, con buscador, orden
  y barra de progreso.
- **DinoPedia**: catálogo completo; desde cada ficha puedes añadir la carta a tu
  colección.
- **Mi perfil**: datos de la cuenta y estadísticas de la colección.

---

## Estructura del proyecto

Estructura con carpetas, estilo `dino-prueba` / `dinocards`, manteniendo los
controladores `gestionar_*.php` y las funciones `PA_*`:

```
dino-cambios/
├── index.php                    Iniciar sesión / registrarse
├── inicio.php                   Panel principal
├── coleccion.php                Mi colección
├── dinopedia.php                Catálogo de dinosaurios
├── perfil.php                   Ficha del usuario
├── src/
│   ├── AccesoDatos.php          Conexión + funciones PA_* (procedimientos)
│   ├── Respuesta.php            Respuestas JSON
│   ├── UsuarioService.php       Usuarios, hash y verificación de contraseñas
│   ├── ColeccionService.php     Colección, catálogo e imágenes
│   └── ReglasColeccion.php      Reglas puras (agrupar, progreso, repetidas)
├── partials/
│   └── cabecera.php             Cabecera y navegación comunes
├── php/
│   ├── gestionar_login.php
│   ├── gestionar_registro.php
│   ├── gestionar_logout.php
│   ├── gestionar_cartaDiaria.php
│   ├── gestionar_abrirSobrePrueba.php
│   └── gestionar_anadirCarta.php
├── css/                         comun.css, paginas.css, perfil.css
├── js/                          script.js, inicio.js, carrusel.js, coleccion.js, dinopedia.js
├── img/
│   ├── img-cartas/
│   └── img-sobres/
├── sql/                         Volcados de tablas y procedimientos.sql
└── README.md
```

Todas las páginas y controladores incluyen `src/AccesoDatos.php` con `require_once`.
Los controladores de `php/` añaden `src/Respuesta.php` para devolver JSON al
`fetch` de JavaScript.

---

## Base de datos

La base se llama `dinocards` y tiene tres tablas:

| Tabla | Contenido |
|---|---|
| `usuarios` | Cuentas: usuario, correo, contraseña, última obtención y sobre de prueba. |
| `dinosaurios` | Catálogo de 100 especies con estadísticas y ruta de la imagen (`image_url`). |
| `colecciones` | Cartas que tiene cada usuario (usuario, dinosaurio y fecha). |

Los volcados están en `sql/`. Los procedimientos se instalan con:

```
C:\xampp\mysql\bin\mysql.exe -u oscar -p dinocards < sql\procedimientos.sql
```

Procedimientos propios del proyecto: `Registro`, `ObtenerUsuarioLogin`,
`MigrarPassword`, `ObtenerUsuarioPorId`, `ObtenerColeccion`, `AbrirCartaDiaria`,
`AnadirCartaElegida`, `AnadirCartasPrueba`, `ObtenerResumenColeccion`,
`ObtenerUltimasCartas`, `ObtenerDinosauriosPorIds`, `ContarDinosaurios`,
`ListarDinosaurios` y `ActualizarImagenDinosaurio`.

---

## Configuración

Las credenciales no están dentro del proyecto. `src/AccesoDatos.php` lee, en este orden:

1. Variables de entorno `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`.
2. `DB_CONFIG` o, por defecto, `C:/xampp/config-dinocards.php`.

Ese archivo devuelve un array con `host`, `port`, `database`, `username` y `password`.

---

## Cómo ejecutar en XAMPP

1. Copia la carpeta `dino-cambios` dentro de `C:\xampp\htdocs\ejer\`.
2. Arranca **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Crea la base de datos importando los volcados de `sql/` y ejecuta
   `sql\procedimientos.sql`.
4. Revisa `C:\xampp\config-dinocards.php` o las variables de entorno.
5. Abre en el navegador:
   ```
   http://localhost:8080/ejer/dino-cambios/
   ```

> Usuario de prueba: **oscar@gmail.com** / **1234**

---

## Notas técnicas y seguridad

- Las operaciones de datos se hacen mediante procedimientos almacenados y
  sentencias preparadas; no hay SQL de negocio suelto en PHP.
- Las salidas se escapan con `htmlspecialchars()` antes de mostrarse en HTML.
- Las contraseñas nuevas se guardan con `password_hash()`; el login admite
  contraseñas antiguas en md5 y las migra automáticamente.
- El frontend consume los controladores de `php/` mediante `fetch` y JSON.
- La interfaz es responsive y usa variables CSS, Flexbox y Grid.
