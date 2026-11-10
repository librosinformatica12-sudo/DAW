[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio Servidor 01 — Clínica / Citas

**Enunciado general:** montar un portal con dos tablas (`doctores` y `citas`), dar de alta
doctores y citas, listar las citas con el nombre del doctor, modificar y borrar.

Es el mismo patrón que `portal_citasV2` y que `conciertos`.

Solución completa en `solucion/`. Para probar: importa `generador.sql`, ajusta `AccesoDatos.php`
y abre `portal_citas.html` desde `http://localhost/...`.

---

## Hueco 1 — Formulario HTML

Completa para que el PHP reciba `paciente`, `especialidad`, `fecha_hora` y `doctor`:

```html
<form action="____" method="____">
    <input type="hidden" name="accion" value="alta">

    <input type="text" name="____" required>
    <input type="datetime-local" name="____" required>
    <input type="number" name="____" required>

    <button type="submit">Guardar</button>
</form>
```

<details>
<summary>Ver solución</summary>

```html
<form action="gestionar_citas.php" method="post">
    <input type="hidden" name="accion" value="alta">

    <input type="text" name="paciente" required>
    <input type="datetime-local" name="fecha_hora" required>
    <input type="number" name="doctor" required>

    <button type="submit">Guardar</button>
</form>
```
</details>

---

## Hueco 2 — Arranque del PHP

```php
require_once __DIR__ . '/____';
session_start();

$accion = $_POST['accion'] ?? '';
```

<details>
<summary>Ver solución</summary>

```php
require_once __DIR__ . '/AccesoDatos.php';
```
</details>

---

## Hueco 3 — Recoger y validar

```php
$paciente     = trim($_POST['paciente'] ?? '');
$especialidad = trim($_POST['especialidad'] ?? '');
$fechaEntrada = trim($_POST['fecha_hora'] ?? '');
$doctor       = (int)($_POST['doctor'] ?? 0);

$fecha = date('Y-m-d H:i:s', strtotime($fechaEntrada));

if (/* TODO: paciente vacío */) {
    $mensaje = 'El nombre no puede estar vacío.';
} elseif (/* TODO: especialidad no permitida */) {
    $mensaje = 'Especialidad no válida.';
} elseif (/* TODO: fecha vacía */) {
    $mensaje = 'La fecha no puede estar vacía.';
} else {
    $res = PA_AltaCita($paciente, $especialidad, $fecha, $doctor);
}
```

<details>
<summary>Ver solución</summary>

```php
if ($paciente === '') {
    ...
} elseif (!in_array($especialidad, $especialidades, true)) {
    ...
} elseif ($fechaEntrada === '') {
```
</details>

---

## Hueco 4 — `bind_param`

```php
$stmt = $connection->prepare('CALL AltaCita(?, ?, ?, ?, @resultado)');
$stmt->bind_param('____', $paciente, $especialidad, $fecha, $doctor);
```

<details>
<summary>Ver solución</summary>

```php
$stmt->bind_param('sssi', $paciente, $especialidad, $fecha, $doctor);
```

`sssi` = string, string, string, integer (en el mismo orden que los `?`).
</details>

---

## Hueco 5 — Recoger el OUT

```php
$stmt->execute();
$stmt->close();

$resultado = $connection->query('SELECT @resultado AS resultado');
$fila = $resultado->fetch_assoc();

return (int)$fila['____'];
```

<details>
<summary>Ver solución</summary>

```php
return (int)$fila['resultado'];
```
</details>

---

## Hueco 6 — Procedimiento `AltaCita`

```sql
CREATE PROCEDURE AltaCita(
    IN _paciente VARCHAR(50),
    IN _especialidad VARCHAR(30),
    IN _fecha DATETIME,
    IN _doctor INT,
    OUT _res INT
)
BEGIN
    IF (/* nombre vacío */) THEN
        SET _res = -1;
    ELSEIF (/* fecha anterior a hoy */) THEN
        SET _res = -4;
    ELSEIF (/* el doctor no existe */) THEN
        SET _res = -5;
    ELSE
        INSERT INTO citas(paciente, especialidad, fecha, fk_doctor)
        VALUES(_paciente, _especialidad, _fecha, _doctor);
        SET _res = 0;
    END IF;
END
```

<details>
<summary>Ver solución</summary>

```sql
IF (_paciente = '' OR _paciente IS NULL) THEN
    SET _res = -1;
ELSEIF (_fecha <= NOW()) THEN
    SET _res = -4;
ELSEIF NOT EXISTS (SELECT 1 FROM doctores WHERE n_licencia = _doctor) THEN
    SET _res = -5;
ELSE
    INSERT INTO citas(paciente, especialidad, fecha, fk_doctor)
    VALUES(_paciente, _especialidad, _fecha, _doctor);
    SET _res = 0;
END IF;
```
</details>

---

## Hueco 7 — Pintar la tabla

```php
<?php foreach ($citas as $cita): ?>
    <tr>
        <td><?= htmlspecialchars($cita['____']) ?></td>
        <td><?= htmlspecialchars($cita['____']) ?></td>
        <td><?= htmlspecialchars($cita['____']) ?></td>
    </tr>
<?php endforeach; ?>
```

<details>
<summary>Ver solución</summary>

```php
<td><?= htmlspecialchars($cita['id_cita']) ?></td>
<td><?= htmlspecialchars($cita['paciente']) ?></td>
<td><?= htmlspecialchars($cita['especialidad']) ?></td>
<td><?= htmlspecialchars($cita['fecha']) ?></td>
```
</details>

---

## Hueco 8 — Lista con JOIN

```sql
CREATE PROCEDURE ListaCitas()
BEGIN
    SELECT c.*, d.nombre AS doctor_nombre
    FROM citas c
    /* TODO: unir con doctores por la licencia */
    ORDER BY c.fecha;
END
```

<details>
<summary>Ver solución</summary>

```sql
JOIN doctores d ON c.fk_doctor = d.n_licencia
```
</details>

---

## Hueco 9 (reto) — Borrar

Completa `BorrarCita` y su función `PA_BorrarCita`. Pistas:

```php
$stmt = $connection->prepare('CALL BorrarCita(?, @resultado)');
$stmt->bind_param('i', $id);
```

```sql
IF NOT EXISTS (SELECT 1 FROM citas WHERE id_cita = _id) THEN
    SET _res = -1;
ELSE
    DELETE FROM citas WHERE id_cita = _id;
    SET _res = 0;
END IF;
```

---

## Posibles preguntas del profe

1. **¿Por qué `session_start()` va antes del HTML?**
   Porque las cabeceras deben enviarse antes de cualquier salida.
2. **¿Qué hace `htmlspecialchars`?**
   Convierte `<`, `>`, `&`, comillas... en entidades y evita XSS.
3. **¿Qué letra de `bind_param` usarías para el precio?**
   `d` (double/decimal).
4. **¿Qué pasa si el `OUT` devuelve `null`?**
   `(int)null` es `0`, por eso conviene comprobar que el procedure asigna siempre `_res`.
5. **¿Por qué no se puede pasar `''` a un `DATETIME`?**
   Porque MySQL lo rechaza en modo estricto. Se valida antes en PHP.
6. **¿Diferencia entre `require` y `require_once`?**
   `require_once` no vuelve a cargar el archivo si ya se cargó; evita errores de funciones duplicadas.
