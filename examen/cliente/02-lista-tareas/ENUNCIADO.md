[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 02 — Lista de tareas (cliente)

**Enunciado general:** crea una lista de tareas que permita:

- Añadir una tarea escribiendo en un input.
- Marcarla como hecha / deshacer.
- Borrarla.
- Borrar todas las completadas.
- Ver cuántas hay y cuántas quedan pendientes.

Todo sin recargar la página. La solución está en `solucion/`.

---

## Hueco 1 — Evitar que el formulario recargue

```js
function agregarTarea(evento) {
    /* TODO: evitar el comportamiento por defecto del formulario */
    ...
}
```

<details>
<summary>Ver solución</summary>

```js
evento.preventDefault();
```
</details>

---

## Hueco 2 — Validar que no esté vacío

```js
const texto = input.value.trim();

if (/* TODO: el texto está vacío */) {
    mensaje.textContent = "Escribe algo antes de añadir.";
    return;
}
```

<details>
<summary>Ver solución</summary>

```js
if (texto === "") {
```
</details>

---

## Hueco 3 — Guardar la tarea en el array

Cada tarea debe ser un objeto con `id`, `texto` y `hecha`:

```js
tareas.push({
    id: /* TODO: usa siguienteId */,
    texto: /* TODO: el texto escrito */,
    hecha: /* TODO: false */
});
```

<details>
<summary>Ver solución</summary>

```js
tareas.push({
    id: siguienteId,
    texto: texto,
    hecha: false
});

siguienteId++;
```
</details>

---

## Hueco 4 — Crear el `<li>` y sus hijos

Dentro de `render()`:

```js
tareas.forEach(function (tarea) {
    const li = /* TODO: crear un li */;
    if (tarea.hecha) li.classList.add("hecha");

    const span = document.createElement("span");
    span./* TODO: poner el texto de la tarea */;

    const btnBorrar = document.createElement("button");
    btnBorrar.textContent = "Borrar";
    btnBorrar.dataset.id = tarea.id;
    btnBorrar.dataset.accion = "borrar";

    li./* TODO: añadir span y btnBorrar al li */;
    /* TODO: añadir el li a la lista */
});
```

<details>
<summary>Ver solución</summary>

```js
const li = document.createElement("li");

const span = document.createElement("span");
span.textContent = tarea.texto;

li.append(span, btnBorrar);
lista.appendChild(li);
```
</details>

---

## Hueco 5 — Marcar como hecha

```js
if (accion === "alternar") {
    const tarea = tareas.find(function (t) {
        return /* TODO: el id coincide */;
    });
    if (tarea) /* TODO: cambiar hecha a su contrario */;
}
```

<details>
<summary>Ver solución</summary>

```js
const tarea = tareas.find(function (t) {
    return t.id === id;
});
if (tarea) tarea.hecha = !tarea.hecha;
```
</details>

---

## Hueco 6 — Borrar una tarea

```js
if (accion === "borrar") {
    tareas = tareas./* TODO: quedarse con las que NO tienen ese id */;
}
```

<details>
<summary>Ver solución</summary>

```js
tareas = tareas.filter(function (t) {
    return t.id !== id;
});
```
</details>

---

## Hueco 7 — Contador

```js
const pendientes = tareas./* TODO: filtrar las no hechas */;
contador.textContent = tareas.length + " tareas (" + pendientes + " pendientes)";
```

<details>
<summary>Ver solución</summary>

```js
const pendientes = tareas.filter(function (t) {
    return !t.hecha;
}).length;
```
</details>

---

## Hueco 8 (extra) — Guardar en `localStorage`

Para que las tareas sobrevivan al recargar:

```js
// Al final de render() o tras cada cambio:
localStorage.setItem("tareas", JSON.stringify(tareas));

// Al arrancar:
const guardadas = localStorage.getItem("tareas");
if (guardadas) {
    tareas = JSON.parse(guardadas);
    siguienteId = tareas.length ? Math.max(...tareas.map(t => t.id)) + 1 : 1;
    render();
}
```

---

## Posibles preguntas del profe

1. **¿Por qué usas `evento.preventDefault()`?**
   Para que el `submit` no recargue la página y podamos gestionarlo con JS.
2. **¿Qué es `dataset`?**
   La forma de leer/escribir atributos `data-*`. `boton.dataset.id` lee `data-id`.
3. **¿Por qué un solo listener en la lista?**
   Es delegación de eventos: los botones se crean después, así que escuchamos en el padre.
4. **¿Qué hace `closest("button")`?**
   Busca el botón más cercano subiendo por el DOM desde el elemento clicado.
5. **¿Por qué vacías `lista.innerHTML = ""` antes de pintar?**
   Para no duplicar los elementos en cada render.
6. **¿Diferencia entre `append` y `appendChild`?**
   `append` acepta varios nodos y texto; `appendChild` solo un nodo.
