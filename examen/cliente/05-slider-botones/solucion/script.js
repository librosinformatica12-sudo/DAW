// REFERENCIAS: elementos del HTML
const slider = document.getElementById("exp");
const valor = document.getElementById("valor");
const caja = document.getElementById("caja");
const btnMenos = document.getElementById("btn-menos");
const btnMas = document.getElementById("btn-mas");
const btnReiniciar = document.getElementById("btn-reiniciar");

// VARIABLES: configuración del ejercicio
const MIN = 1;
const MAX = 100;
const PASO = 5;
const ESPACIO_INICIAL = 21;

let espacio = ESPACIO_INICIAL;

// FUNCIÓN: pinta el valor y aplica el espacio a la caja
function pintar() {
    slider.value = espacio;
    valor.textContent = espacio;
    caja.style.padding = espacio + "px";
}

// FUNCIÓN: suma o resta sin salirse de los límites
function cambiar(cantidad) {
    espacio = Math.min(MAX, Math.max(MIN, espacio + cantidad));
    pintar();
}

// FUNCIÓN: vuelve al valor inicial
function reiniciar() {
    espacio = ESPACIO_INICIAL;
    pintar();
}

// EVENTOS: slider y botones
slider.addEventListener("input", function () {
    espacio = Number(slider.value);
    pintar();
});

btnMenos.addEventListener("click", function () {
    cambiar(-PASO);
});

btnMas.addEventListener("click", function () {
    cambiar(PASO);
});

btnReiniciar.addEventListener("click", reiniciar);

// ARRANQUE: deja la página en su estado inicial
pintar();
