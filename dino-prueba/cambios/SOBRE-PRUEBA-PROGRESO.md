# Sobre de Prueba — progreso del tutorial

**Proyecto:** DinoCards (`C:\xampp\htdocs\ejer\dino-prueba`)
**Objetivo:** programar el "Sobre de Prueba" para que dé **4 cartas diferentes aleatorias**, con espera de 5 minutos entre aperturas.
**Modo:** paso a paso (yo explico, tú escribes el código).

---

## Plan de pasos
1. BD: crear columna `carta_prueba` en `usuarios`.
2. PHP: que `obtenerUsuarioPorId()` lea `carta_prueba`.
3. Servicios: `dinosauriosAleatorios($n)` + `marcarCartaPrueba()`.
4. Servicio: `insertarCarta()` (inserta sin tocar `ultima_obtencion`).
5. Endpoint: `php/abrirSobrePrueba.php`.
6. JS: enganchar el botón `[data-sobre="prueba"]` + probar.

---

## Estado

### ✅ Paso 1 — HECHO
Columna creada:
```sql
ALTER TABLE usuarios
ADD COLUMN carta_prueba DATETIME NULL DEFAULT NULL AFTER ultima_obtencion;
```

### ✅ Paso 2 — HECHO
- `src\UsuarioService.php` → `obtenerUsuarioPorId()` ya selecciona `carta_prueba`:
```php
'SELECT id, username, email, fecha_registro, ultima_obtencion, carta_prueba
 FROM usuarios WHERE id = ? LIMIT 1'
```
- `inicio.php` línea 56 → `$ultimaPrueba = $usuarioActual['carta_prueba'] ?? null;`

### ✅ Paso 3 — HECHO (una función tiene un typo, ver abajo)
- `src\ColeccionService.php` → `dinosauriosAleatorios($n)`
- `src\UsuarioService.php` → `marcarCartaPrueba($usuarioId)`

### 🟡 Paso 4 — A MEDIO (dos errores que corregir)
- `src\ColeccionService.php` → `insertarCarta($usuarioId, $cartaId)`

---

## ⚠️ ERRORES PENDIENTES DE CORREGIR (hazlo antes de seguir)

### Error 1 — nombre de función mal escrito (`src\ColeccionService.php`, línea ~232)
Está así (❌):
```php
function dunosauriosAleatorios(int $n): array
```
Debe ser (✅):
```php
function dinosauriosAleatorios(int $n): array
```

### Error 2 — backticks en vez de comillas simples (`src\ColeccionService.php`, en `insertarCarta`)
Está así (❌) — las backticks `` ` `` en PHP ejecutan comandos del sistema:
```php
$stmt = $connection -> prepare (
        `INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
        VALUES (?, ?, NOW())`
);
```
Debe ser (✅) con comillas simples:
```php
$stmt = $connection->prepare(
    'INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
     VALUES (?, ?, NOW())'
);
```
(Detalle opcional: quitar el espacio de `$connection -> prepare` y `$stmt-> bind_param` → `$connection->prepare`, `$stmt->bind_param`.)

---

## Funciones que YA deben estar en los archivos

### `src\ColeccionService.php`
```php
/** Devuelve N dinosaurios al azar (sin repetir), o [] si no hay ninguno. */
function dinosauriosAleatorios(int $n): array
{
    global $connection;

    $n = max(1, min(50, $n));

    if ($connection->connect_errno) {
        return [];
    }

    $res = $connection->query('SELECT * FROM dinosaurios ORDER BY RAND() LIMIT ' . $n);
    if (!$res) {
        return [];
    }

    $filas = [];
    while ($fila = $res->fetch_assoc()) {
        $filas[] = $fila;
    }
    $res->free();

    return $filas;
}

/** Inserta una carta SIN tocar ultima_obtencion (sobre de prueba). */
function insertarCarta(int $usuarioId, int $cartaId): bool
{
    global $connection;

    if ($connection->connect_errno) {
        return false;
    }

    $stmt = $connection->prepare(
        'INSERT INTO colecciones (usuario_id, dinosaurio_id, fecha_adquisicion)
         VALUES (?, ?, NOW())'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ii', $usuarioId, $cartaId);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}
```

### `src\UsuarioService.php`
```php
/** Actualiza la fecha/hora de la última apertura del sobre de prueba. */
function marcarCartaPrueba(int $usuarioId): void
{
    global $connection;

    if ($connection->connect_errno) {
        return;
    }

    $stmt = $connection->prepare('UPDATE usuarios SET carta_prueba = NOW() WHERE id = ?');
    if (!$stmt) {
        return;
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $stmt->close();
}
```

---

## 📌 Nombres exactos (para no equivocarse)
- Columna BD: **`carta_prueba`** (guion bajo, NO guion).
- Función aleatorios: **`dinosauriosAleatorios`**.
- Función insertar: **`insertarCarta`**.
- Función marcar: **`marcarCartaPrueba`**.

---

## Siguiente: Paso 5 — Endpoint `php/abrirSobrePrueba.php`

Debe hacer (siguiendo el patrón de `php/cartaDiaria.php`):
1. `session_start()`, cabecera JSON, función `responder()`.
2. Comprobar método POST y sesión.
3. Leer usuario (`obtenerUsuarioPorId`).
4. Comprobar espera: si `carta_prueba` existe y `time() - strtotime(...) < 300` → responder `false` con segundos restantes.
5. `$dinos = dinosauriosAleatorios(4);`
6. Recorrer y `insertarCarta($usuarioId, (int) $d["id"]);`
7. `marcarCartaPrueba($usuarioId);`
8. Responder `true` con `['dinosaurios' => $dinos]`.

Nota: la espera (300 s) debe coincidir con la de `inicio.php` (línea 55: `$esperaPrueba = 300;`).

---

## Siguiente: Paso 6 — JS
- En `script/inicio.js`, enganchar el botón `button[data-sobre="prueba"]`.
- Al hacer clic: `fetch('php/abrirSobrePrueba.php', { method:'POST' })`.
- Pintar las 4 cartas (reutilizar el HTML de `mostrarCarta`).
- Manejar la espera/cooldown.

---

## Cómo retomar
Cuando vuelvas, dime: **"seguimos con el Paso 5"** (después de corregir los 2 errores del Paso 4).
