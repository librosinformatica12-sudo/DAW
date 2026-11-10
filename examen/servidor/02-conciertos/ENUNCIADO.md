[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio Servidor 02 — Conciertos

**Enunciado general:** terminar el gestor de conciertos:

- Tabla `concierto` y tabla `entradas`.
- Dar de alta, listar, modificar y borrar conciertos.
- Comprar una entrada comprobando que el concierto no ha pasado y que queda aforo.

Este ejemplo es tu proyecto de `conciertos`, pero completo.

> La base de datos se llama `gestor_conciertos_examen` para **no borrar** tu base real
> `gestor_conciertos`. Si quieres usar este código en tu proyecto, cambia el nombre en
> `generador.sql` y en `AccesoDatos.php`.

Solución en `solucion/`.

---

## Hueco 1 — Esquema SQL

```sql
CREATE TABLE concierto (
    id_concierto    INT ____ PRIMARY KEY,
    nombre_banda    VARCHAR(60) NOT NULL,
    fecha_concierto DATETIME NOT NULL,
    precio          DECIMAL(6,2) NOT NULL,
    aforo           INT NOT NULL DEFAULT 50
);

CREATE TABLE entradas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    fecha_adquisicion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fk_concierto      INT NOT NULL,
    FOREIGN KEY (fk_concierto) REFERENCES ____(id_concierto)
);
```

<details>
<summary>Ver solución</summary>

```sql
INT AUTO_INCREMENT PRIMARY KEY
...
REFERENCES concierto(id_concierto)
```
</details>

---

## Hueco 2 — Procedimiento `AltaConcierto`

Completa los casos:

```sql
IF (/* nombre vacío */) THEN
    SET _res = -1;
ELSEIF (/* fecha nula */) THEN
    SET _res = -2;
ELSEIF (/* fecha anterior a hoy */) THEN
    SET _res = -3;
ELSEIF (/* precio <= 0 */) THEN
    SET _res = -4;
ELSEIF (/* aforo <= 0 */) THEN
    SET _res = -5;
ELSE
    INSERT INTO concierto(nombre_banda, fecha_concierto, precio, aforo)
    VALUES(_nombre, _fecha, _precio, _aforo);
    SET _res = 0;
END IF;
```

<details>
<summary>Ver solución</summary>

```sql
IF (_nombre = '' OR _nombre IS NULL) THEN
    SET _res = -1;
ELSEIF (_fecha IS NULL) THEN
    SET _res = -2;
ELSEIF (_fecha <= NOW()) THEN
    SET _res = -3;
ELSEIF (_precio <= 0) THEN
    SET _res = -4;
ELSEIF (_aforo <= 0) THEN
    SET _res = -5;
```
</details>

---

## Hueco 3 — Llamada desde PHP

```php
$stmt = $connection->prepare('CALL AltaConcierto(?, ?, ?, ?, @resultado)');
$stmt->bind_param('____', $nombre, $fecha, $precio, $aforo);
$stmt->execute();
$stmt->close();

$resultado = $connection->query('SELECT @resultado AS resultado');
$fila = $resultado->fetch_assoc();

return (int)$fila['resultado'];
```

<details>
<summary>Ver solución</summary>

```php
$stmt->bind_param('ssdi', $nombre, $fecha, $precio, $aforo);
```

`ssdi` = string (nombre), string (fecha), double (precio), integer (aforo).
</details>

---

## Hueco 4 — Listar con entradas vendidas

Queremos que cada concierto muestre cuántas entradas se han vendido:

```sql
SELECT c.*,
       (/* TODO: contar entradas de este concierto */) AS entradas_vendidas
FROM concierto c
ORDER BY c.fecha_concierto;
```

<details>
<summary>Ver solución</summary>

```sql
SELECT c.*,
       (SELECT COUNT(*) FROM entradas e
         WHERE e.fk_concierto = c.id_concierto) AS entradas_vendidas
FROM concierto c
ORDER BY c.fecha_concierto;
```
</details>

---

## Hueco 5 — Comprar entrada

Completa `ComprarEntrada`:

```sql
DECLARE _fecha DATETIME;
DECLARE _aforo INT;
DECLARE _vendidas INT DEFAULT 0;

IF NOT EXISTS (SELECT 1 FROM concierto WHERE id_concierto = _id_concierto) THEN
    SET _res = -1;
ELSE
    SELECT fecha_concierto, aforo INTO _fecha, _aforo
      FROM concierto WHERE id_concierto = _id_concierto;

    SELECT COUNT(*) INTO _vendidas
      FROM entradas WHERE fk_concierto = _id_concierto;

    IF (/* concierto pasado */) THEN
        SET _res = -2;
    ELSEIF (/* aforo completo */) THEN
        SET _res = -3;
    ELSE
        INSERT INTO entradas(fk_concierto) VALUES(_id_concierto);
        SET _res = 0;
    END IF;
END IF;
```

<details>
<summary>Ver solución</summary>

```sql
IF (_fecha <= NOW()) THEN
    SET _res = -2;
ELSEIF (_vendidas >= _aforo) THEN
    SET _res = -3;
```
</details>

---

## Hueco 6 — Borrar con seguridad

No se debe borrar un concierto que ya tiene entradas:

```sql
IF NOT EXISTS (SELECT 1 FROM concierto WHERE id_concierto = _id) THEN
    SET _res = -1;
ELSEIF (/* existen entradas de ese concierto */) THEN
    SET _res = -2;
ELSE
    DELETE FROM concierto WHERE id_concierto = _id;
    SET _res = 0;
END IF;
```

<details>
<summary>Ver solución</summary>

```sql
ELSEIF EXISTS (SELECT 1 FROM entradas WHERE fk_concierto = _id) THEN
    SET _res = -2;
```
</details>

---

## Hueco 7 — Formulario

El formulario debe enviar estos nombres:

```html
<form action="gestionar_conciertos.php" method="post">
    <input type="hidden" name="accion" value="alta">

    <input type="text" name="____" required>
    <input type="datetime-local" name="____" required>
    <input type="number" name="____" step="0.01" required>
    <input type="number" name="____" required>
</form>
```

<details>
<summary>Ver solución</summary>

```html
<input type="text" name="nombre_banda" required>
<input type="datetime-local" name="fecha_concierto" required>
<input type="number" name="precio" step="0.01" required>
<input type="number" name="aforo" required>
```
</details>

---

## Hueco 8 — Mensajes de error

Relaciona cada código con su mensaje:

```php
if ($res === -1) {
    $mensaje = '____';
} elseif ($res === -3) {
    $mensaje = '____';
} elseif ($res === -4) {
    $mensaje = '____';
} elseif ($res === -5) {
    $mensaje = '____';
} else {
    $mensaje = 'Concierto guardado.';
}
```

<details>
<summary>Ver solución</summary>

```php
-1 → 'Nombre vacío.'
-3 → 'La fecha debe ser futura.'
-4 → 'El precio debe ser mayor que 0.'
-5 → 'El aforo debe ser mayor que 0.'
```
</details>

---

## Hueco 9 (reto) — Añadir `entradas` con precio

Añade a la tabla `entradas` una columna `precio_pagado DECIMAL(6,2)` y guárdala en
`ComprarEntrada` leyendo el precio del concierto. Pista:

```sql
INSERT INTO entradas(fk_concierto, precio_pagado)
SELECT id_concierto, precio FROM concierto WHERE id_concierto = _id_concierto;
```

---

## Posibles preguntas del profe

1. **¿Por qué `LEFT JOIN` o subconsulta en la lista?**
   Porque quieres mostrar conciertos aunque no tengan entradas.
2. **¿Qué diferencia hay entre `DELETE` y `ON DELETE CASCADE`?**
   `CASCADE` borra automáticamente las entradas hijas. Aquí lo bloqueamos a propósito.
3. **¿Por qué validar el aforo dentro del procedure?**
   Porque la lógica de negocio vive en la base de datos y es atómica.
4. **¿Qué pasa si dos usuarios compran a la vez?**
   Con el `COUNT` puede haber carrera; en producción se usaría transacción y bloqueo.
5. **¿Por qué `precio` usa `d` en `bind_param`?**
   Porque es un decimal; `d` lo trata como double.
6. **¿Qué código devuelve el procedure si todo va bien?**
   `0`.
