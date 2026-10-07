# DinoCards — FrontEnd SPA + API PHP

Proyecto preparado para XAMPP/WAMP/Docker siguiendo la arquitectura del documento del proyecto: HTML5 semántico, CSS3 con Grid/Flexbox, JavaScript ES6+ con clases, DOM y SPA sin recarga completa.

## Estructura

```text
DinoCards/
├── index.php
├── config/
│   └── db.php
├── api/
│   ├── auth.php
│   └── collection.php
├── js/
│   ├── app.js
│   └── models/
│       ├── Usuario.js
│       └── Dinosaurio.js
└── css/
    └── styles.css
```

## Instalación en XAMPP

1. Copia `DinoCards` dentro de `C:/xampp/htdocs/`.
2. Abre phpMyAdmin.
3. Ejecuta el SQL `generacion_dinocards (1).sql`.
4. Comprueba `config/db.php`:
   - host: `127.0.0.1`
   - base de datos: `DinoCards`
   - usuario: `root`
   - contraseña: vacía por defecto en XAMPP.
5. Inicia Apache y MySQL.
6. Abre `http://localhost/DinoCards/`.

## Login flexible

El campo de login acepta tanto `username` como `email`. El backend localiza el usuario y después llama al procedimiento almacenado `Login` usando el email que exige el procedimiento.

## Contraseñas

El SQL entregado contiene registros iniciales con hashes de 32 caracteres compatibles con MD5. Para no romper esos datos, el login intenta primero `password_hash()` y después MD5. Los nuevos registros se guardan mediante `password_hash(PASSWORD_DEFAULT)` antes de llamar al PA `Registro`.

> Recomendación para producción: migrar las contraseñas antiguas a `password_hash()` y eliminar la compatibilidad MD5.

## Nota sobre image_url

El SQL inicial deja `image_url` en `NULL`, así que las cartas muestran un placeholder de dinosaurio hasta que se añadan URLs de imágenes a la tabla.
