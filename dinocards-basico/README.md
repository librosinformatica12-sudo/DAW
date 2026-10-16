# Dinocards Básico

Una versión simplificada de **Dinocards** creada para practicar el funcionamiento de un portal de coleccionistas con **PHP, MySQL y sesiones**.

El objetivo es tener una base clara, limpia y fácil de entender para seguir ampliando el proyecto poco a poco (carta diaria, colección, DinoPedia...).

## Índice

- [Descripción](#descripción)
- [Tecnologías](#tecnologías)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Base de datos](#base-de-datos)
- [Instalación (XAMPP)](#instalación-xampp)
- [Funcionamiento](#funcionamiento)
- [Carpeta `img`](#carpeta-img)
- [Notas](#notas)

## Descripción

Dinocards Básico permite:

- Registrar un usuario (validación de campos, comprobación de usuario/email duplicados y hash de contraseña)
- Iniciar/cerrar sesión
- Ver un **panel de inicio** con los datos de la cuenta
- Visualizar la **colección** de cartas del usuario
- Consultar la **DinoPedia** con todas las cartas/dinosaurios disponibles

Todo con una estructura sencilla, sin procedimientos almacenados, usando sentencias `mysqli` preparadas.

## Tecnologías

- [PHP](https://www.php.net/) (mysqli, sesiones)
- [MySQL](https://www.mysql.com/)
- [HTML5, CSS3 y JavaScript](https://developer.mozilla.org/es/)
- [XAMPP](https://www.apachefriends.org/es/index.html) (entorno local)

## Estructura del proyecto

```text
dinocards-basico/
├── abrir-dinocards.bat
├── cerrar.php
├── coleccion.php
├── conexion.php        # Conexión a BD + funciones auxiliares
├── dinopedia.php       # Catálogo completo de dinosaurios
├── img/                # Imágenes (cartas y sobres)
├── index.php           # Login + Registro
├── inicio.php          # Panel principal
├── menu.php            # Cabecera/navegación reutilizable
├── perfil.php
├── script.js
└── style.css
```

## Base de datos

La base de datos se llama `dinocards`.

Tablas principales:

- **`usuarios`** – Datos del usuario (username, email, contraseña en MD5, fecha de registro, última obtención...)
- **`dinosaurios`** – Catálogo de dinosaurios con sus estadísticas (nombre, especie, periodo, imagen, altura, largo, peso, hp, vigor, ataque, defensa, agilidad...)
- **`colecciones`** – Relación `usuario_id` ↔ `dinosaurio_id` con `fecha_adquisicion`

Los scripts SQL están disponibles en la carpeta `MySQL/` del repositorio `DAW` para importar fácilmente desde phpMyAdmin.

## Instalación (XAMPP)

1. **Clonar el repositorio**:

   ```bash
   git clone https://github.com/librosinformatica12-sudo/dinocards-basico.git
   ```

   O copiar la carpeta `dinocards-basico/` directamente en `C:\xampp\htdocs\`

2. **Importar la base de datos**

   - Abrir [phpMyAdmin](http://localhost/phpmyadmin)
   - Crear la base `dinocards` (collation `utf8mb4_general_ci` o `utf8mb4_0900_ai_ci`)
   - Importar los archivos `.sql` correspondientes (`usuarios`, `dinosaurios`, `colecciones`)

3. **Configurar conexión**

   Editar `conexion.php` con tus datos de MySQL:

   ```php
   $host       = "127.0.0.1";
   $usuario_bd = "tu_usuario";
   $clave_bd   = "tu_contraseña";
   $base_datos = "dinocards";
   ```

4. **Arrancar y probar**

   - Iniciar Apache y MySQL en XAMPP
   - Acceder a: [http://localhost/dinocards-basico/](http://localhost/dinocards-basico/)
   - Crear una cuenta desde el formulario de registro e iniciar sesión

## Funcionamiento

1. **Registro/Login** – `index.php` gestiona ambas pestañas. Al registrarse se guarda un hash MD5 de la contraseña.
2. **Sesión** – `conexion.php` inicia sesión y comprueba con `esta_logueado()`.
3. **Navegación** – `menu.php` se incluye para mantener el mismo menú en todas las páginas.
4. **Colección** – `coleccion.php` muestra solo las cartas que tiene el usuario logueado.
5. **DinoPedia** – `dinopedia.php` lista todo el catálogo con imagen, nombre y datos básicos.

## Carpeta `img`

Contiene las imágenes de cartas (`img-cartas/`) y sobres (`img-sobres/`). Se usan tanto en `coleccion.php` como en `dinopedia.php`.

## Notas

- Se utiliza `md5()` para el cifrado de contraseñas por simplicidad. Para un proyecto más real se recomienda migrar a [`password_hash()`](https://www.php.net/manual/es/function.password-hash.php) y [`password_verify()`](https://www.php.net/manual/es/function.password-verify.php).
- El proyecto está pensado para **aprender y practicar**. Se irá mejorando añadiendo la "carta diaria", lógica de duplicados, etc.
- Todos los datos se escapan con `htmlspecialchars()` (`function e()`/`escapar()`) para evitar XSS.