# DinoCards

Aplicación web para coleccionar cartas de dinosaurios. Cada día puedes abrir un
sobre gratuito, consultar el catálogo completo y añadir dinosaurios a tu
colección personal.

Proyecto de **Desarrollo de Aplicaciones Web (nivel medio)** realizado con
**HTML, CSS y JavaScript** en el frontend y **PHP + MySQL (PDO)** en el backend.

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

## Notas técnicas

- Las consultas usan **PDO con sentencias preparadas** (evitan inyecciones SQL).
- Las nuevas contraseñas se guardan con `password_hash()`; el login también
  admite contraseñas antiguas en md5 para no perder usuarios ya existentes.
- El frontend consume la carpeta `api/` mediante `fetch` y JSON.
- La interfaz es **responsive** y usa variables CSS, Flexbox y Grid.
