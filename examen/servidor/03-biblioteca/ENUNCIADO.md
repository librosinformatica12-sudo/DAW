[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio Servidor 03 — Biblioteca

**Enunciado general:** gestionar libros, socios y préstamos.

- Alta de libros y socios.
- Listar libros mostrando cuántos ejemplares quedan disponibles.
- Prestar un libro si hay existencias.
- Devolver un préstamo.

Este ejemplo añade algo que no tienen los otros: **JOIN, subconsultas y UPDATE de estado**.

Solución en `solucion/`.

---

## Hueco 1 — Tabla de préstamos

```sql
CREATE TABLE prestamos (
    id_prestamo      INT AUTO_INCREMENT PRIMARY KEY,
    fecha_prestamo   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion DATETIME NULL,
    devuelto         TINYINT(1) NOT NULL DEFAULT ____,
    fk_libro         INT NOT NULL,
    fk_socio         INT NOT NULL,
    FOREIGN KEY (fk_libro) REFERENCES ____(id_libro),
    FOREIGN KEY (fk_socio) REFERENCES ____(id_socio)
);
```

<details>
<summary>Ver solución</summary>

```sql
devuelto TINYINT(1) NOT NULL DEFAULT 0,
FOREIGN KEY (fk_libro) REFERENCES libros(id_libro),
FOREIGN KEY (fk_socio) REFERENCES socios(id_socio)
```
</details>

---

## Hueco 2 — Disponibles por libro

```sql
SELECT l.*,
       (l.ejemplares - (/* TODO: contar préstamos NO devueltos de este libro */))
           AS disponibles
FROM libros l;
```

<details>
<summary>Ver solución</summary>

```sql
SELECT l.*,
       (l.ejemplares - (SELECT COUNT(*)
                          FROM prestamos p
                         WHERE p.fk_libro = l.id_libro
                           AND p.devuelto = 0)) AS disponibles
FROM libros l;
```
</details>

---

## Hueco 3 — Validaciones de `AltaLibro`

```sql
IF (/* título vacío */) THEN
    SET _res = -1;
ELSEIF (/* autor vacío */) THEN
    SET _res = -2;
ELSEIF (/* ejemplares <= 0 */) THEN
    SET _res = -3;
ELSE
    INSERT INTO libros(titulo, autor, ejemplares)
    VALUES(_titulo, _autor, _ejemplares);
    SET _res = 0;
END IF;
```

<details>
<summary>Ver solución</summary>

```sql
IF (_titulo = '' OR _titulo IS NULL) THEN
    SET _res = -1;
ELSEIF (_autor = '' OR _autor IS NULL) THEN
    SET _res = -2;
ELSEIF (_ejemplares <= 0) THEN
    SET _res = -3;
```
</details>

---

## Hueco 4 — `bind_param` de un alta

```php
$stmt = $connection->prepare('CALL AltaLibro(?, ?, ?, @resultado)');
$stmt->bind_param('____', $titulo, $autor, $ejemplares);
```

<details>
<summary>Ver solución</summary>

```php
$stmt->bind_param('ssi', $titulo, $autor, $ejemplares);
```
</details>

---

## Hueco 5 — Comprobar existencias al prestar

```sql
SELECT (l.ejemplares - (SELECT COUNT(*)
                          FROM prestamos p
                         WHERE p.fk_libro = l.id_libro
                           AND p.devuelto = 0))
  INTO _disponibles
  FROM libros l
 WHERE l.id_libro = _libro;

IF (/* no hay disponibles */) THEN
    SET _res = -3;
ELSE
    INSERT INTO prestamos(fk_libro, fk_socio) VALUES(_libro, _socio);
    SET _res = 0;
END IF;
```

<details>
<summary>Ver solución</summary>

```sql
IF (_disponibles <= 0) THEN
```
</details>

---

## Hueco 6 — Devolver un libro

```sql
IF NOT EXISTS (SELECT 1 FROM prestamos WHERE id_prestamo = _id_prestamo) THEN
    SET _res = -1;
ELSEIF (/* ya estaba devuelto */) THEN
    SET _res = -2;
ELSE
    UPDATE prestamos
    SET devuelto = ____,
        fecha_devolucion = ____
    WHERE id_prestamo = _id_prestamo;

    SET _res = 0;
END IF;
```

<details>
<summary>Ver solución</summary>

```sql
ELSEIF (SELECT devuelto FROM prestamos WHERE id_prestamo = _id_prestamo) = 1 THEN

SET devuelto = 1,
    fecha_devolucion = NOW()
```
</details>

---

## Hueco 7 — Lista de préstamos con JOIN

```sql
SELECT p.id_prestamo, p.fecha_prestamo, l.titulo, s.nombre AS socio
FROM prestamos p
/* TODO: unir con libros */
/* TODO: unir con socios */
WHERE p.devuelto = 0;
```

<details>
<summary>Ver solución</summary>

```sql
JOIN libros l ON p.fk_libro = l.id_libro
JOIN socios s ON p.fk_socio = s.id_socio
```
</details>

---

## Hueco 8 — Pintar la tabla de libros

```php
<?php foreach ($libros as $libro): ?>
    <tr>
        <td><?= htmlspecialchars($libro['____']) ?></td>
        <td><?= htmlspecialchars($libro['____']) ?></td>
        <td><?= htmlspecialchars($libro['____']) ?></td>
        <td><?= htmlspecialchars($libro['disponibles']) ?></td>
    </tr>
<?php endforeach; ?>
```

<details>
<summary>Ver solución</summary>

```php
$libro['id_libro']
$libro['titulo']
$libro['autor']
```
</details>

---

## Hueco 9 (reto) — Evitar préstamos duplicados

Impide que el mismo socio tenga dos veces el mismo libro sin devolver:

```sql
ELSEIF EXISTS (SELECT 1 FROM prestamos
                WHERE fk_libro = _libro
                  AND fk_socio = _socio
                  AND devuelto = 0) THEN
    SET _res = -4;
```

---

## Posibles preguntas del profe

1. **¿Qué diferencia hay entre `INNER JOIN` y `LEFT JOIN`?**
   `INNER JOIN` solo devuelve filas con coincidencia; `LEFT JOIN` devuelve también las de la izquierda sin coincidencia.
2. **¿Por qué `devuelto` es `TINYINT(1)`?**
   Porque MySQL no tiene booleano puro; `TINYINT(1)` se usa como 0/1.
3. **¿Por qué calcular `disponibles` en SQL y no en PHP?**
   Porque la base de datos ya tiene los datos y evita traer todos los préstamos.
4. **¿Qué pasa si se presta el último ejemplar dos veces a la vez?**
   Podría haber condición de carrera; se resolvería con transacción y bloqueo.
5. **¿Qué hace `NOW()`?**
   Devuelve la fecha y hora actual del servidor MySQL.
6. **¿Cómo comprobarías que un socio existe antes de prestar?**
   Con `EXISTS (SELECT 1 FROM socios WHERE id_socio = _socio)`, como hace `PrestarLibro`.
