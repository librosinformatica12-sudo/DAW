# Guía paso a paso: hacer "Tiro con Arco" en el examen

Esta guía resume cómo construir el proyecto desde cero, en orden, para que
puedas reproducirlo sin mirar el código. Los archivos `index.html`,
`estilos/estilos.css` y `js/tiroarco.js` de esta carpeta están comentados
línea a línea para consultarlos.

---

## 0. Idea general

- **HTML** = estructura (paneles, botones, divs del escenario).
- **CSS** = diseño (tamaños, colores, `position`, animaciones).
- **JS** = lógica (mover la diana, disparar, puntuar, temporizadores).

Patrón **Modelo–Vista**: las variables guardan el estado (modelo) y el JS
escribe ese estado en el HTML (vista). Nunca al revés.

---

## 1. Estructura de carpetas

```
TiroArco/
├── index.html
├── estilos/estilos.css
├── js/tiroarco.js
└── res/fondo.png
```

En `index.html`, carga el CSS en el `<head>` y el JS con `defer`:

```html
<link rel="stylesheet" href="estilos/estilos.css">
<script src="js/tiroarco.js" defer></script>
```

> `defer` es clave: el JS se ejecuta cuando el HTML ya está cargado, así
> `getElementById` encuentra todos los elementos.

---

## 2. HTML: dos paneles

1. **Panel configuración** (`#panel-config`): dos `<input type="number">`
   (`#spin_flechas`, `#spin_velocidad`) y el botón `#btn_iniciar_juego`.
2. **Panel juego** (`#panel-juego`), con:
   - `#barra_control`: botones Disparar/Play/Pausa/Acelerar/Frenar/Reiniciar.
   - `#escenario`: dentro van `#diana`, `#flecha`, `#arco`.
   - `#panel_estadisticas`: `<span>` con ids para flechas, puntos, aciertos,
     fallos y un `<p id="mensaje">`.

---

## 3. CSS: lo mínimo imprescindible

1. `#escenario` con tamaño **600x450**, `position: relative` y
   `overflow: hidden`. El `relative` es lo que permite colocar los hijos
   con `absolute` dentro.
2. `#diana`: 100x100, `border-radius: 50%`, `position: absolute`, se mueve
   cambiando `left`.
3. `#flecha`: 6x70, `position: absolute`, se mueve cambiando `bottom`.
   La punta se dibuja con `::before` (truco del triángulo con bordes).
4. `#arco`: semicírculo con `border-radius: 100px 100px 0 0`.
5. `#main`: `display: flex` para poner barra | escenario | estadísticas.
6. `@keyframes` para las animaciones: `diana-impacto`, `arco-retroceso`,
   `subir-desaparecer`.

**Truco para reiniciar una animación CSS desde JS:**

```js
elemento.classList.remove('clase');
void elemento.offsetWidth; // fuerza reflow
elemento.classList.add('clase');
```

---

## 4. JS: constantes y modelo

```js
const ANCHO_ESCENARIO = 600;
const ANCHO_DIANA     = 100;
const LARGO_FLECHA    = 70;
const ALTO_ESCENARIO  = 450;
const Y_IMPACTO       = 370; // 450 - (top 30 + radio 50)

let flechasRestantes, puntos = 0, aciertos = 0, fallos = 0;
let dianaX = 0, dianaDireccion = 1, velocidad = 30;
let flechaY = 70, flechaVolando = false, impactoComprobado = false;
let timerDiana, timerFlecha;
```

> Las constantes deben coincidir con los tamaños del CSS.

---

## 5. JS: eventos

```js
btn_iniciar.addEventListener('click', prepararJuego);
btn_disparar.addEventListener('click', disparar);
btn_play.addEventListener('click', playDiana);
btn_stop.addEventListener('click', stopDiana);
btn_acelerar.addEventListener('click', acelerar);
btn_frenar.addEventListener('click', frenar);
btn_reiniciar.addEventListener('click', reiniciar);
```

Se pasa el **nombre** de la función, sin paréntesis.

---

## 6. JS: temporizadores (la base del movimiento)

- `setInterval(fn, ms)` repite `fn` cada `ms` milisegundos.
- `clearInterval(id)` lo detiene.
- **Siempre** hacer `clearInterval` antes de crear uno nuevo para no tener
  dos intervalos a la vez (iría el doble de rápido).

```js
function playDiana() {
  clearInterval(timerDiana);
  timerDiana = setInterval(moverDiana, velocidad);
}
```

- Acelerar = **menos** ms (`velocidad -= 5`, mínimo 10).
- Frenar   = **más** ms (`velocidad += 5`, máximo 100).

---

## 7. JS: mover la diana

```js
function moverDiana() {
  dianaX += 4 * dianaDireccion;
  if (dianaX >= ANCHO_ESCENARIO - ANCHO_DIANA) { dianaX = 500; dianaDireccion = -1; }
  if (dianaX <= 0)                             { dianaX = 0;   dianaDireccion = 1; }
  dianaDIV.style.left = dianaX + 'px';
}
```

---

## 8. JS: disparar y volar la flecha

```js
function disparar() {
  if (flechaVolando || flechasRestantes <= 0) return;
  flechaVolando = true;
  impactoComprobado = false;
  flechasRestantes--;
  flechaY = 70;
  flechaDIV.style.bottom = flechaY + 'px';
  flechaDIV.style.display = 'block';
  btn_disparar.disabled = true;
  timerFlecha = setInterval(volarFlecha, 10);
}
```

En `volarFlecha` la flecha sube `flechaY += 8` cada 10 ms. Cuando la punta
(`flechaY + LARGO_FLECHA`) llega a `Y_IMPACTO`, se comprueba el impacto
**una sola vez** gracias a `impactoComprobado`.

---

## 9. JS: puntuación

La flecha sube por el centro (`x = 300`). Solo importa la distancia
horizontal entre el centro de la diana y 300:

| Distancia | Puntos | Zona |
|-----------|--------|------|
| ≤ 10      | 10     | centro rojo |
| ≤ 25      | 5      | amarillo |
| ≤ 50      | 1      | borde blanco |
| > 50      | 0      | fallo |

```js
let centroDiana = dianaX + ANCHO_DIANA / 2;
let distancia = Math.abs(centroDiana - ANCHO_ESCENARIO / 2);
```

---

## 10. JS: fin de vuelo y estadísticas

- `terminarVuelo()`: `clearInterval(timerFlecha)`, oculta la flecha,
  `flechaVolando = false` y reactiva el botón si quedan flechas; si no,
  muestra "Fin de la partida".
- `actualizarEstadisticas()`: copia las variables del modelo a los `<span>`
  con `innerText`.

---

## 11. JS: preparar y reiniciar

- `prepararJuego()`: lee los inputs con `parseInt(.value)`, valida
  (`isNaN`, rangos), calcula `velocidad = 110 - nivel*10`, reinicia el
  modelo, cambia de panel y arranca `playDiana()`.
- `reiniciar()`: `stopDiana()`, `clearInterval(timerFlecha)` y vuelve al
  panel de configuración.

---

## 12. Errores típicos que te pueden preguntar

1. Olvidar `defer` → `getElementById` devuelve `null`.
2. Poner `position: relative` en los hijos en vez de en `#escenario`.
3. No hacer `clearInterval` antes de un nuevo `setInterval` (velocidad x2).
4. Pasar `prepararJuego()` con paréntesis en `addEventListener` (se ejecuta
   al instante en vez de al hacer clic).
5. No convertir `.value` a número (`parseInt`) → comparaciones con texto.
6. Comprobar el impacto cada tick sin `impactoComprobado` → puntos múltiples.
7. Que las constantes del JS no coincidan con los px del CSS.

---

## 13. Checklist para el examen

- [ ] Carpetas y enlaces CSS/JS con rutas relativas.
- [ ] Paneles config/juego que se alternan con `style.display`.
- [ ] Escenario `relative` + hijos `absolute`.
- [ ] `setInterval`/`clearInterval` para diana y flecha.
- [ ] Rebote de la diana en los bordes.
- [ ] Disparo con validaciones (flecha volando / sin flechas).
- [ ] Cálculo de impacto y puntuación por distancia.
- [ ] Estadísticas actualizadas y mensajes.
- [ ] Animaciones con `@keyframes` y reinicio con `offsetWidth`.
