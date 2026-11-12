[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 06 — Espaciado de letras (cliente)

**Enunciado general:** crea una página con un slider que controle el espacio entre las letras de un título. Añade botones para sumar, restar, reiniciar y lanzar una animación de latido. Usa variables y funciones.

**Pistas:** `input type="range"`, variables, funciones, botones y una animación CSS diferente a la del ejercicio 05.

La solución está en `solucion/`. **No la abras hasta intentarlo.**

---

## Qué hay que hacer

1. Poner un `input type="range"` con `min="0"`, `max="20"` y `step="1"`.
2. Mostrar el valor actual al lado del slider.
3. Cambiar el `letter-spacing` del título cuando se mueve el slider.
4. Añadir botones **-1**, **+1**, **Animar** y **Reiniciar**.
5. Usar variables para el valor, el mínimo, el máximo y el paso.
6. Usar funciones para pintar, cambiar, reiniciar y animar.
7. Añadir una animación CSS de latido al pulsar **Animar**.

---

## Hueco 1 — HTML del slider y los botones

```html
<label for="exp">
    Espacio entre letras:
    <span id="valor">2</span> px
</label>

<input type="range" id="exp" name="exp"
       min="0" max="20" step="1" value="2">

<button type="button" id="btn-menos">-1</button>
<button type="button" id="btn-mas">+1</button>
<button type="button" id="btn-animar">Animar</button>
<button type="button" id="btn-reiniciar">Reiniciar</button>
```

<details>
<summary>Ver explicación</summary>

El `label` usa `for="exp"` para quedar asociado al slider. El `span` con `id="valor"` muestra el número. Los botones llevan `type="button"` para que no envíen ningún formulario.
</details>

---

## Hueco 2 — Referencias del DOM

```js
const slider = /* TODO: input con id exp */;
const valor = /* TODO: span con id valor */;
const titulo = /* TODO: h1 con id titulo */;
```

<details>
<summary>Ver solución</summary>

```js
const slider = document.getElementById("exp");
const valor = document.getElementById("valor");
const titulo = document.getElementById("titulo");
```
</details>

---

## Hueco 3 — Variables

```js
let espacio = /* TODO: valor inicial */;
const MIN = /* TODO: mínimo */;
const MAX = /* TODO: máximo */;
const PASO = /* TODO: cuánto suma cada botón */;
```

<details>
<summary>Ver solución</summary>

```js
let espacio = 2;
const MIN = 0;
const MAX = 20;
const PASO = 1;
```
</details>

---

## Hueco 4 — Función pintar

```js
function pintar() {
    /* TODO: sincronizar el slider con la variable espacio */
    /* TODO: mostrar el número en el span */
    /* TODO: aplicar el espacio entre letras al título */
}
```

<details>
<summary>Ver solución</summary>

```js
function pintar() {
    slider.value = espacio;
    valor.textContent = espacio;
    titulo.style.letterSpacing = espacio + "px";
}
```

`textContent` escribe texto sin interpretar HTML. `style.letterSpacing` espera un valor con unidad, por eso añadimos `"px"`.
</details>

---

## Hueco 5 — Cambiar y reiniciar

```js
function cambiar(cantidad) {
    /* TODO: sumar la cantidad y limitar entre MIN y MAX */
    /* TODO: llamar a pintar */
}

function reiniciar() {
    /* TODO: volver al valor inicial */
    /* TODO: llamar a pintar */
}
```

<details>
<summary>Ver solución</summary>

```js
function cambiar(cantidad) {
    espacio = Math.min(MAX, Math.max(MIN, espacio + cantidad));
    pintar();
}

function reiniciar() {
    espacio = 2;
    pintar();
}
```

`Math.max(MIN, ...)` impide bajar del mínimo. `Math.min(MAX, ...)` impide pasar del máximo.
</details>

---

## Hueco 6 — Animación y eventos

```js
function animar() {
    /* TODO: añadir la clase latido */
    /* TODO: quitarla cuando termine la animación */
}

slider.addEventListener(/* TODO: evento */, /* TODO: función */);
btnMas.addEventListener("click", /* TODO */);
btnMenos.addEventListener("click", /* TODO */);
btnAnimar.addEventListener("click", /* TODO */);
btnReiniciar.addEventListener("click", /* TODO */);
```

<details>
<summary>Ver solución</summary>

```js
function animar() {
    titulo.classList.add("latido");

    setTimeout(function () {
        titulo.classList.remove("latido");
    }, 900);
}

slider.addEventListener("input", function () {
    espacio = Number(slider.value);
    pintar();
});

btnMas.addEventListener("click", function () {
    cambiar(PASO);
});

btnMenos.addEventListener("click", function () {
    cambiar(-PASO);
});

btnAnimar.addEventListener("click", animar);
btnReiniciar.addEventListener("click", reiniciar);
```

El evento `input` se dispara mientras arrastras el slider. `Number(slider.value)` convierte el texto del slider en número.
</details>

---

## Hueco 7 — CSS de la animación

```css
.titulo {
    /* TODO: transición del espaciado */
}

.titulo.latido {
    /* TODO: aplicar la animación */
}

@keyframes latido {
    /* TODO: escala 1 → 1.12 → 0.96 → 1 */
}
```

<details>
<summary>Ver solución</summary>

```css
.titulo {
    letter-spacing: 2px;
    transition: letter-spacing 0.2s ease;
}

.titulo.latido {
    animation: latido 0.9s ease-in-out;
}

@keyframes latido {
    0%, 100% {
        transform: scale(1);
    }

    30% {
        transform: scale(1.12);
    }

    60% {
        transform: scale(0.96);
    }

    80% {
        transform: scale(1.04);
    }
}
```

`transition` suaviza el cambio de espaciado. `@keyframes` define los fotogramas y `animation` la aplica.
</details>

---

## Si el profe pide otra cosa

| Lo que pide | Qué cambiar |
|---|---|
| Espacio entre letras | `titulo.style.letterSpacing` |
| Espacio entre palabras | `titulo.style.wordSpacing` |
| Espacio interior de una caja | `caja.style.padding` |
| Animación más lenta | `animation-duration` |
| Que se vea el valor | `valor.textContent = espacio` |

---

## Posibles preguntas del profe

1. **¿Qué diferencia hay entre `input` y `change`?** `input` salta mientras mueves el slider; `change` salta al soltarlo.
2. **¿Por qué usas `Number()`?** Porque `slider.value` devuelve un texto, no un número.
3. **¿Para qué sirven `Math.min` y `Math.max`?** Para limitar el valor y que no se salga de `MIN` y `MAX`.
4. **¿Qué hace `classList.add`?** Añade una clase al elemento; en este caso activa la animación.
5. **¿Por qué quitas la clase con `setTimeout`?** Para poder volver a lanzar la misma animación.
6. **¿Qué diferencia hay entre `textContent` e `innerHTML`?** `textContent` escribe texto; `innerHTML` interpreta etiquetas HTML.
