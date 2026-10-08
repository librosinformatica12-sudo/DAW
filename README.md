# 🦕 DinoCards — Portal del Coleccionista

**DinoCards V1.0** es un portal web para coleccionistas de cartas de dinosaurios. Incluye registro de usuarios, inicio de sesión y un panel personal donde cada jugador puede consultar su colección.

> Este repositorio (`DAW`) contiene también los ejercicios del ciclo de **Desarrollo de Aplicaciones Web**. El proyecto Dinocards vive en la carpeta [`dino-prueba/`](dino-prueba/).

---

## ✨ Funcionalidades actuales

| Módulo | Descripción |
|---|---|
| 🔐 **Registro** | Crear cuenta con nombre, usuario, email y contraseña (validación por servidor y mensajes JSON). |
| 🔓 **Login** | Iniciar sesión por usuario/email; la sesión se guarda con `session_start()`. |
| 🧑‍🔬 **Panel del coleccionista** | Muestra datos de la cuenta: usuario, email, fecha de alta, última carta obtenida y **total de cartas** de la colección. |
| 🚪 **Logout** | Cierra la sesión y devuelve a la portada. |
| 📱 **Responsive** | CSS y JS propios (`style.css`, `script.js`). |

---

## 🛠️ Tecnologías

- **PHP 8+** con `mysqli` preparado y `session_start()`.
- **MySQL** — base de datos `dinocards` con **procedimientos almacenados** (`Registro` y `Login`) que devuelven códigos de error.
- **JavaScript + CSS** — panel con pestañas de login/registro, botón "Mostrar" contraseña y estilos propios.
- **XAMPP** como entorno local.

---

## 📂 Estructura del proyecto

```
dino-prueba/
├── index.php            # Portada: login y registro
├── inicio.php           # Panel del coleccionista (requiere sesión)
├── php/
│   ├── login.php        # Procesa el login (JSON)
│   ├── registro.php     # Procesa el registro (JSON)
│   └── logout.php       # Cierra sesión
├── src/
│   └── AccesoDatos.php  # Conexión MySQL + llamadas a los SP Registro/Login
├── script/script.js     # Interacción del cliente
├── styles/style.css     # Estilos
├── doc SQL/             # Scripts de la base de datos (generación de tablas)
└── sincronizar.bat      # Sube los cambios de dino-prueba a GitHub
```

---

## 🚀 Cómo montarlo en local (XAMPP)

1. Copia la carpeta `dino-prueba/` dentro de `C:\xampp\htdocs\`.
2. Importa el script SQL de `doc SQL/` en phpMyAdmin para crear la base `dinocards` (tablas y procedimientos almacenados).
3. Revisa la conexión en `src/AccesoDatos.php` (host, usuario, contraseña y puerto).
4. Arranca Apache y MySQL en el panel de XAMPP y entra en: `http://localhost/dino-prueba/`.

---

## 🧠 Idea del proyecto (reglas)

Un coleccionista **reclama una carta nueva cada día**, abre su sobre, y va construyendo su colección. La idea se compone de tres pilares:

- 🎁 **Carta diaria** — un sobre gratis cada día con un dinosaurio nuevo.
- 🗂️ **Mi colección** — todas las cartas conseguidas (hoy muestra el total, más adelante el catálogo completo).
- 📚 **DinoPedia** — guía de todas las especies con sus estadísticas.

---

## 🎯 Próximos pasos (ideas)

- Añadir cartas a la colección (módulo "abrir sobre / carta diaria").
- Página de colección con el catálogo de dinosaurios y sus estadísticas.
- Sistema de reglas para eventos/duplicados (pendiente de decidir con la lógica de negocio).
- Separar mejor la lógica (`ColeccionService`, `ReglasColeccion`, `UsuarioService`).

---

## 🔐 Seguridad (pendiente)

- Las credenciales de MySQL están **en texto plano** en `AccesoDatos.php` → se recomienda usar variables de entorno.
- El hash de contraseñas es `md5` → migrar a `password_hash()` / `password_verify()`.

---

## 🗂️ Trabajar con Git en varios dispositivos

```bash
git pull                    # Antes de empezar: descargar lo último
# … editar tus archivos …
git add .                   # (o: git add dino-prueba)
git commit -m "descripción"
git push origin main        # Subir a GitHub

git clone https://github.com/librosinformatica12-sudo/DAW   # Descargar trabajo DAW
```

También hay dos accesos rápidos para sincronizar con doble clic:

| Archivo | Qué hace |
|---|---|
| `dino-prueba/sincronizar.bat` | Solo sube cambios de `dino-prueba/` |
| `sincronizar-todo.bat` | Sube **todo** el repositorio y genera un informe de cambios en `cambios-todo/` |
