[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 04 — Carrito de compra (cliente)

**Enunciado general:** muestra un catálogo de productos. Al pulsar "Añadir", el producto
entra en el carrito. Si ya estaba, aumenta la cantidad. Se muestra el subtotal de cada línea
y el total general. Se puede quitar una línea o vaciar todo.

Solución en `solucion/`.

---

## Hueco 1 — Modelo del carrito

Completa el array de productos y define el carrito vacío:

```js
const PRODUCTOS = [
    { id: 1, nombre: "Pan",   precio: 1.20 },
    /* TODO: añade 3 productos más */
];

// Cada línea: { id, nombre, precio, cantidad }
let carrito = /* TODO: array vacío */;
```

<details>
<summary>Ver solución</summary>

```js
const PRODUCTOS = [
    { id: 1, nombre: "Pan",   precio: 1.20 },
    { id: 2, nombre: "Leche", precio: 0.95 },
    { id: 3, nombre: "Huevos", precio: 2.50 },
    { id: 4, nombre: "Café",  precio: 4.75 }
];

let carrito = [];
```
</details>

---

## Hueco 2 — Pintar el catálogo

```js
function renderProductos() {
    contenedorProductos.innerHTML = "";

    PRODUCTOS.forEach(function (producto) {
        const articulo = /* TODO: crear article */;
        articulo.className = "producto";

        const titulo = document.createElement("h3");
        titulo.textContent = /* TODO: nombre */;

        const boton = document.createElement("button");
        boton.textContent = "Añadir";
        boton./* TODO: guardar el id en data-id */;

        articulo.append(titulo, boton);
        /* TODO: añadir al contenedor */
    });
}
```

<details>
<summary>Ver solución</summary>

```js
const articulo = document.createElement("article");
titulo.textContent = producto.nombre;
boton.dataset.id = producto.id;
contenedorProductos.appendChild(articulo);
```
</details>

---

## Hueco 3 — Añadir al carrito (o sumar cantidad)

```js
function agregarAlCarrito(id) {
    const producto = PRODUCTOS.find(function (p) {
        return /* TODO: id coincide */;
    });

    const linea = carrito.find(function (l) {
        return l.id === id;
    });

    if (linea) {
        linea./* TODO: cantidad + 1 */;
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            precio: producto.precio,
            cantidad: /* TODO: 1 */
        });
    }

    renderCarrito();
}
```

<details>
<summary>Ver solución</summary>

```js
const producto = PRODUCTOS.find(function (p) {
    return p.id === id;
});

linea.cantidad++;
cantidad: 1
```
</details>

---

## Hueco 4 — Quitar una línea

```js
function quitarDelCarrito(id) {
    carrito = carrito./* TODO: filtrar las líneas cuyo id NO sea este */;
    renderCarrito();
}
```

<details>
<summary>Ver solución</summary>

```js
carrito = carrito.filter(function (linea) {
    return linea.id !== id;
});
```
</details>

---

## Hueco 5 — Subtotal de una línea

Dentro de `renderCarrito()`:

```js
const tdSubtotal = document.createElement("td");
tdSubtotal.textContent = /* TODO: precio * cantidad, con 2 decimales */;
```

<details>
<summary>Ver solución</summary>

```js
tdSubtotal.textContent = (linea.precio * linea.cantidad).toFixed(2) + " €";
```
</details>

---

## Hueco 6 — Total general

```js
const total = carrito./* TODO: recorrer y acumular precio * cantidad */;
spanTotal.textContent = total.toFixed(2);
```

<details>
<summary>Ver solución</summary>

```js
const total = carrito.reduce(function (acumulado, linea) {
    return acumulado + linea.precio * linea.cantidad;
}, 0);
```

Versión con `forEach` (si no quieres usar `reduce`):

```js
let total = 0;
carrito.forEach(function (linea) {
    total += linea.precio * linea.cantidad;
});
```
</details>

---

## Hueco 7 — Mostrar/ocultar "carrito vacío"

```js
pVacio.style.display = /* TODO: "block" si está vacío, "none" si no */;
```

<details>
<summary>Ver solución</summary>

```js
pVacio.style.display = carrito.length === 0 ? "block" : "none";
```
</details>

---

## Hueco 8 (extra) — Botón "+" y "−" de cantidad

Añade dos botones por línea y en `manejarClic`:

```js
if (accion === "sumar") linea.cantidad++;
if (accion === "restar") {
    linea.cantidad--;
    if (linea.cantidad <= 0) quitarDelCarrito(id);
}
```

---

## Posibles preguntas del profe

1. **¿Por qué separas `PRODUCTOS` y `carrito`?**
   `PRODUCTOS` es el catálogo fijo; `carrito` son las líneas que elige el usuario.
2. **¿Qué hace `find`?**
   Devuelve el primer elemento que cumple la condición, o `undefined`.
3. **¿Qué hace `filter`?**
   Devuelve un array nuevo con los elementos que cumplen la condición.
4. **¿Qué hace `reduce`?**
   Recorre el array acumulando un valor; aquí, el total.
5. **¿Por qué `toFixed(2)`?**
   Para mostrar siempre dos decimales (los precios).
6. **¿Cómo evitarías que el total tenga errores de decimales?**
   Trabajando en céntimos (enteros) y dividiendo entre 100 al final.
