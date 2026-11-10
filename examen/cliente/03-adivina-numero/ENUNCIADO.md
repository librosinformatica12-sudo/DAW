[NOTA PARA NOTEPAD] Abre index.html en el navegador para leerlo bien. En Notepad se ven símbolos raros.

# Ejercicio 03 — Adivina el número (cliente)

**Enunciado general:** el programa piensa un número del 1 al 100. El usuario escribe intentos
y el programa responde "más alto", "más bajo" o "acertaste". Se cuentan los intentos y se
guarda un historial. Al acertar se bloquea el juego y se puede reiniciar.

Solución en `solucion/`.

---

## Hueco 1 — Generar el número secreto

```js
function generarNumero() {
    return /* TODO: entero aleatorio entre 1 y 100 */;
}
```

<details>
<summary>Ver solución</summary>

```js
return Math.floor(Math.random() * 100) + 1;
```
</details>

---

## Hueco 2 — Evitar la recarga del formulario

```js
form.addEventListener("submit", function (evento) {
    /* TODO: evitar el envío normal */
    comprobarIntento();
});
```

<details>
<summary>Ver solución</summary>

```js
evento.preventDefault();
```
</details>

---

## Hueco 3 — Validar la entrada

```js
const texto = input.value.trim();
const valor = Number(texto);

if (/* TODO: vacío, no entero o fuera de 1..100 */) {
    pMensaje.textContent = "Escribe un número entero entre 1 y 100.";
    pMensaje.className = "mensaje error";
    return;
}
```

<details>
<summary>Ver solución</summary>

```js
if (texto === "" || !Number.isInteger(valor) || valor < 1 || valor > 100) {
```
</details>

---

## Hueco 4 — Contar el intento

```js
intentos++;
/* TODO: mostrar intentos en spanIntentos */
```

<details>
<summary>Ver solución</summary>

```js
spanIntentos.textContent = intentos;
```
</details>

---

## Hueco 5 — Comparar

```js
if (/* TODO: ha acertado */) {
    ...
} else if (/* TODO: se ha quedado corto */) {
    pMensaje.textContent = "El número secreto es MÁS ALTO.";
} else {
    pMensaje.textContent = "El número secreto es MÁS BAJO.";
}
```

<details>
<summary>Ver solución</summary>

```js
if (valor === secreto) {
    ...
} else if (valor < secreto) {
```
</details>

---

## Hueco 6 — Bloquear al acertar

```js
function terminarPartida() {
    terminado = /* TODO: true */;
    input./* TODO: deshabilitar */;
    btnProbar./* TODO: deshabilitar */;
}
```

<details>
<summary>Ver solución</summary>

```js
terminado = true;
input.disabled = true;
btnProbar.disabled = true;
```
</details>

---

## Hueco 7 — Reiniciar

```js
function reiniciar() {
    secreto = /* TODO: nuevo número */;
    intentos = /* TODO: 0 */;
    terminado = /* TODO: false */;

    spanIntentos.textContent = "0";
    historial.innerHTML = "";
    input.disabled = false;
    btnProbar.disabled = false;
    input.value = "";
}
```

<details>
<summary>Ver solución</summary>

```js
secreto = generarNumero();
intentos = 0;
terminado = false;
```
</details>

---

## Hueco 8 — Añadir al historial

```js
function anadirHistorial(valor, tipo) {
    const li = /* TODO: crear un li */;
    li.textContent = /* TODO: texto del intento */;
    historial./* TODO: añadirlo a la lista */;
}
```

<details>
<summary>Ver solución</summary>

```js
const li = document.createElement("li");
li.textContent = valor + " → " + tipo;
historial.appendChild(li);
```
</details>

---

## Posibles preguntas del profe

1. **¿Por qué `Number()` y no comparar directamente?**
   `input.value` es siempre string. `"50" === 50` es `false`; hay que convertir.
2. **¿Qué hace `Number.isInteger()`?**
   Comprueba que sea un número entero, no decimal ni `NaN`.
3. **¿Por qué `terminado`?**
   Para que al acertar no se sigan procesando intentos aunque se pulse el botón.
4. **¿Qué pasa si el usuario escribe `abc` en un `type="number"`?**
   El navegador devuelve `""` en `value`, por eso validamos el vacío.
5. **¿Cómo limitarías a 10 intentos?**
   Añadiendo `if (intentos >= 10) { terminarPartida(); ... }`.
