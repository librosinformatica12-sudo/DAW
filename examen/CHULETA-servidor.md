[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# CHULETA — SERVIDOR (MySQL + PHP + HTML)

## 1. El viaje del dato

```
portal.html
  <form action="gestionar_x.php" method="post">
        name="campo"
          ↓
gestionar_x.php
  session_start();
  require_once __DIR__ . '/AccesoDatos.php';
  $_POST['campo']  → validar → PA_Alta(...)
          ↓
AccesoDatos.php
  prepare('CALL Alta(?, ?, @resultado)')
  bind_param('si', ...)
  execute()
  query('SELECT @resultado AS resultado')
          ↓
MySQL
  procedure Alta(... OUT _res INT)
  IF error → SET _res = -1; ELSE INSERT; SET _res = 0;
          ↓
gestionar_x.php
  if ($res === -1) mensaje = '...';
  header('Location: ...') o pintar tabla
```

## 2. Formulario HTML

```html
<form action="gestionar_citas.php" method="post">
    <label for="paciente">Paciente:</label>
    <input type="text" id="paciente" name="paciente" maxlength="50" required>

    <label for="fecha">Fecha:</label>
    <input type="datetime-local" id="fecha" name="fecha_hora" required>

    <label for="precio">Precio:</label>
    <input type="number" id="precio" name="precio" min="0" step="0.01">

    <select name="especialidad">
        <option value="">-- Elige --</option>
        <option value="Pediatría">Pediatría</option>
    </select>

    <input type="hidden" name="accion" value="alta">
    <button type="submit">Guardar</button>
</form>
```

**Truco:** el `name` del input es la clave que aparece en `$_POST['name']`.

## 3. Recibir y validar en PHP

```php
require_once __DIR__ . '/AccesoDatos.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paciente = trim($_POST['paciente'] ?? '');
    $fecha    = trim($_POST['fecha_hora'] ?? '');
    $doctor   = (int)($_POST['doctor'] ?? 0);

    if ($paciente === '') {
        $mensaje = 'El nombre no puede estar vacío.';
    } elseif ($fecha === '') {
        $mensaje = 'La fecha no puede estar vacía.';
    } else {
        $resultado = PA_AltaCita($paciente, $fecha, $doctor);
    }
}
```

Funciones útiles:

| Quiero... | Código |
|---|---|
| Saber si llega un campo | `isset($_POST['x'])` |
| Valor por defecto | `$_POST['x'] ?? ''` |
| Quitar espacios | `trim($texto)` |
| Convertir a entero | `(int)$texto` |
| Comprobar vacío | `$texto === ''` |
| Validar email | `filter_var($email, FILTER_VALIDATE_EMAIL)` |
| Evitar XSS al imprimir | `htmlspecialchars($texto)` |
| Redirigir | `header('Location: portal.html'); exit;` |
| Sesión | `session_start(); $_SESSION['x'] = 1;` |

## 4. Conexión MySQL (AccesoDatos.php)

```php
<?php
$host = '127.0.0.1';
$port = 3306;
$database = 'clinica_examen';
$username = 'root';
$password = '1234';      // cámbialo si tu MySQL usa otra

mysqli_report(MYSQLI_REPORT_OFF);
$connection = @new mysqli($host, $username, $password, $database, $port);
```

## 5. Consulta SELECT con prepared statement

```php
function PA_ListaCitas()
{
    global $connection;

    $stmt = $connection->prepare('CALL ListaCitas()');

    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }

    $citas = [];
    $resultado = $stmt->get_result();
    while ($fila = $resultado->fetch_assoc()) {
        $citas[] = $fila;
    }
    $resultado->free();
    $stmt->close();

    return $citas;
}
```

## 6. Procedimiento con parámetro OUT

```php
function PA_AltaCita(string $paciente, string $fecha, int $doctor)
{
    global $connection;

    $stmt = $connection->prepare('CALL AltaCita(?, ?, ?, @resultado)');
    $stmt->bind_param('ssi', $paciente, $fecha, $doctor);

    if (!$stmt->execute()) {
        throw new RuntimeException($stmt->error);
    }
    $stmt->close();

    $resultado = $connection->query('SELECT @resultado AS resultado');
    $fila = $resultado->fetch_assoc();
    $resultado->free();

    return $fila['resultado'];
}
```

### Letras de `bind_param`

| Letra | Tipo | Ejemplo |
|---|---|---|
| `s` | string | nombre, fecha, especialidad |
| `i` | integer | id, licencia, cantidad |
| `d` | double/decimal | precio |

**El orden de las letras debe coincidir con el orden de los `?`.**

## 7. Tablas SQL

```sql
CREATE DATABASE IF NOT EXISTS clinica_examen;
USE clinica_examen;

DROP TABLE IF EXISTS citas;
DROP TABLE IF EXISTS doctores;

CREATE TABLE doctores (
    n_licencia INT PRIMARY KEY,
    nombre     VARCHAR(50) NOT NULL,
    apellidos  VARCHAR(50) NOT NULL
);

CREATE TABLE citas (
    id_cita      INT AUTO_INCREMENT PRIMARY KEY,
    paciente     VARCHAR(50) NOT NULL,
    especialidad ENUM('Medicina General','Pediatría','Dermatología','Traumatología'),
    fecha        DATETIME NOT NULL,
    fk_doctor    INT NOT NULL,
    FOREIGN KEY (fk_doctor) REFERENCES doctores(n_licencia)
);
```

## 8. Procedimiento almacenado (patrón de códigos)

```sql
DROP PROCEDURE IF EXISTS AltaCita;

DELIMITER //
CREATE PROCEDURE AltaCita(
    IN  _paciente VARCHAR(50),
    IN  _fecha    DATETIME,
    IN  _doctor   INT,
    OUT _res      INT
)
BEGIN
    IF (_paciente = '' OR _paciente IS NULL) THEN
        SET _res = -1;                                  -- nombre vacío
    ELSEIF (_fecha IS NULL OR _fecha = '') THEN
        SET _res = -3;                                  -- fecha vacía
    ELSEIF (_fecha <= NOW()) THEN
        SET _res = -4;                                  -- fecha pasada
    ELSEIF NOT EXISTS (SELECT 1 FROM doctores WHERE n_licencia = _doctor) THEN
        SET _res = -5;                                  -- doctor no existe
    ELSE
        INSERT INTO citas(paciente, especialidad, fecha, fk_doctor)
        VALUES(_paciente, 'Medicina General', _fecha, _doctor);

        SET _res = 0;                                   -- TODO OK
    END IF;
END//
DELIMITER ;
```

Convención: `0` = OK, negativos = error. **Usa siempre la misma en PHP y en SQL.**

## 9. JOIN y UPDATE

```sql
-- Lista con el nombre del doctor
SELECT c.*, d.nombre AS doctor
FROM citas c
JOIN doctores d ON c.fk_doctor = d.n_licencia;

-- Modificar
UPDATE citas
SET paciente = _paciente, fecha = _fecha
WHERE id_cita = _id;

-- Borrar
DELETE FROM citas WHERE id_cita = _id;
```

## 10. Fecha `datetime-local`

El input envía `2026-11-10T18:30`. MySQL espera `2026-11-10 18:30:00`.

```php
$fecha = date('Y-m-d H:i:s', strtotime($_POST['fecha_hora']));
```

## 11. Los 12 huecos típicos (con respuesta)

1. **Enviar a PHP** → `<form action="gestionar.php" method="post">`
2. **Nombre del campo** → `name="paciente"`
3. **Recibir el campo** → `$_POST['paciente']`
4. **Comprobar que llega** → `isset($_POST['paciente'])`
5. **Iniciar sesión** → `session_start();`
6. **Incluir la conexión** → `require_once __DIR__ . '/AccesoDatos.php';`
7. **Preparar llamada** → `$connection->prepare('CALL Alta(?, ?, @resultado)');`
8. **Enlazar parámetros** → `$stmt->bind_param('si', $texto, $numero);`
9. **Ejecutar** → `$stmt->execute();`
10. **Recoger el OUT** → `$connection->query('SELECT @resultado AS resultado')->fetch_assoc()['resultado'];`
11. **Recorrer resultados** → `while ($fila = $resultado->fetch_assoc()) { ... }`
12. **Imprimir seguro** → `htmlspecialchars($fila['paciente'])`

## 12. Errores que restan puntos

- Poner `session_start()` después de imprimir HTML → *headers already sent*.
- `bind_param('s', ...)` para un número, o letras en orden distinto a los `?`.
- Olvidar `SELECT @resultado` después del `CALL`.
- No comprobar `execute()`.
- Concatenar `$_POST` dentro del SQL → inyección SQL (usa `?`).
- Imprimir datos del usuario sin `htmlspecialchars` → XSS.
- Confundir el nombre del input (`nombre_concierto`) con la columna (`nombre_banda`).
- Usar `==` con tipos raros para el OUT: el valor puede volver como string. Usa `==` o castea a `(int)`.

## 13. Checklist antes de entregar

- [ ] `name` del HTML = clave de `$_POST`.
- [ ] `action` apunta al PHP correcto.
- [ ] `session_start()` antes de cualquier HTML.
- [ ] `require_once` de `AccesoDatos.php`.
- [ ] `bind_param` con letras y orden correctos.
- [ ] `SELECT @resultado` tras el `CALL` con OUT.
- [ ] Mensajes para cada código negativo.
- [ ] `htmlspecialchars` al pintar datos.
- [ ] El procedure tiene `IN`/`OUT` y `DELIMITER`.
- [ ] La tabla se crea con PK, FK y `AUTO_INCREMENT` donde toque.
