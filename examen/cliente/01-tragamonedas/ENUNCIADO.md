[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 01 — Tragamonedas (cliente)

**Enunciado general:** crea una tragaperras con 3 rodillos. Cada tirada cuesta 10 créditos.
Si los tres rodillos coinciden, se gana según la tabla:

| Símbolo | Premio |
|---|---|
| 🍊 naranja | +5 |
| 🍋 limón | +7 |
| 🍒 cereza | +10 |
| 🔔 campana | +20 |
| 7 siete | +50 |

Empiezas con 100 créditos. Si no llegas a 10, no puedes tirar.

La solución está en `solucion/`. **No la abras hasta intentarlo.**

---

## Hueco 1 — Seleccionar los elementos

En `script.js`, completa las referencias al HTML:

```js
const btnTirar     = /* TODO: botón con id btn_tirar */;
const spanCreditos = /* TODO: span con id creditos */;
const pMensaje     = /* TODO: párrafo con id mensaje */;
```

<details>
<summary>Ver solución</summary>

```js
const btnTirar     = document.getElementById("btn_tirar");
const spanCreditos = document.getElementById("creditos");
const pMensaje     = document.getElementById("mensaje");
```
</details>

---

## Hueco 2 — Escuchar el clic

```js
/* TODO: cuando se pulse btnTirar, ejecutar la función tirada */
```

<details>
<summary>Ver solución</summary>

```js
btnTirar.addEventListener("click", tirada);
```
</details>

---

## Hueco 3 — Validar créditos

Dentro de `tirada()`, antes de cobrar:

```js
if (/* TODO: no hay créditos suficientes */) {
    pMensaje.textContent = "Te has quedado sin créditos. Pulsa Reiniciar.";
    return;
}
```

<details>
<summary>Ver solución</summary>

```js
if (creditos < 10) {
    pMensaje.textContent = "Te has quedado sin créditos. Pulsa Reiniciar.";
    return;
}
```
</details>

---

## Hueco 4 — Número aleatorio del 1 al 5

```js
for (let i = 0; i < 3; i++) {
    slots[i] = /* TODO: aleatorio entero entre 1 y 5 */;
}
```

<details>
<summary>Ver solución</summary>

```js
slots[i] = Math.floor(Math.random() * 5) + 1;
```

`Math.random()` da un decimal entre 0 y casi 1. Multiplicado por 5 da 0..4.99.
`Math.floor` lo baja a 0..4. Sumando 1 queda 1..5.
</details>

---

## Hueco 5 — Comprobar el premio

```js
if (/* TODO: los tres rodillos son iguales */) {
    const premio = PREMIOS[slots[0]];
    creditos += premio;
    pMensaje.textContent = "¡TRIPLE! Has ganado " + premio + " créditos.";
} else {
    pMensaje.textContent = "No ha habido suerte. Prueba otra vez.";
}
```

<details>
<summary>Ver solución</summary>

```js
if (slots[0] === slots[1] && slots[1] === slots[2]) {
```
</details>

---

## Hueco 6 — Pintar el estado

Completa `pintarSlots()`:

```js
function pintarSlots() {
    /* TODO: mostrar los créditos en spanCreditos */

    for (let i = 0; i < 3; i++) {
        /* TODO: poner en slotsVista[i] el símbolo que toca */
        /* TODO: añadir la clase "ganador" si los tres son iguales */
    }
}
```

<details>
<summary>Ver solución</summary>

```js
function pintarSlots() {
    spanCreditos.textContent = creditos;

    for (let i = 0; i < 3; i++) {
        slotsVista[i].textContent = SIMBOLOS[slots[i]];

        if (slots[0] === slots[1] && slots[1] === slots[2]) {
            slotsVista[i].classList.add("ganador");
        } else {
            slotsVista[i].classList.remove("ganador");
        }
    }
}
```
</details>

---

## Hueco 7 (extra) — Reiniciar

```js
function reiniciar() {
    /* TODO: créditos a 100, slots a [5,5,5], mensaje y repintar */
}
```

<details>
<summary>Ver solución</summary>

```js
function reiniciar() {
    creditos = 100;
    slots = [5, 5, 5];
    pMensaje.textContent = "Partida reiniciada.";
    pintarSlots();
}
```
</details>

---

## Si el profe usa imágenes en vez de emojis

Cambia solo la línea del pintado:

```js
// En vez de:
slotsVista[i].textContent = SIMBOLOS[slots[i]];

// Usa:
slotsVista[i].setAttribute("src", IMAGENES[slots[i]]);
```

Y define:

```js
const IMAGENES = {
    1: "img/naranja.png",
    2: "img/limon.png",
    3: "img/cereza.png",
    4: "img/campana.png",
    5: "img/siete.png"
};
```

---

## Posibles preguntas del profe

1. **¿Por qué restas 10 antes de comprobar el premio?**
   Porque la tirada se paga siempre, ganes o no. El premio se suma después.
2. **¿Qué diferencia hay entre `innerText`, `textContent` e `innerHTML`?**
   `textContent`/`innerText` escriben texto; `innerHTML` interpreta etiquetas HTML.
3. **¿Qué hace `return` dentro de `tirada()`?**
   Sale de la función inmediatamente; no sigue cobrando ni generando números.
4. **¿Por qué `defer` en el `<script>`?**
   Para que el JS se ejecute cuando el HTML ya existe y `getElementById` no devuelva `null`.
5. **¿Cómo cambiarías el precio de la tirada a 20?**
   Cambiando el `10` de la resta y el `10` de la condición.
