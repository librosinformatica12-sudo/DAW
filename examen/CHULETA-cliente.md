[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# CHULETA — CLIENTE (HTML + CSS + JS)

## 1. Esqueleto HTML mínimo

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ejercicio</title>
    <link rel="stylesheet" href="style.css">
    <script src="script.js" defer></script>
</head>
<body>
    <h1>Mi ejercicio</h1>
    <button id="btn">Pulsar</button>
    <p id="mensaje"></p>
</body>
</html>
```

`defer` = el JS se ejecuta cuando el HTML ya está cargado. Si no lo pones, usa `DOMContentLoaded`.

## 2. Seleccionar elementos

```js
const btn = document.getElementById("btn");
const mensaje = document.getElementById("mensaje");
const cajas = document.querySelectorAll(".caja");   // todos
const primera = document.querySelector(".caja");    // el primero
```

| Quiero... | Código |
|---|---|
| Por id | `document.getElementById("id")` |
| Por clase | `document.querySelector(".clase")` |
| Todos por clase | `document.querySelectorAll(".clase")` |
| Dentro de otro | `padre.querySelector(".hijo")` |

## 3. Eventos

```js
btn.addEventListener("click", function () {
    // acción
});

btn.addEventListener("click", miFuncion);   // sin paréntesis

function miFuncion() {
    // acción
}
```

Otros eventos: `input`, `change`, `submit`, `keydown`, `mouseover`.

Formulario (evitar recarga):

```js
form.addEventListener("submit", function (e) {
    e.preventDefault();
    // validar y actuar
});
```

## 4. Cambiar el DOM

```js
mensaje.innerText = "Hola";              // texto plano
mensaje.textContent = "Hola";            // igual, recomendado
mensaje.innerHTML = "<b>Hola</b>";       // interpreta HTML (cuidado)
imagen.setAttribute("src", "foto.png");
imagen.src = "foto.png";                 // lo mismo
caja.classList.add("activa");
caja.classList.remove("activa");
caja.classList.toggle("activa");
caja.style.display = "none";             // ocultar
caja.style.color = "red";
input.value = "";                        // vaciar input
```

## 5. Crear elementos (listas, tablas...)

```js
const li = document.createElement("li");
li.textContent = "Nueva tarea";
li.dataset.id = 3;                       // guarda un dato: data-id="3"
lista.appendChild(li);

// Recorrer una lista de elementos
document.querySelectorAll(".tarea").forEach(function (el) {
    el.classList.add("vista");
});
```

## 6. Arrays y objetos

```js
const tareas = [];                       // array
tareas.push({ id: 1, texto: "Estudiar", hecha: false });
tareas.length;                           // cuántos
tareas.forEach(function (t) { console.log(t.texto); });
tareas.filter(function (t) { return !t.hecha; });
tareas.find(function (t) { return t.id === 1; });
tareas.splice(indice, 1);                // borrar 1 elemento

// Array de objetos para precios
const carrito = [{ nombre: "Pan", precio: 1.5, cantidad: 2 }];
let total = 0;
carrito.forEach(function (p) { total += p.precio * p.cantidad; });
```

## 7. Random

```js
const n = Math.floor(Math.random() * 5) + 1;   // 1..5
const n2 = Math.floor(Math.random() * 100) + 1; // 1..100
```

## 8. Validar antes de actuar

```js
if (texto.trim() === "") {
    mensaje.innerText = "No puede estar vacío";
    return;                              // corta la función
}
```

## 9. CSS que más cae

```css
* { box-sizing: border-box; }

body {
    font-family: Arial, sans-serif;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    background: #222;
    color: white;
}

.contenedor {
    display: flex;
    gap: 20px;
    padding: 20px;
    border-radius: 10px;
    background: #333;
}

button {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    background: gold;
}

button:hover { transform: scale(1.05); }
button:active { transform: scale(0.95); }

/* Rejilla responsive */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

@media (max-width: 600px) {
    .contenedor { flex-direction: column; }
}
```

## 10. Los 12 huecos típicos (con respuesta)

1. **Seleccionar el botón** → `document.getElementById("btn")`
2. **Escuchar el clic** → `btn.addEventListener("click", funcion)`
3. **Número aleatorio 1-5** → `Math.floor(Math.random() * 5) + 1`
4. **Cambiar una imagen** → `img.setAttribute("src", "foto.png")`
5. **Escribir un texto en pantalla** → `span.innerText = "..."` o `textContent`
6. **Ocultar un elemento** → `el.style.display = "none"` o `classList.add("oculto")`
7. **Crear un `<li>`** → `document.createElement("li")`
8. **Añadirlo a la lista** → `ul.appendChild(li)`
9. **Recorrer todos los elementos** → `document.querySelectorAll(".x").forEach(...)`
10. **Leer lo que escribe el usuario** → `input.value`
11. **Evitar que el formulario recargue** → `e.preventDefault()`
12. **Comprobar si está vacío** → `if (texto.trim() === "")`

## 11. Checklist antes de entregar

- [ ] Los `id` son únicos (no repetir `id="panel"` dos veces).
- [ ] El `<script>` lleva `defer` o el código va en `DOMContentLoaded`.
- [ ] Los nombres de archivos coinciden: `style.css`, `script.js`.
- [ ] La función está definida antes de usarla o es una `function` (se elevan).
- [ ] Valido entradas vacías.
- [ ] Uso `textContent`/`innerText` para texto del usuario (evita inyección HTML).
- [ ] Pruebo a pulsar varias veces seguidas.
