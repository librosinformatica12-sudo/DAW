# DinoCards

Aplicación web de coleccionismo de cartas de dinosaurios. Cada jugador crea su
cuenta y va reuniendo cartas de un catálogo de **100 dinosaurios**, cada uno con
sus estadísticas (HP, vigor, ataque, defensa y agilidad).

El objetivo es **completar la colección**: consigues cartas nuevas abriendo
sobres y puedes consultar toda la información de cada especie en la **DinoPedia**.
Las cartas repetidas también se guardan, así que poco a poco vas llenando el álbum.

Proyecto de **Desarrollo de Aplicaciones Web (nivel medio)** realizado con
**HTML, CSS y JavaScript** en el frontend y **PHP 8 + MySQL (PDO)** en el backend.

---

## Cómo funciona (reglas del juego)

- **Sobre del día**: una carta gratis cada 24 horas. Si ya lo has abierto hoy, el
  botón queda desactivado hasta el día siguiente.
- **Sobre de prueba**: entrega **4 cartas aleatorias** de golpe, sin límite diario.
- **Mi colección**: todas las cartas conseguidas, agrupadas, con buscador, orden
  y barra de progreso.
- **DinoPedia**: catálogo completo con búsqueda y filtro por periodo; desde cada
  ficha puedes añadir la carta a tu colección.
- **Mi perfil**: datos de la cuenta y estadísticas de la colección.

---

## Funcionalidades

- Registro e inicio de sesión de usuarios (contraseñas cifradas).
- **Sobre del día**: una carta gratuita cada 24 horas.
- **Sobre de prueba**: 4 cartas aleatorias cuando quieras.
- **Mi colección**: cartas agrupadas, con buscador, orden y barra de progreso.
- **DinoPedia**: catálogo completo con búsqueda, filtro por periodo y botón para
  añadir cada carta a la colección.
- **Mi perfil**: datos del usuario y estadísticas de su colección.

---

## Tecnologías

- **PHP 8** con **MySQLi** y sentencias preparadas.
- **MySQL 8** — base de datos `dinocards` (3 tablas + procedimientos almacenados).
- **JavaScript** moderno (módulos ES6) consumiendo la API con `fetch`.
- **CSS** propio con variables, Flexbox, Grid y diseño responsive.
- **XAMPP** (Apache + MySQL) como entorno local.

---

## Estructura del proyecto

```
dino-prueba/
├── index.php          Entrada: iniciar sesión / registrarse
├── inicio.php         Panel principal (sobres y resumen)
├── coleccion.php      Mi colección
├── dinopedia.php      Catálogo de dinosaurios
├── perfil.php         Ficha del usuario
├── partials/
│   └── cabecera.php   Cabecera común (navegación)
├── php/               Endpoints que devuelven JSON (usados con fetch)
│   ├── login.php
│   ├── registro.php
│   ├── cartaDiaria.php
│   ├── abrirSobrePrueba.php
│   ├── añadirCarta.php
│   └── logout.php
├── src/                Capa de datos y reglas de negocio (MySQLi)
│   ├── AccesoDatos.php
│   ├── UsuarioService.php
│   ├── ColeccionService.php
│   └── ReglasColeccion.php
├── styles/            Estilos (comun.css, paginas.css, perfil.css)
├── script/            JavaScript (script.js, inicio.js, coleccion.js…)
├── doc SQL/           Scripts SQL de instalación y copias de seguridad
├── img/               Imágenes de cartas y sobres
└── README.md
```

---

## Base de datos

La base se llama `dinocards` y tiene tres tablas:

| Tabla | Contenido |
|---|---|
| `usuarios` | Cuentas: usuario, correo, contraseña, última obtención y sobre de prueba. |
| `dinosaurios` | Catálogo de 100 especies con estadísticas y ruta de la imagen. |
| `colecciones` | Cartas que tiene cada usuario (usuario, dinosaurio y fecha). |

Procedimientos almacenados: `Registro`, `Login`, `AñadirNuevaCartaAColeccion` y
`ObtenerColeccion`. El script con la estructura y los datos está en
`doc SQL/instalar bueno.sql` (instala también las 100 cartas y los usuarios de prueba).

---

## Cómo ejecutar en XAMPP

1. La carpeta debe estar en
   `C:\xampp\htdocs\ejer\examen\examenes\servidor\dino-prueba`.
2. Arranca **Apache** y **MySQL** desde el panel de control de XAMPP (puerto 80
   y 3306).
3. Crea la base de datos (una sola vez) importando el script SQL:
   - Opción A (phpMyAdmin): importa `doc SQL/instalar bueno.sql`.
   - Opción B (línea de comandos con el cliente de MySQL 8):
     ```
     "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" -u root -p < "doc SQL\instalar bueno.sql"
     ```
4. Las credenciales están en `src/AccesoDatos.php`: usuario `root`, contraseña
   `1234`, base `dinocards`. `C:\xampp\mysql\bin\mysql.exe` no sirve para importar
   aquí porque le falta el plugin `caching_sha2_password`.
5. Abre en el navegador:
   ```
   http://localhost/ejer/examen/examenes/servidor/dino-prueba/
   ```

> Usuario de prueba: **oscar@gmail.com** / **1234**

---

## Notas técnicas y seguridad

- Las consultas usan **MySQLi con sentencias preparadas** (evitan inyecciones SQL).
- Las salidas se escapan con `htmlspecialchars()` antes de mostrarlas en HTML.
- Las nuevas contraseñas se guardan con `password_hash()`; el login también
  admite contraseñas antiguas en md5 para no perder usuarios ya existentes.
- El frontend consume la carpeta `api/` mediante `fetch` y JSON.
- La interfaz es **responsive** y usa variables CSS, Flexbox y Grid.
- Las credenciales de MySQL están en `src/AccesoDatos.php`; en un entorno real
  convendría moverlas a variables de entorno.

---

## Posibles mejoras

- Evitar cartas repetidas hasta completar el catálogo (reglas de duplicados).
- Añadir más sobres (eventos, sobres de pago, sobres temáticos).
- Paginación o scroll infinito en la DinoPedia.
- Mover las credenciales a variables de entorno y servir por HTTPS.
