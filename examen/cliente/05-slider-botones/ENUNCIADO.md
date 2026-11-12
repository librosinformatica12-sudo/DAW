[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 05 — Slider, botones y animaciones (cliente)

**Enunciado general:** crea una página con un slider que controle el espacio interior de una caja. Debes usar variables, funciones, botones y una animación sencilla. Todo con HTML, CSS y JavaScript.

## Notas que me pasaron

```text
control espacio usando el slider para usar un input
<input type="range" name="exp" min="1" max="100" step="5">
por ejemplo
nos va a pedir usar control espacio
que toquemos funciones
frontend
css simple
usando botones
crear variables y usarlas en funciones
saber hacer botones bonitos
y animaciones sencillas
¿eso en cliente no? en server?
```

**Respuesta rápida:** esto es **cliente**. Se hace con HTML, CSS y JavaScript; no hace falta PHP ni base de datos.

He interpretado “control espacio” como controlar el espacio interior (`padding`) de una caja con el slider. Si el profesor pide otra cosa, cambia solo la propiedad CSS que se modifica.

La solución está en `solucion/`. **No la abras hasta intentarlo.**

---

## Qué hay que hacer

1. Poner un `input type="range"` con `min="1"`, `max="100"` y `step="5"`.
2. Mostrar el valor actual al lado del slider.
3. Cambiar el `padding` de una caja cuando se mueve el slider.
4. Añadir botones **-5**, **+5** y **Reiniciar**.
5. Usar variables para el valor, el mínimo, el máximo y el paso.
6. Usar funciones para pintar, cambiar el valor y reiniciar.
7. Dar estilo a los botones y añadir una animación sencilla a la caja.

---

## Hueco 1 — El slider en HTML

```html
<label for="exp">
    Espacio: <span id="valor">21</span> px
</label>

<input type="range" id="exp" name="exp"
       min="1" max="100" step="5" value="21">

<button type="button" id="btn-menos">-5</button>
<button type="button" id="btn-mas">+5</button>
<button type="button" id="btn-reiniciar">Reiniciar</button>
```

<details>
<summary>Ver explicación</summary>

El `label` usa `for="exp"` para quedar asociado al slider. El `span` con `id="valor"` servirá para mostrar el número. Los botones llevan `type="button"` para que no envíen ningún formulario.
</details>

---

## Hueco 2 — Referencias del DOM

```js
const slider = /* TODO: input con id exp */;
const valor = /* TODO: span con id valor */;
const caja = /* TODO: div con id caja */;
```

<details>
<summary>Ver solución</summary>

```js
const slider = document.getElementById("exp");
const valor = document.getElementById("valor");
const caja = document.getElementById("caja");
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
let espacio = 21;
const MIN = 1;
const MAX = 100;
const PASO = 5;
```

El valor inicial es 21 porque con `min="1"` y `step="5"` los valores válidos son 1, 6, 11, 16, 21, 26...
</details>

---

## Hueco 4 — Función pintar

```js
function pintar() {
    /* TODO: sincronizar el slider con la variable espacio */
    /* TODO: mostrar el número en el span */
    /* TODO: aplicar el espacio como padding de la caja */
}
```

<details>
<summary>Ver solución</summary>

```js
function pintar() {
    slider.value = espacio;
    valor.textContent = espacio;
    caja.style.padding = espacio + "px";
}
```

`textContent` escribe texto sin interpretar HTML. `style.padding` espera un valor con unidad, por eso añadimos `"px"`.
</details>

---

## Hueco 5 — Función cambiar

```js
function cambiar(cantidad) {
    /* TODO: sumar la cantidad y limitar entre MIN y MAX */
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
```

`Math.max(MIN, ...)` impide bajar del mínimo. `Math.min(MAX, ...)` impide pasar del máximo.
</details>

---

## Hueco 6 — Eventos

```js
slider.addEventListener(/* TODO: evento */, /* TODO: función */);

btnMas.addEventListener("click", /* TODO */);
btnMenos.addEventListener("click", /* TODO */);
btnReiniciar.addEventListener("click", /* TODO */);
```

<details>
<summary>Ver solución</summary>

```js
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

btnReiniciar.addEventListener("click", function () {
    espacio = 21;
    pintar();
});
```

El evento `input` se dispara mientras arrastras el slider. `Number(slider.value)` convierte el texto del slider en número.
</details>

---

## Hueco 7 — Botones bonitos y animación

```css
button {
    /* TODO: fondo, color, borde redondeado y transición */
}

button:hover {
    /* TODO: subir un poco y poner sombra */
}

.caja {
    /* TODO: animación */
}

@keyframes flotar {
    /* TODO: movimiento suave de arriba a abajo */
}
```

<details>
<summary>Ver solución</summary>

```css
button {
    padding: 11px 18px;
    border: 0;
    border-radius: 10px;
    background: #2b6cb0;
    color: #ffffff;
    font-weight: 700;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

button:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(43, 108, 176, 0.28);
}

.caja {
    animation: flotar 2.5s ease-in-out infinite;
}

@keyframes flotar {
    0%, 100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-6px);
    }
}
```

`transition` suaviza el cambio al pasar el ratón. `@keyframes` define los fotogramas de la animación y `animation` la aplica.
</details>

---

## Si el profe pide otra cosa

| Lo que pide | Qué cambiar |
|---|---|
| Controlar el espacio | `caja.style.padding = valor + "px"` |
| Controlar el tamaño | `caja.style.width` o `caja.style.height` |
| Controlar el color | `caja.style.backgroundColor` o una variable CSS |
| Controlar la velocidad | `animation-duration` |
| Que se vea el valor | `valor.textContent = espacio` |
| Que empiece en otro número | `ESPACIO_INICIAL` y el `value` del HTML |

---

## Posibles preguntas del profe

1. **¿Qué diferencia hay entre `input` y `change`?** `input` salta mientras mueves el slider; `change` salta al soltarlo.
2. **¿Por qué usas `Number()`?** Porque `slider.value` devuelve un texto, no un número.
3. **¿Para qué sirven `Math.min` y `Math.max`?** Para limitar el valor y que no se salga de `MIN` y `MAX`.
4. **¿Qué hace `addEventListener`?** Asocia un evento (clic, movimiento del slider...) a una función.
5. **¿Por qué el `<script>` lleva `defer`?** Para que el JavaScript se ejecute cuando el HTML ya existe y `getElementById` no devuelva `null`.
6. **¿Qué diferencia hay entre `textContent` e `innerHTML`?** `textContent` escribe texto; `innerHTML` interpreta etiquetas HTML.
7. **¿Cómo añadirías otro botón, por ejemplo +10?** Añadir el botón en HTML, escuchar su `click` y llamar a `cambiar(10)`.
