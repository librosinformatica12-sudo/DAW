[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 07 — Espaciado flexible (cliente)

**Enunciado general:** crea una página con un slider que controle la separación entre varias tarjetas. Añade botones para sumar, restar, reiniciar y lanzar una animación de balanceo en cadena. Usa variables y funciones.

**Pistas:** `input type="range"`, `display: flex`, `gap`, variables, funciones, botones y una animación CSS diferente a las de los ejercicios 05 y 06.

La solución está en `solucion/`. **No la abras hasta intentarlo.**

---

## Qué hay que hacer

1. Poner un `input type="range"` con `min="0"`, `max="40"` y `step="4"`.
2. Mostrar el valor actual al lado del slider.
3. Cambiar el `gap` de la galería cuando se mueve el slider.
4. Añadir botones **-4**, **+4**, **Balancear** y **Reiniciar**.
5. Usar variables para el valor, el mínimo, el máximo y el paso.
6. Usar funciones para pintar, cambiar, reiniciar y animar.
7. Añadir una animación CSS de balanceo que se lance con el botón.

---

## Hueco 1 — HTML del slider, botones y tarjetas

```html
<label for="exp">
    Espacio entre tarjetas:
    <span id="valor">8</span> px
</label>

<input type="range" id="exp" name="exp"
       min="0" max="40" step="4" value="8">

<button type="button" id="btn-menos">-4</button>
<button type="button" id="btn-mas">+4</button>
<button type="button" id="btn-animar">Balancear</button>
<button type="button" id="btn-reiniciar">Reiniciar</button>

<section id="galeria" class="galeria">
    <div class="caja">1</div>
    <div class="caja">2</div>
    <div class="caja">3</div>
    <div class="caja">4</div>
</section>
```

<details>
<summary>Ver explicación</summary>

El `label` usa `for="exp"` para quedar asociado al slider. El `span` con `id="valor"` muestra el número. Las tarjetas tienen la clase `caja` para poder seleccionarlas todas con `querySelectorAll`.
</details>

---

## Hueco 2 — Referencias del DOM

```js
const slider = /* TODO: input con id exp */;
const valor = /* TODO: span con id valor */;
const galeria = /* TODO: section con id galeria */;
const cajas = /* TODO: todas las tarjetas con clase caja */;
```

<details>
<summary>Ver solución</summary>

```js
const slider = document.getElementById("exp");
const valor = document.getElementById("valor");
const galeria = document.getElementById("galeria");
const cajas = document.querySelectorAll(".caja");
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
let espacio = 8;
const MIN = 0;
const MAX = 40;
const PASO = 4;
```
</details>

---

## Hueco 4 — Función pintar

```js
function pintar() {
    /* TODO: sincronizar el slider con la variable espacio */
    /* TODO: mostrar el número en el span */
    /* TODO: aplicar el espacio como gap de la galería */
}
```

<details>
<summary>Ver solución</summary>

```js
function pintar() {
    slider.value = espacio;
    valor.textContent = espacio;
    galeria.style.gap = espacio + "px";
}
```

`gap` separa los elementos de un flex o grid. `style.gap` espera un valor con unidad, por eso añadimos `"px"`.
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
    espacio = 8;
    pintar();
}
```

`Math.max(MIN, ...)` impide bajar del mínimo. `Math.min(MAX, ...)` impide pasar del máximo.
</details>

---

## Hueco 6 — Animación en cadena

```js
function animar() {
    cajas.forEach(function (caja, indice) {
        /* TODO: quitar la clase balanceo */
        /* TODO: forzar el reinicio de la animación */
        /* TODO: retrasar cada tarjeta según su índice */
        /* TODO: añadir la clase balanceo */
        /* TODO: quitarla cuando termine */
    });
}
```

<details>
<summary>Ver solución</summary>

```js
function animar() {
    cajas.forEach(function (caja, indice) {
        caja.classList.remove("balanceo");
        void caja.offsetWidth;

        caja.style.animationDelay = (indice * 0.08) + "s";
        caja.classList.add("balanceo");

        setTimeout(function () {
            caja.classList.remove("balanceo");
            caja.style.animationDelay = "";
        }, 900 + indice * 80);
    });
}
```

`void caja.offsetWidth` obliga al navegador a recalcular el elemento y permite repetir la animación. `animationDelay` hace que las tarjetas se muevan en cadena.
</details>

---

## Hueco 7 — CSS del flex y la animación

```css
.galeria {
    /* TODO: display flex, wrap, centrado y transición del gap */
}

.caja {
    /* TODO: tamaño, centrado y estilo de tarjeta */
}

.caja.balanceo {
    /* TODO: aplicar la animación */
}

@keyframes balanceo {
    /* TODO: rotar a un lado y a otro */
}
```

<details>
<summary>Ver solución</summary>

```css
.galeria {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: 8px;
    transition: gap 0.2s ease;
}

.caja {
    width: 92px;
    height: 92px;
    display: grid;
    place-items: center;
    border-radius: 14px;
    background: linear-gradient(135deg, #7c3aed, #06b6d4);
    color: #ffffff;
    font-size: 1.5rem;
    font-weight: 800;
}

.caja.balanceo {
    animation: balanceo 0.8s ease-in-out;
}

@keyframes balanceo {
    0%, 100% {
        transform: rotate(0deg) translateY(0);
    }

    25% {
        transform: rotate(-8deg) translateY(-8px);
    }

    75% {
        transform: rotate(8deg) translateY(-8px);
    }
}
```

`flex-wrap` permite que las tarjetas bajen a otra línea si no caben. `transition` suaviza el cambio de `gap`.
</details>

---

## Si el profe pide otra cosa

| Lo que pide | Qué cambiar |
|---|---|
| Espacio entre tarjetas | `galeria.style.gap` |
| Espacio interior de cada tarjeta | `caja.style.padding` |
| Que las tarjetas se coloquen en columna | `flex-direction: column` |
| Animación más lenta | `animation-duration` |
| Que se vea el valor | `valor.textContent = espacio` |
| Que la animación sea en cadena | `animationDelay` según el índice |

---

## Posibles preguntas del profe

1. **¿Qué diferencia hay entre `input` y `change`?** `input` salta mientras mueves el slider; `change` salta al soltarlo.
2. **¿Por qué usas `Number()`?** Porque `slider.value` devuelve un texto, no un número.
3. **¿Para qué sirven `Math.min` y `Math.max`?** Para limitar el valor y que no se salga de `MIN` y `MAX`.
4. **¿Qué hace `querySelectorAll`?** Devuelve todos los elementos que coinciden con el selector.
5. **¿Qué hace `classList.add`?** Añade una clase al elemento; en este caso activa la animación.
6. **¿Por qué quitas la clase con `setTimeout`?** Para poder volver a lanzar la misma animación.
7. **¿Qué diferencia hay entre `textContent` e `innerHTML`?** `textContent` escribe texto; `innerHTML` interpreta etiquetas HTML.
