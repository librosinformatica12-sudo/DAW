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

- **PHP 8** con **PDO** y sentencias preparadas.
- **MySQL 8** — base de datos `dinocards` (3 tablas + procedimientos almacenados).
- **JavaScript** moderno (módulos ES6) consumiendo la API con `fetch`.
- **CSS** propio con variables, Flexbox, Grid y diseño responsive.
- **XAMPP** (Apache + MySQL) como entorno local.

---

## Estructura del proyecto

```
dinocards/
├── index.php          Entrada: iniciar sesión / registrarse
├── inicio.php         Panel principal (sobres y resumen)
├── coleccion.php      Mi colección
├── dinopedia.php      Catálogo de dinosaurios
├── perfil.php         Ficha del usuario
├── logout.php         Cerrar sesión
├── includes/
│   ├── conexion.php   Conexión PDO a la base de datos
│   ├── funciones.php  Funciones de ayuda (sesión, escape, JSON)
│   ├── cabecera.php   Cabecera común (<head> + navegación)
│   └── pie.php        Pie de página común
├── api/               Endpoints que devuelven JSON (usados con fetch)
│   ├── login.php
│   ├── registro.php
│   ├── carta_diaria.php
│   ├── abrir_prueba.php
│   ├── anadir_carta.php
│   ├── dinosaurios.php
│   └── coleccion.php
├── css/
│   └── estilos.css    Estilos (variables, Flexbox, Grid, responsive)
├── js/                JavaScript moderno (módulos ES6)
│   ├── main.js        Funciones comunes (fetch, crear carta…)
│   ├── index.js
│   ├── inicio.js
│   ├── coleccion.js
│   └── dinopedia.js
├── img/
│   ├── img-cartas/    Imágenes de las cartas
│   └── img-sobres/    Imágenes de los sobres
├── sql/
│   └── dinocards.sql  Copia de la base de datos (estructura y datos)
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
`sql/dinocards.sql`.

---

## Cómo ejecutar en XAMPP

1. Copia la carpeta `dinocards` dentro de `C:\xampp\htdocs\ejer\`.
2. Arranca **Apache** y **MySQL** desde el panel de control de XAMPP.
3. Crea la base de datos (una sola vez) importando el script SQL:
   - Opción A (phpMyAdmin): importa `sql/dinocards.sql`.
   - Opción B (línea de comandos):
     ```
     C:\xampp\mysql\bin\mysql.exe -u oscar -p < sql\dinocards.sql
     ```
4. Revisa las credenciales en `includes/conexion.php`
   (`DB_USER`, `DB_PASS`, `DB_NAME`).
5. Abre en el navegador:
   ```
   http://localhost:8080/ejer/dinocards/
   ```

> Usuario de prueba: **oscar@gmail.com** / **1234**

---

## Notas técnicas y seguridad

- Las consultas usan **PDO con sentencias preparadas** (evitan inyecciones SQL).
- Las salidas se escapan con `htmlspecialchars()` antes de mostrarlas en HTML.
- Las nuevas contraseñas se guardan con `password_hash()`; el login también
  admite contraseñas antiguas en md5 para no perder usuarios ya existentes.
- El frontend consume la carpeta `api/` mediante `fetch` y JSON.
- La interfaz es **responsive** y usa variables CSS, Flexbox y Grid.
- Las credenciales de MySQL están en `includes/conexion.php`; en un entorno real
  convendría moverlas a variables de entorno.

---

## Posibles mejoras

- Evitar cartas repetidas hasta completar el catálogo (reglas de duplicados).
- Añadir más sobres (eventos, sobres de pago, sobres temáticos).
- Paginación o scroll infinito en la DinoPedia.
- Mover las credenciales a variables de entorno y servir por HTTPS.
